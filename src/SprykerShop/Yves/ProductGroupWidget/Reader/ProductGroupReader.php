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
        $productViewTransfers = $this->expandProductViewBulkTransfers($productViewTransfers);

        return $this->getExpandedProductViewTransfers($productViewTransfers);
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

        return $this->productStorageClient->getProductAbstractViewTransfers(
            $productAbstractGroupStorageTransfer->getGroupProductAbstractIds(),
            $localeName,
            $selectedAttributes,
        );
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

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer
     */
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
