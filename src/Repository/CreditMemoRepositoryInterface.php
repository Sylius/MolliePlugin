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

namespace Sylius\MolliePlugin\Repository;

use Sylius\RefundPlugin\Entity\CreditMemoInterface;
use Sylius\RefundPlugin\Repository\CreditMemoRepositoryInterface as BaseCreditMemoRepositoryInterface;

interface CreditMemoRepositoryInterface extends BaseCreditMemoRepositoryInterface
{
    /** @return CreditMemoInterface[] */
    public function findByOrderNumberAndDateTime(
        int $orderId,
        \DateTime $dateTime,
        int $amount,
    ): array;
}
