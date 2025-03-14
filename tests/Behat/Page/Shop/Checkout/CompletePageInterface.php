<?php


declare(strict_types=1);

namespace Tests\Sylius\MolliePlugin\Behat\Page\Shop\Checkout;

use Sylius\Behat\Page\Shop\Checkout\CompletePageInterface as BaseCompletePageInterface;

interface CompletePageInterface extends BaseCompletePageInterface
{
    public function selectPaymentOption(string $paymentOptionName): void;
}
