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

namespace Sylius\MolliePlugin\Refund\Repository\Query;

use Sylius\RefundPlugin\Entity\CreditMemoInterface;

interface CreditMemosByOrderNumberDateTimeAndAmountQueryInterface
{
    /** @return iterable<CreditMemoInterface> */
    public function __invoke(string $orderNumber, \DateTime $dateTime, int $amount): iterable;
}
