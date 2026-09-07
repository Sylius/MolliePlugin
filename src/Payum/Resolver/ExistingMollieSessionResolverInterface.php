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

namespace Sylius\MolliePlugin\Payum\Resolver;

use Mollie\Api\Resources\Order as MollieOrder;
use Mollie\Api\Resources\Payment as MolliePayment;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Security\TokenInterface;

interface ExistingMollieSessionResolverInterface
{
    /** @param ArrayObject $details `Payment::details` of the payment being captured */
    public function resolve(
        MollieOrder|MolliePayment $resource,
        ArrayObject $details,
        ?TokenInterface $token,
    ): ExistingMollieSessionDecision;
}
