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

namespace Sylius\MolliePlugin\Updater;

/**
 * @phpstan-type PositionsArray = array<array-key, array{
 *     name: string,
 *     identifier: int|string,
 *     id: string,
 * }>
 */
interface MolliePaymentMethodPositionUpdaterInterface
{
    /** @param PositionsArray $positions */
    public function update(array $positions): void;
}
