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

namespace Sylius\MolliePlugin\Voucher\Checker;

use Sylius\Component\Core\Model\OrderInterface;

interface ProductVoucherTypeCheckerInterface
{
    /**
     * @param array{
     *      data: array<string, string>,
     *      image: array<string, string>,
     *      issuers: array<string, mixed>|null,
     *      paymentFee: array<string, mixed>
     *  } $methods
     *
     * @return array{
     *     data: array<string, string>,
     *     image: array<string, string>,
     *     issuers: array<string, mixed>|null,
     *     paymentFee: array<string, mixed>
     * }
     */
    public function checkTheProductTypeOnCart(OrderInterface $order, array $methods): array;
}
