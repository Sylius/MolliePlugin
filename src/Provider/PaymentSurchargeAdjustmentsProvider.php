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

namespace Sylius\MolliePlugin\Provider;

final class PaymentSurchargeAdjustmentsProvider implements PaymentSurchargeAdjustmentsProviderInterface
{
    /** @param string[] $types */
    public function __construct(private readonly array $types)
    {
    }

    public function getTypes(): array
    {
        return $this->types;
    }
}
