<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ProductGroupWidget\Dependency\Client;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;

class ProductGroupWidgetToProductStorageClientBridge implements ProductGroupWidgetToProductStorageClientInterface
{
    /**
     * @var \Spryker\Client\ProductStorage\ProductStorageClientInterface
     */
    protected $productStorageClient;

    /**
     * @param \Spryker\Client\ProductStorage\ProductStorageClientInterface $productStorageClient
     */
    public function __construct($productStorageClient)
    {
        $this->productStorageClient = $productStorageClient;
    }

    /**
     * @param array $productAbstractIds
     * @param string $localeName
     * @param \Generated\Shared\Transfer\CustomerTransfer|null $customerTransfer
     * @param string|null $priceMode
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer[]|null
     */
    public function findMappedProductsAbstractStorageData(array $productAbstractIds, string $localeName, CustomerTransfer $customerTransfer = null, string $priceMode = null): ?array
    {
        return $this->productStorageClient->findMappedProductsAbstractStorageData($productAbstractIds, $localeName, $customerTransfer, $priceMode);
    }

    /**
     * @param int $idProductAbstract
     * @param string $localeName
     *
     * @return array
     */
    public function getProductAbstractStorageData($idProductAbstract, $localeName): array
    {
        return $this->productStorageClient->getProductAbstractStorageData($idProductAbstract, $localeName);
    }

    /**
     * @param int $idProductAbstract
     * @param string $localeName
     *
     * @return array|null
     */
    public function findProductAbstractStorageData(int $idProductAbstract, string $localeName): ?array
    {
        return $this->productStorageClient->findProductAbstractStorageData($idProductAbstract, $localeName);
    }

    /**
     * @param array $data
     * @param string $localeName
     * @param array $selectedAttributes
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer
     */
    public function mapProductStorageData(array $data, $localeName, array $selectedAttributes = [], CustomerTransfer $customerTransfer = null, string $priceMode = null): ProductViewTransfer
    {
        return $this->productStorageClient->mapProductStorageData($data, $localeName, $selectedAttributes, $customerTransfer, $priceMode);
    }
}
