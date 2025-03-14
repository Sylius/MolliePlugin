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

namespace Sylius\MolliePlugin\Action\StateMachine;

use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\MolliePlugin\Action\Api\BaseApiAwareAction;
use Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use Sylius\MolliePlugin\Action\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;
use Sylius\MolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Request\StateMachine\StatusRecurringSubscription;
use Sylius\MolliePlugin\Transitions\MollieSubscriptionTransitions;

final class StatusRecurringSubscriptionAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    public function __construct(private readonly EntityManagerInterface $subscriptionManager, private readonly SubscriptionAndPaymentIdApplicatorInterface $subscriptionAndPaymentIdApplicator, private readonly SubscriptionAndSyliusPaymentApplicatorInterface $subscriptionAndSyliusPaymentApplicator, private readonly StateMachineTransitionInterface $stateMachineTransition)
    {
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

        $this->stateMachineTransition->apply($subscription, MollieSubscriptionTransitions::TRANSITION_COMPLETE);
        $this->stateMachineTransition->apply($subscription, MollieSubscriptionTransitions::TRANSITION_ABORT);

        $this->subscriptionManager->persist($subscription);
        $this->subscriptionManager->flush();
    }

    public function supports($request): bool
    {
        return
            $request instanceof StatusRecurringSubscription &&
            $request->getModel() instanceof MollieSubscriptionInterface;
    }
}
