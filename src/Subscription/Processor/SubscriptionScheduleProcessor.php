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

namespace Sylius\MolliePlugin\Subscription\Processor;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Subscription\Generator\SubscriptionScheduleGeneratorInterface;
use Webmozart\Assert\Assert;

final class SubscriptionScheduleProcessor implements SubscriptionScheduleProcessorInterface
{
    public function __construct(
        private readonly RepositoryInterface $scheduleRepository,
        private readonly SubscriptionScheduleGeneratorInterface $scheduleGenerator,
    ) {
    }

    public function process(MollieSubscriptionInterface $subscription): void
    {
        $payment = $subscription->getLastPayment();

        if (null !== $payment) {
            /** @var OrderInterface $order */
            $order = $payment->getOrder();
            Assert::notNull($order->getRecurringSequenceIndex());
            $schedule = $subscription->getScheduleByIndex($order->getRecurringSequenceIndex());

            Assert::notNull($schedule);
            if (false === $schedule->isFulfilled()) {
                $schedule->setFulfilledDate($payment->getUpdatedAt());
            }

            $this->scheduleRepository->add($schedule);
        }
    }

    public function processScheduleGeneration(MollieSubscriptionInterface $subscription): void
    {
        if (0 !== $subscription->getSchedules()->count()) {
            return;
        }

        foreach ($this->scheduleGenerator->generate($subscription) as $schedule) {
            $subscription->addSchedule($schedule);
        }
        $subscription->setStartedAt(new \DateTime());
    }
}
