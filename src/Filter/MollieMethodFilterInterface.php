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

namespace Sylius\MolliePlugin\Filter;

use Sylius\Component\Payment\Model\PaymentMethodInterface;

interface MollieMethodFilterInterface
{
    /**
     * @param PaymentMethodInterface[] $paymentMethods
     *
     * @return PaymentMethodInterface[]
     */
    public function nonRecurringFilter(array $paymentMethods): array;

    /**
     * @param PaymentMethodInterface[] $paymentMethods
     *
     * @return PaymentMethodInterface[]
     */
    public function recurringFilter(array $paymentMethods): array;
}
