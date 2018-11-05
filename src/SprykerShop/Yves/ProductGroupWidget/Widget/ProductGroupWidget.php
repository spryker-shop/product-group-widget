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
        $groupAbstractProductIds = $productGroup->getGroupProductAbstractIds();

        foreach($groupAbstractProductIds as $index => $productAbstractId) {
            if($productAbstractId === $productViewTransfer->getIdProductAbstract()) {
                unset($productAbstractId[$index]);
            }
        }

        $customer = (new CustomerClient())->getCustomer();
        $priceMode = (new PriceClient())->getCurrentPriceMode();

        return array_merge(
            [$productViewTransfer],
            $this->getFactory()->getProductStorageClient()->findMappedProductsAbstractStorageData(
                $productGroup->getGroupProductAbstractIds(),
                $this->getLocale(),
                $customer,
                $priceMode
            )
        );
    }
}
