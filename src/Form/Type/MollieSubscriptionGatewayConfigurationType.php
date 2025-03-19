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

namespace Sylius\MolliePlugin\Form\Type;

use Symfony\Component\Form\AbstractType;

final class MollieSubscriptionGatewayConfigurationType extends AbstractType
{
    public function getParent(): string
    {
        return MollieGatewayConfigurationType::class;
    }
}
