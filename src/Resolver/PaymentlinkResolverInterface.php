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

namespace SyliusMolliePlugin\Resolver;

use Mollie\Api\Types\PaymentMethod;
use Sylius\Component\Core\Model\OrderInterface;

interface PaymentlinkResolverInterface
{
    public const NO_AVAILABLE_METHODS = [
        PaymentMethod::KLARNA_PAY_LATER,
        PaymentMethod::KLARNA_SLICE_IT,
        PaymentMethod::IN3
    ];

    public function resolve(
        OrderInterface $order,
        array $data,
        string $templateName
    ): string;
}
