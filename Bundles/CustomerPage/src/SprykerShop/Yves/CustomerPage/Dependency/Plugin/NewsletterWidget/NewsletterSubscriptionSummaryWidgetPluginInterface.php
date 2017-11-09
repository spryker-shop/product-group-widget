<?php

/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CustomerPage\Dependency\Plugin\NewsletterWidget;

use Generated\Shared\Transfer\CustomerOverviewResponseTransfer;
use Spryker\Yves\Kernel\Dependency\Plugin\WidgetPluginInterface;

interface NewsletterSubscriptionSummaryWidgetPluginInterface extends WidgetPluginInterface
{
    public const NAME = 'NewsletterSubscriptionSummaryWidgetPlugin';

    /**
     * @param \Generated\Shared\Transfer\CustomerOverviewResponseTransfer $customerOverviewResponseTransfer
     *
     * @return void
     */
    public function initialize(CustomerOverviewResponseTransfer $customerOverviewResponseTransfer): void;
}