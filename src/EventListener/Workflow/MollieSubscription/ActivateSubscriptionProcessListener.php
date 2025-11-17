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

namespace Sylius\MolliePlugin\EventListener\Workflow\MollieSubscription;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionScheduleProcessorInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final class ActivateSubscriptionProcessListener
{
    public function __construct(private readonly SubscriptionScheduleProcessorInterface $subscriptionScheduleProcessor)
    {
    }

    public function __invoke(CompletedEvent $event): void
    {
        $subscription = $event->getSubject();
        Assert::isInstanceOf($subscription, MollieSubscriptionInterface::class);

        $this->subscriptionScheduleProcessor->processScheduleGeneration($subscription);
    }
}
