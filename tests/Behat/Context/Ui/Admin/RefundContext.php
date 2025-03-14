<?php


declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Sylius\Behat\Context\Ui\Admin\ManagingOrdersContext;
use Sylius\Component\Core\Model\OrderInterface;

final class RefundContext implements Context
{
    /** @var ManagingOrdersContext */
    private $managingOrdersContext;

    public function __construct(
        ManagingOrdersContext $managingOrdersContext
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
