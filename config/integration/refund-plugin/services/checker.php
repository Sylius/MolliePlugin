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

use Sylius\MolliePlugin\Refund\Checker\DuplicateRefundTheSameAmountChecker;
use Sylius\MolliePlugin\Refund\Checker\DuplicateRefundTheSameAmountCheckerInterface;
use Sylius\MolliePlugin\Refund\Checker\MollieOrderRefundChecker;
use Sylius\MolliePlugin\Refund\Checker\MollieOrderRefundCheckerInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.refund.checker.duplicate_refund_the_same_amount', DuplicateRefundTheSameAmountChecker::class)
        ->args([
            service('sylius_mollie.repository.query.credit_memo.by_order_id_date_time_and_amount'),
            service('sylius_refund.filter.unit_refund'),
        ]);

    $services->alias(DuplicateRefundTheSameAmountCheckerInterface::class, 'sylius_mollie.refund.checker.duplicate_refund_the_same_amount');

    $services->set('sylius_mollie.refund.checker.mollie_order_refund', MollieOrderRefundChecker::class);

    $services->alias(MollieOrderRefundCheckerInterface::class, 'sylius_mollie.refund.checker.mollie_order_refund');
};
