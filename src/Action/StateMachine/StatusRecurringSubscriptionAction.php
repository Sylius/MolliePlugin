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

namespace SyliusMolliePlugin\Action\StateMachine;

use SyliusMolliePlugin\Action\Api\BaseApiAwareAction;
use SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndPaymentIdApplicatorInterface;
use SyliusMolliePlugin\Action\StateMachine\Applicator\SubscriptionAndSyliusPaymentApplicatorInterface;
use SyliusMolliePlugin\Action\StateMachine\Transition\StateMachineTransitionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Request\StateMachine\StatusRecurringSubscription;
use SyliusMolliePlugin\Transitions\MollieSubscriptionTransitions;
use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;

final class StatusRecurringSubscriptionAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /** @var EntityManagerInterface */
    private $subscriptionManager;

    /** @var SubscriptionAndPaymentIdApplicatorInterface */
    private $subscriptionAndPaymentIdApplicator;

    /** @var SubscriptionAndSyliusPaymentApplicatorInterface */
    private $subscriptionAndSyliusPaymentApplicator;

    /** @var StateMachineTransitionInterface */
    private $stateMachineTransition;

    public function __construct(
        EntityManagerInterface $subscriptionManager,
        SubscriptionAndPaymentIdApplicatorInterface $subscriptionAndPaymentIdApplicator,
        SubscriptionAndSyliusPaymentApplicatorInterface $subscriptionAndSyliusPaymentApplicator,
        StateMachineTransitionInterface $stateMachineTransition
    ) {
        $this->subscriptionManager = $subscriptionManager;
        $this->subscriptionAndPaymentIdApplicator = $subscriptionAndPaymentIdApplicator;
        $this->subscriptionAndSyliusPaymentApplicator = $subscriptionAndSyliusPaymentApplicator;
        $this->stateMachineTransition = $stateMachineTransition;
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
