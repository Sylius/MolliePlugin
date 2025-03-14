<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\PaymentProcessing;

use Sylius\Component\Core\Model\PaymentInterface;

interface SubscriptionPaymentProcessorInterface
{
    public function processSuccess(PaymentInterface $payment): void;

    public function processFailed(PaymentInterface $payment): void;
}
