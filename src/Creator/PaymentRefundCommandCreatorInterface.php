<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Creator;

use Mollie\Api\Resources\Payment;
use Sylius\RefundPlugin\Command\RefundUnits;

interface PaymentRefundCommandCreatorInterface
{
    public function fromPayment(Payment $payment): RefundUnits;
}
