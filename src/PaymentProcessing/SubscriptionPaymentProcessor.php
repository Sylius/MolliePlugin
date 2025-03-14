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

namespace SyliusMolliePlugin\PaymentProcessing;

use SyliusMolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use SyliusMolliePlugin\Request\StateMachine\StatusRecurringSubscription;
use Payum\Core\Payum;
use Sylius\Component\Core\Model\PaymentInterface;

final class SubscriptionPaymentProcessor implements SubscriptionPaymentProcessorInterface
{
    private MollieSubscriptionRepositoryInterface $subscriptionRepository;

    private Payum $payum;

    public function __construct(
        MollieSubscriptionRepositoryInterface $subscriptionRepository,
        Payum $payum
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->payum = $payum;
    }

    public function processSuccess(PaymentInterface $payment): void
    {
        $this->handlePayment($payment);
    }

    public function processFailed(PaymentInterface $payment): void
    {
        $this->handlePayment($payment);
    }

    private function handlePayment(PaymentInterface $payment): void
    {
        $details = $payment->getDetails();

        if (false === isset($details['metadata']['gateway'])) {
            return;
        }

        $gateway = $this->payum->getGateway($details['metadata']['gateway']);
        $subscriptions = $this->subscriptionRepository->findByPayment($payment);

        foreach ($subscriptions as $subscription) {
            $gateway->execute(new StatusRecurringSubscription($subscription, $details['payment_mollie_id'] ?? null, $payment));
            $this->subscriptionRepository->add($subscription);
        }
    }
}
