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

use Sylius\MolliePlugin\Model\AdjustmentInterface;
use Sylius\MolliePlugin\Provider\CustomerProvider;
use Sylius\MolliePlugin\Provider\CustomerProviderInterface;
use Sylius\MolliePlugin\Provider\DivisorProvider;
use Sylius\MolliePlugin\Provider\DivisorProviderInterface;
use Sylius\MolliePlugin\Provider\Methods\MollieMethodsProvider;
use Sylius\MolliePlugin\Provider\Methods\MollieMethodsProviderInterface;
use Sylius\MolliePlugin\Provider\PaymentDescriptionProvider;
use Sylius\MolliePlugin\Provider\PaymentDescriptionProviderInterface;
use Sylius\MolliePlugin\Provider\PaymentSurchargeAdjustmentsProvider;
use Sylius\MolliePlugin\Provider\PaymentSurchargeAdjustmentsProviderInterface;

return static function (ContainerConfigurator $container) {
    $container->parameters()->set('sylius_mollie.payment_surcharge_adjustments', [
        AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT,
        AdjustmentInterface::PERCENTAGE_ADJUSTMENT,
        AdjustmentInterface::PERCENTAGE_AND_AMOUNT_ADJUSTMENT,
    ]);

    $services = $container->services();

    $services->defaults()
        ->public();

    $services->set('sylius_mollie.provider.divisor', DivisorProvider::class);

    $services->alias(DivisorProviderInterface::class, 'sylius_mollie.provider.divisor');

    $services->set('sylius_mollie.provider.payment_surcharge_adjustments', PaymentSurchargeAdjustmentsProvider::class)
        ->args(['%sylius_mollie.payment_surcharge_adjustments%']);

    $services->alias(PaymentSurchargeAdjustmentsProviderInterface::class, 'sylius_mollie.provider.payment_surcharge_adjustments');

    $services->set('sylius_mollie.provider.customer', CustomerProvider::class)
        ->args([
            service('sylius.repository.customer'),
            service('sylius.factory.customer'),
        ]);

    $services->alias(CustomerProviderInterface::class, 'sylius_mollie.provider.customer');

    $services->set('sylius_mollie.provider.payment_description', PaymentDescriptionProvider::class)
        ->args([service('sylius_payum.provider.payment_description')]);

    $services->alias(PaymentDescriptionProviderInterface::class, 'sylius_mollie.provider.payment_description');

    $services->set('sylius_mollie.provider.methods.mollie_methods', MollieMethodsProvider::class)
        ->args([
            service('sylius_mollie.client.mollie_api'),
            service('sylius_mollie.logger.mollie_logger_action'),
        ]);

    $services->alias(MollieMethodsProviderInterface::class, 'sylius_mollie.provider.methods.mollie_methods');
};
