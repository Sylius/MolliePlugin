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

namespace Sylius\MolliePlugin\Resolver;

interface MolliePaymentsMethodResolverInterface
{
    /**
     * @return array{
     *     data: array<string, string>,
     *     image: array<string, string>,
     *     issuers: array<string, mixed>|null,
     *     paymentFee: array<string, mixed>
     * }
     *
     * @see \Mollie\Api\Resources\Method
     */
    public function resolve(): array;
}
