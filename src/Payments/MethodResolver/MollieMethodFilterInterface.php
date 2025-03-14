<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Payments\MethodResolver;

use Sylius\Component\Core\Model\PaymentMethodInterface;

interface MollieMethodFilterInterface
{
    /** @return PaymentMethodInterface[] */
    public function nonRecurringFilter(array $paymentMethods): array;

    /** @return PaymentMethodInterface[] */
    public function recurringFilter(array $paymentMethods): array;
}
