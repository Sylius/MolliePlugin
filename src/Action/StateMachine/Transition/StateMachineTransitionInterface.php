<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Action\StateMachine\Transition;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

interface StateMachineTransitionInterface
{
    public function apply(MollieSubscriptionInterface $subscription, string $transitions): void;
}
