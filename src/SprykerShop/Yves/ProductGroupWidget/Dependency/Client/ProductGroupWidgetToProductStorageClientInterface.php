<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ProductGroupWidget\Dependency\Client;

use Generated\Shared\Transfer\CustomerTransfer;

interface ProductGroupWidgetToProductStorageClientInterface
{
    /**
     * @param array $productAbstractIds
     * @param string $localeName
     * @param \Generated\Shared\Transfer\CustomerTransfer|null $customerTransfer
     * @param string|null $priceMode
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer[]|null
     */
    public function findMappedProductsAbstractStorageData(array $productAbstractIds, string $localeName, CustomerTransfer $customerTransfer = null, string $priceMode = null): ?array;

    /**
     * @param int $idProductAbstract
     * @param string $localeName
     *
     * @return array
     */
    public function getProductAbstractStorageData($idProductAbstract, $localeName);

    /**
     * @param int $idProductAbstract
     * @param string $localeName
     *
     * @return array|null
     */
    public function findProductAbstractStorageData(int $idProductAbstract, string $localeName): ?array;

    /**
     * @param array $data
     * @param string $localeName
     * @param array $selectedAttributes
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer
     */
    public function mapProductStorageData(array $data, $localeName, array $selectedAttributes = [], CustomerTransfer $customerTransfer = null, string $priceMode = null);
}
