<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Action\StateMachine\Applicator;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;

interface SubscriptionAndPaymentIdApplicatorInterface
{
    public function execute(MollieSubscriptionInterface $subscription, string $paymentId): void;
}
