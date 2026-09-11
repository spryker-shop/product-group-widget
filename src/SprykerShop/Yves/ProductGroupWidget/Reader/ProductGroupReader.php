<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ProductGroupWidget\Reader;

use Generated\Shared\Transfer\ProductViewTransfer;
use SprykerShop\Yves\ProductGroupWidget\Dependency\Client\ProductGroupWidgetToProductGroupStorageClientInterface;
use SprykerShop\Yves\ProductGroupWidget\Dependency\Client\ProductGroupWidgetToProductStorageClientInterface;

class ProductGroupReader implements ProductGroupReaderInterface
{
    /**
     * @var array<\SprykerShop\Yves\ProductGroupWidgetExtension\Dependency\Plugin\ProductViewBulkExpanderPluginInterface>
     */
    protected $productViewBulkExpanderPlugins;

    /**
     * @var array<\SprykerShop\Yves\ProductGroupWidgetExtension\Dependency\Plugin\ProductViewExpanderPluginInterface>
     */
    protected $productViewExpanderPlugins;

    /**
     * @var \SprykerShop\Yves\ProductGroupWidget\Dependency\Client\ProductGroupWidgetToProductStorageClientInterface
     */
    protected $productStorageClient;

    /**
     * @var \SprykerShop\Yves\ProductGroupWidget\Dependency\Client\ProductGroupWidgetToProductGroupStorageClientInterface
     */
    protected $productGroupStorageClient;

    /**
     * @var array<string, \Generated\Shared\Transfer\ProductViewTransfer>
     */
    protected static array $expandedProductViewTransferCache = [];

    /**
     * @param \SprykerShop\Yves\ProductGroupWidget\Dependency\Client\ProductGroupWidgetToProductGroupStorageClientInterface $productGroupStorageClient
     * @param \SprykerShop\Yves\ProductGroupWidget\Dependency\Client\ProductGroupWidgetToProductStorageClientInterface $productStorageClient
     * @param array<\SprykerShop\Yves\ProductGroupWidgetExtension\Dependency\Plugin\ProductViewExpanderPluginInterface> $productViewExpanderPlugins
     * @param array<\SprykerShop\Yves\ProductGroupWidgetExtension\Dependency\Plugin\ProductViewBulkExpanderPluginInterface> $productViewBulkExpanderPlugins
     */
    public function __construct(
        ProductGroupWidgetToProductGroupStorageClientInterface $productGroupStorageClient,
        ProductGroupWidgetToProductStorageClientInterface $productStorageClient,
        array $productViewExpanderPlugins,
        array $productViewBulkExpanderPlugins
    ) {
        $this->productGroupStorageClient = $productGroupStorageClient;
        $this->productStorageClient = $productStorageClient;
        $this->productViewExpanderPlugins = $productViewExpanderPlugins;
        $this->productViewBulkExpanderPlugins = $productViewBulkExpanderPlugins;
    }

    /**
     * @param int $idProductAbstract
     * @param string $localeName
     * @param array $selectedAttributes
     *
     * @return array<\Generated\Shared\Transfer\ProductViewTransfer>
     */
    public function getProductGroups(int $idProductAbstract, string $localeName, array $selectedAttributes = []): array
    {
        $productViewTransfers = $this->getProductViewTransfers($idProductAbstract, $localeName, $selectedAttributes);

        return $this->expandProductViewTransfersOnce($productViewTransfers, $localeName, $selectedAttributes);
    }

