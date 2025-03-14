<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Action\StateMachine\Applicator;

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface SubscriptionAndSyliusPaymentApplicatorInterface
{
    public function execute(MollieSubscriptionInterface $subscription, PaymentInterface $payment): void;
}
