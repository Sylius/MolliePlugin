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

namespace SyliusMolliePlugin\Request\StateMachine;

use Payum\Core\Request\Generic;
use Sylius\Component\Core\Model\PaymentInterface;

class StatusRecurringSubscription extends Generic
{
    private ?string $paymentId;

    private ?PaymentInterface $payment;

    public function __construct(
        $model,
        string $paymentId = null,
        PaymentInterface $payment = null
    ) {
        parent::__construct($model);
        $this->paymentId = $paymentId;
        $this->payment = $payment;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function getPayment(): ?PaymentInterface
    {
        return $this->payment;
    }
}