    /**
     * Product groups on a page share the same members (color variants), so the same product view transfer
     * is requested by every sibling tile. The expander plugins (labels, reviews) do storage lookups per item,
     * so they are executed only once per product view transfer within a request and reused for the siblings.
     *
     * @param array<\Generated\Shared\Transfer\ProductViewTransfer> $productViewTransfers
     * @param string $localeName
     * @param array $selectedAttributes
     *
     * @return array<\Generated\Shared\Transfer\ProductViewTransfer>
     */
    protected function expandProductViewTransfersOnce(array $productViewTransfers, string $localeName, array $selectedAttributes): array
    {
        $notExpandedProductViewTransfers = [];
        foreach ($productViewTransfers as $productViewTransfer) {
            $cacheKey = $this->generateProductViewTransferCacheKey($productViewTransfer->getIdProductAbstract(), $localeName, $selectedAttributes);
            if (isset(static::$expandedProductViewTransferCache[$cacheKey])) {
                continue;
            }

            $notExpandedProductViewTransfers[] = $productViewTransfer;
        }

        if ($notExpandedProductViewTransfers !== []) {
            $notExpandedProductViewTransfers = $this->expandProductViewBulkTransfers($notExpandedProductViewTransfers);
            $this->getExpandedProductViewTransfers($notExpandedProductViewTransfers);

            foreach ($notExpandedProductViewTransfers as $productViewTransfer) {
                $cacheKey = $this->generateProductViewTransferCacheKey($productViewTransfer->getIdProductAbstract(), $localeName, $selectedAttributes);
                static::$expandedProductViewTransferCache[$cacheKey] = $productViewTransfer;
            }
        }

        foreach ($productViewTransfers as $key => $productViewTransfer) {
            $cacheKey = $this->generateProductViewTransferCacheKey($productViewTransfer->getIdProductAbstract(), $localeName, $selectedAttributes);
            $productViewTransfers[$key] = static::$expandedProductViewTransferCache[$cacheKey] ?? $productViewTransfer;
        }

        return $productViewTransfers;
    }

    protected function generateProductViewTransferCacheKey(?int $idProductAbstract, string $localeName, array $selectedAttributes): string
    {
        return sprintf('%s:%s:%s', $localeName, (string)$idProductAbstract, json_encode($selectedAttributes));
    }

    /**
     * @param int $idProductAbstract
     * @param string $localeName
     * @param array $selectedAttributes
     *
     * @return array<\Generated\Shared\Transfer\ProductViewTransfer>
     */
    protected function getProductViewTransfers(int $idProductAbstract, string $localeName, array $selectedAttributes = []): array
    {
        $productAbstractGroupStorageTransfer = $this->productGroupStorageClient
            ->findProductGroupItemsByIdProductAbstract($idProductAbstract);
        $groupProductAbstractIds = $productAbstractGroupStorageTransfer->getGroupProductAbstractIds();

        return $this->productStorageClient->getProductAbstractViewTransfers(
            $groupProductAbstractIds,
            $localeName,
            $this->getSelectedAttributesIndexedByIdProductAbstract($groupProductAbstractIds, $selectedAttributes),
        );
    }

    /**
     * @param array<int> $groupProductAbstractIds
     * @param array<string, mixed> $selectedAttributes
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getSelectedAttributesIndexedByIdProductAbstract(
        array $groupProductAbstractIds,
        array $selectedAttributes
    ): array {
        if ($selectedAttributes === []) {
            return [];
        }

        return array_fill_keys($groupProductAbstractIds, $selectedAttributes);
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductViewTransfer> $productViewTransfers
     *
     * @return array<\Generated\Shared\Transfer\ProductViewTransfer>
     */
    protected function getExpandedProductViewTransfers(array $productViewTransfers): array
    {
        foreach ($productViewTransfers as $productViewTransfer) {
            $this->expandProductViewTransfer($productViewTransfer);
        }

        return $productViewTransfers;
    }

    protected function expandProductViewTransfer(ProductViewTransfer $productViewTransfer): ProductViewTransfer
    {
        foreach ($this->productViewExpanderPlugins as $productViewExpanderPlugin) {
            $productViewTransfer = $productViewExpanderPlugin->expand($productViewTransfer);
        }

        return $productViewTransfer;
    }

    /**
     * @param array<\Generated\Shared\Transfer\ProductViewTransfer> $productViewTransfers
     *
     * @return array<\Generated\Shared\Transfer\ProductViewTransfer>
     */
    protected function expandProductViewBulkTransfers(array $productViewTransfers): array
    {
        foreach ($this->productViewBulkExpanderPlugins as $productViewBulkExpanderPlugin) {
            $productViewTransfers = $productViewBulkExpanderPlugin->execute($productViewTransfers);
        }

        return $productViewTransfers;
    }
}
