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

namespace Sylius\MolliePlugin\EventListener\Workflow;

use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Processor\PaymentSurchargeProcessorInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final readonly class OrderCheckoutProcessPaymentSurchargeListener
{
    public function __construct(
        private PaymentSurchargeProcessorInterface $paymentSurchargeProcessor,
    ) {
    }

    public function __invoke(CompletedEvent $event): void
    {
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $this->paymentSurchargeProcessor->process($order);
    }
}
