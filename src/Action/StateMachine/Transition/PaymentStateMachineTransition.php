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

namespace SyliusMolliePlugin\Action\StateMachine\Transition;

use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;
use SM\Factory\FactoryInterface;

final class PaymentStateMachineTransition implements PaymentStateMachineTransitionInterface
{
    /** @var FactoryInterface */
    private $subscriptionStateMachineFactory;

    public function __construct(FactoryInterface $subscriptionStateMachineFactory)
    {
        $this->subscriptionStateMachineFactory = $subscriptionStateMachineFactory;
    }

    public function apply(
        MollieSubscriptionInterface $subscription,
        string $transitions
    ): void {
        $stateMachine = $this->subscriptionStateMachineFactory->get(
            $subscription,
            MollieSubscriptionPaymentProcessingTransitions::GRAPH
        );

        if (!$stateMachine->can($transitions)) {
            return;
        }

        $stateMachine->apply($transitions);
    }
}
