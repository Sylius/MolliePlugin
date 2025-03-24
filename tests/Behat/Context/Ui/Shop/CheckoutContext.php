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

namespace Tests\Sylius\MolliePlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\RawMinkContext;
use Tests\Sylius\MolliePlugin\Behat\Page\Shop\Checkout\CompletePageInterface;

final class CheckoutContext extends RawMinkContext implements Context
{
    private CompletePageInterface $summaryPage;

    public function __construct(
        CompletePageInterface $summaryPage,
    ) {
        $this->summaryPage = $summaryPage;
    }

    /**
     * @When I select :paymentOptionName as my payment option
     */
    public function iSelectPaymentOption(string $paymentOptionName): void
    {
        $this->summaryPage->selectPaymentOption($paymentOptionName);
    }
}
