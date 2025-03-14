<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Refund;

use Mollie\Api\Resources\Payment;

interface PaymentRefundInterface
{
    public function refund(Payment $payment): void;
}
