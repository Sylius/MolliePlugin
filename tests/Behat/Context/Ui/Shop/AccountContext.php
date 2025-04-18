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

namespace Tests\SyliusMolliePlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Tests\SyliusMolliePlugin\Behat\Page\Shop\Account\Order\IndexPageInterface;

final class AccountContext implements Context
{
    /** @var IndexPageInterface */
    private $orderIndexPage;

    /** @var NotificationCheckerInterface */
    private $notificationChecker;

    public function __construct(
        IndexPageInterface $orderIndexPage,
        NotificationCheckerInterface $notificationChecker
    ) {
        $this->orderIndexPage = $orderIndexPage;
        $this->notificationChecker = $notificationChecker;
    }

    /**
     * @When I cancel this subscription
     */
    public function iCancelThisSubscription(): void
    {
        $this->orderIndexPage->cancelSubscription();
    }

    /**
     * @Then I should be notified that it has been successfully canceled
     */
    public function iShouldBeNotifiedThatItHasBeenSuccessfullyCanceled(): void
    {
        $this->notificationChecker->checkNotification(
            'Subscription has been cancelled.',
            NotificationType::success()
        );
    }
}
