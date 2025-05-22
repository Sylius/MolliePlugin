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

namespace Sylius\MolliePlugin\EventListener\Workflow\Payment;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\MolliePlugin\Subscription\Processor\SubscriptionPaymentProcessorInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final readonly class SubscriptionPaymentFailListener
{
    public function __construct(
        private SubscriptionPaymentProcessorInterface $subscriptionPaymentProcessor,
    ) {
    }

    public function __invoke(CompletedEvent $event): void
    {
        $payment = $event->getSubject();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        $this->subscriptionPaymentProcessor->processFailed($payment);
    }
}
