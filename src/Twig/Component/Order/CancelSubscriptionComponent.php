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

namespace Sylius\MolliePlugin\Twig\Component\Order;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\UiBundle\Twig\Component\TemplatePropTrait;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use Sylius\MolliePlugin\StateMachine\MollieRecurringTransitions;
use Sylius\TwigHooks\Twig\Component\HookableComponentTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PostMount;

class CancelSubscriptionComponent
{
    use HookableComponentTrait;
    use TemplatePropTrait;

    public OrderInterface $order;

    #[ExposeInTemplate(name: 'cancellable_subscription')]
    public bool $cancellableSubscription = false;

    #[ExposeInTemplate(name: 'order_token')]
    public ?string $orderToken = null;

    #[ExposeInTemplate(name: 'subscription_id')]
    public int|string|null $subscriptionId = null;

    public function __construct(
        protected readonly MollieSubscriptionRepositoryInterface $subscriptionRepository,
        protected readonly StateMachineInterface $stateMachine,
    ) {
    }

    #[PostMount]
    public function postMount(): void
    {
        $this->orderToken = $this->order->getTokenValue();
        $subscription = $this->subscriptionRepository->findOneActiveByOrderToken($this->orderToken);

        if ($subscription !== null) {
            $this->subscriptionId = $subscription->getId();
            $this->cancellableSubscription = $this->stateMachine->can(
                $subscription,
                MollieRecurringTransitions::GRAPH_MANUAL,
                MollieRecurringTransitions::TRANSITION_CANCEL,
            );
        }
    }
}
