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

namespace Sylius\MolliePlugin\Payum\Request\Subscription;

use Payum\Core\Request\Generic;
use Sylius\Component\Core\Model\PaymentInterface;

class StatusRecurringSubscription extends Generic
{
    public function __construct(
        $model,
        private ?string $paymentId = null,
        private ?PaymentInterface $payment = null,
    ) {
        parent::__construct($model);
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
