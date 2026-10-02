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

use Sylius\Bundle\CoreBundle\Form\Type\Checkout\PaymentType;
use Sylius\Bundle\PaymentBundle\Form\Type\GatewayConfigType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType;
use Sylius\MolliePlugin\Form\Extension\GatewayConfigTypeExtension;
use Sylius\MolliePlugin\Form\Extension\PaymentTypeExtension;
use Sylius\MolliePlugin\Form\Extension\ProductTypeExtension;
use Sylius\MolliePlugin\Form\Extension\ProductVariantRecurringExtension;
use Sylius\MolliePlugin\Payum\Checker\MollieGatewayFactoryCheckerInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.form.extension.type.product_variant_recurring', ProductVariantRecurringExtension::class)
        ->args([service('sylius_mollie.form.resolver.product_variant_validation_groups')])
        ->tag('form.type_extension', ['extended_type' => ProductVariantType::class]);

    $services->set('sylius_mollie.form.extension.type.payment', PaymentTypeExtension::class)
        ->args([service(MollieGatewayFactoryCheckerInterface::class)])
        ->tag('form.type_extension', ['extended_type' => PaymentType::class]);

    $services->set('sylius_mollie.form.extension.type.gateway_config', GatewayConfigTypeExtension::class)
        ->tag('form.type_extension', ['extended_type' => GatewayConfigType::class]);

    $services->set('sylius_mollie.form.extension.type.product_type', ProductTypeExtension::class)
        ->tag('form.type_extension', ['extended_type' => ProductType::class]);
};
