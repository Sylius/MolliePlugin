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

use Sylius\MolliePlugin\Payum\Provider\PaymentTokenProvider;
use Sylius\MolliePlugin\Payum\Provider\PaymentTokenProviderInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.payum.provider.payment_token', PaymentTokenProvider::class)
        ->args([
            service('payum'),
            'sylius_shop_order_after_pay',
        ]);

    $services->alias(PaymentTokenProviderInterface::class, 'sylius_mollie.payum.provider.payment_token');
};
