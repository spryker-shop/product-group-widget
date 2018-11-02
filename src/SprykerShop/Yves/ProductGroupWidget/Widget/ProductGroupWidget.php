<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ProductGroupWidget\Widget;

use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Client\Customer\CustomerClient;
use Spryker\Client\Price\PriceClient;
use Spryker\Yves\Kernel\Widget\AbstractWidget;

/**
 * @method \SprykerShop\Yves\ProductGroupWidget\ProductGroupWidgetFactory getFactory()
 */
class ProductGroupWidget extends AbstractWidget
{
    /**
     * @param int $idProductAbstract
     */
    public function __construct(ProductViewTransfer $productViewTransfer)
    {
        $this->addParameter('productGroupItems', $this->getProductGroups($productViewTransfer))
            ->addParameter('idProductAbstract', $productViewTransfer->getIdProductAbstract());
    }

    /**
     * @api
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'ProductGroupWidget';
    }

    /**
     * @api
     *
     * @return string
     */
    public static function getTemplate(): string
    {
        return '@ProductGroupWidget/views/product-group/product-group.twig';
    }

    /**
     * @param int $idProductAbstract
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer[]
     */
    protected function getProductGroups(ProductViewTransfer $productViewTransfer): array
    {
        $productGroup = $this->getFactory()->getProductGroupStorageClient()->findProductGroupItemsByIdProductAbstract($productViewTransfer->getIdProductAbstract());
        $productViewTransfers = [$productViewTransfer];
        $productStorageClient = $this->getFactory()->getProductStorageClient();

        $customer = (new CustomerClient())->getCustomer();
        $priceMode = (new PriceClient())->getCurrentPriceMode();

        foreach ($productGroup->getGroupProductAbstractIds() as $idProductAbstract) {
            if($idProductAbstract === $productViewTransfer->getIdProductAbstract()) {
                $productViewTransfers[] = $productViewTransfer;
                continue;
            }

            $productData = $productStorageClient->findProductAbstractStorageData($idProductAbstract, $this->getLocale());
            if (!$productData) {
                continue;
            }

            $productViewTransfers[] = $productStorageClient->mapProductStorageData($productData, $this->getLocale(), [], $customer, $priceMode);
        }

        return $productViewTransfers;
    }
}
