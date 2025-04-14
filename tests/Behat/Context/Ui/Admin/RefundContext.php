<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Sylius\Behat\Context\Ui\Admin\ManagingOrdersContext;
use Sylius\Component\Core\Model\OrderInterface;

final class RefundContext implements Context
{
    private ManagingOrdersContext $managingOrdersContext;

    public function __construct(
        ManagingOrdersContext $managingOrdersContext,
    ) {
        $this->managingOrdersContext = $managingOrdersContext;
    }

    /**
     * @When /^I mark (this order)'s mollie payment as refunded$/
     */
    public function iMarkThisOrdersMolliePaymentAsRefunded(OrderInterface $order): void
    {
        $this->managingOrdersContext->iMarkThisOrderSPaymentAsRefunded($order);
    }
}
