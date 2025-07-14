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
use Behat\Step\Then;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Tests\Sylius\MolliePlugin\Behat\Page\Shop\Checkout\CompletePageInterface;

final class CheckoutContext extends RawMinkContext implements Context
{
    public function __construct(
        private readonly CompletePageInterface $summaryPage,
        private readonly NotificationCheckerInterface $notificationChecker,
    ) {
    }

    /**
     * @When I select :paymentOptionName as my payment option
     */
    public function iSelectPaymentOption(string $paymentOptionName): void
    {
        $this->summaryPage->selectPaymentOption($paymentOptionName);
    }

    #[Then('I should be notified that my payment has been completed')]
    public function iShouldBeNotifiedThatMyPaymentHasBeenCompleted(): void
    {
        $this->notificationChecker->checkNotification(
            'Payment has been completed.',
            NotificationType::info(),
        );
    }

    #[Then('I should be notified that my payment has been cancelled')]
    public function iShouldBeNotifiedThatMyPaymentHasBeenCancelled(): void
    {
        $this->notificationChecker->checkNotification(
            'Payment has been cancelled.',
            NotificationType::info(),
        );
    }
}
