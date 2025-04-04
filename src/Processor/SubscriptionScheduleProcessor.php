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

namespace SyliusMolliePlugin\Processor;

use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Entity\OrderInterface;
use SyliusMolliePlugin\Generator\SubscriptionScheduleGeneratorInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class SubscriptionScheduleProcessor implements SubscriptionScheduleProcessorInterface
{
    private RepositoryInterface $scheduleRepository;

    private SubscriptionScheduleGeneratorInterface $scheduleGenerator;

    public function __construct(
        RepositoryInterface $scheduleRepository,
        SubscriptionScheduleGeneratorInterface $scheduleGenerator
    ) {
        $this->scheduleRepository = $scheduleRepository;
        $this->scheduleGenerator = $scheduleGenerator;
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
