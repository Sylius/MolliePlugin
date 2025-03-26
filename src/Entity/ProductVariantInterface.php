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

namespace Sylius\MolliePlugin\Entity;

use Sylius\Component\Core\Model\ProductVariantInterface as BaseProductVariantInterface;

interface ProductVariantInterface extends BaseProductVariantInterface
{
    public function isRecurring(): bool;

    public function setRecurring(bool $recurring): void;

    public function getTimes(): ?int;

    public function setTimes(?int $times): void;

    public function getInterval(): ?string;

    public function setInterval(string $interval): void;
}
