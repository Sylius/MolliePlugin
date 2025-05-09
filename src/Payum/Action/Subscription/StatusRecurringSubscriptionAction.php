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

namespace Sylius\MolliePlugin\Payum\Action\Subscription;

use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Payum\Action\BaseApiAwareAction;
use Sylius\MolliePlugin\Payum\Request\Subscription\StatusRecurringSubscription;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use Sylius\MolliePlugin\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;
use Sylius\MolliePlugin\StateMachine\MollieSubscriptionTransitions;

final class StatusRecurringSubscriptionAction extends BaseApiAwareAction
{
    public function __construct(
        private readonly EntityManagerInterface $subscriptionManager,
        private readonly SubscriptionAndPaymentIdApplicatorInterface $subscriptionAndPaymentIdApplicator,
        private readonly SubscriptionAndSyliusPaymentApplicatorInterface $subscriptionAndSyliusPaymentApplicator,
        private readonly StateMachineInterface $stateMachine,
    ) {
    }

    /** @param StatusRecurringSubscription|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var MollieSubscriptionInterface $subscription */
        $subscription = $request->getModel();
        $paymentId = $request->getPaymentId();
        $syliusPayment = $request->getPayment();

        if (null !== $paymentId) {
            $this->subscriptionAndPaymentIdApplicator->execute($subscription, $paymentId);
        }

        if (null !== $syliusPayment) {
            $this->subscriptionAndSyliusPaymentApplicator->execute($subscription, $syliusPayment);
        }

        $this->apply($subscription, MollieSubscriptionTransitions::TRANSITION_COMPLETE);
        $this->apply($subscription, MollieSubscriptionTransitions::TRANSITION_ABORT);

        $this->subscriptionManager->persist($subscription);
        $this->subscriptionManager->flush();
    }

    public function supports($request): bool
    {
        return
            $request instanceof StatusRecurringSubscription &&
            $request->getModel() instanceof MollieSubscriptionInterface;
    }

    private function apply(MollieSubscriptionInterface $subscription, string $transition): void
    {
        if ($this->stateMachine->can($subscription, MollieSubscriptionTransitions::GRAPH, $transition)) {
            $this->stateMachine->apply($subscription, MollieSubscriptionTransitions::GRAPH, $transition);
        }
    }
}
