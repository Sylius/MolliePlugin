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

namespace SyliusMolliePlugin\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

final class MollieSubscriptionState implements FilterInterface
{
    public function apply(
        DataSourceInterface $dataSource,
        string $name,
        $data,
        array $options
    ): void {
        if (false === array_key_exists('state', $data) || [] === $data['state']) {
            return;
        }

        $dataSource->restrict($dataSource->getExpressionBuilder()->in('state', $data['state']));
    }
}
