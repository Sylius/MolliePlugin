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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Refund\Repository\Query\CreditMemosByOrderNumberDateTimeAndAmountQuery;
use Sylius\MolliePlugin\Refund\Repository\Query\CreditMemosByOrderNumberDateTimeAndAmountQueryInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.repository.query.credit_memo.by_order_id_date_time_and_amount', CreditMemosByOrderNumberDateTimeAndAmountQuery::class)
        ->args([
            service('sylius.repository.order'),
            service('sylius_refund.repository.credit_memo'),
        ]);

    $services->alias(CreditMemosByOrderNumberDateTimeAndAmountQueryInterface::class, 'sylius_mollie.repository.query.credit_memo.by_order_id_date_time_and_amount');
};
