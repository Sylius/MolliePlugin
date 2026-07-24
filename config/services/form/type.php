<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\MolliePlugin\Form\Type\CountriesRestrictionChoiceType;
use Sylius\MolliePlugin\Form\Type\CustomizeMethodImageType;
use Sylius\MolliePlugin\Form\Type\LoggerLevelChoiceType;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigType;
use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigurationType;
use Sylius\MolliePlugin\Form\Type\MollieIntervalType;
use Sylius\MolliePlugin\Form\Type\MollieSubscriptionGatewayConfigurationType;
use Sylius\MolliePlugin\Form\Type\PaymentLinkType;
use Sylius\MolliePlugin\Form\Type\PaymentMollieType;
use Sylius\MolliePlugin\Form\Type\PaymentSurchargeFeeType;
use Sylius\MolliePlugin\Form\Type\PaymentSurchargeFeeTypeChoiceType;
use Sylius\MolliePlugin\Form\Type\PaymentTypeChoiceType;
use Sylius\MolliePlugin\Form\Type\ProductTypeType;
use Sylius\MolliePlugin\Form\Type\Translation\MollieGatewayConfigTranslationType;
use Sylius\MolliePlugin\Form\Type\Translation\TemplateMollieEmailTranslationType;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_mollie.form.type.mollie_gateway_configuration', MollieGatewayConfigurationType::class)
        ->args([service('sylius_mollie.client.mollie_api')])
        ->tag('sylius.gateway_configuration_type', ['type' => 'mollie', 'label' => 'sylius_mollie.ui.mollie_gateway_label'])
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.mollie_subscription_gateway_configuration', MollieSubscriptionGatewayConfigurationType::class)
        ->tag('sylius.gateway_configuration_type', ['type' => 'mollie_subscription', 'label' => 'sylius_mollie.ui.mollie_subscription_gateway_label'])
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.mollie_payment', PaymentMollieType::class)
        ->args([service('sylius_mollie.resolver.payment_methods')])
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.mollie_interval', MollieIntervalType::class)
        ->args([service('sylius_mollie.form.type.data_transformer.mollie_interval')])
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.mollie_gateway_config', MollieGatewayConfigType::class)
        ->args([
            '%sylius_mollie.model.mollie_gateway_config.class%',
            '%sylius_mollie.form.type.mollie_gateway_config.validation_groups%',
            '%sylius_locale.locale%',
        ])
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.payment_surcharge_fee', PaymentSurchargeFeeType::class)
        ->args([
            '%sylius_mollie.model.payment_surcharge_fee.class%',
            '%sylius_mollie.form.type.payment_methods.payment_surcharge_fee.validation_groups%',
        ])
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.customize_method_image', CustomizeMethodImageType::class)
        ->args(['%sylius_mollie.model.mollie_method_image.class%'])
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.product_type', ProductTypeType::class)
        ->args([
            '%sylius_mollie.model.product_type.class%',
            '%sylius_mollie.form.type.mollie.validation_groups%',
        ])
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.translation.block_translation', TemplateMollieEmailTranslationType::class)
        ->args(['%sylius_mollie.model.template_mollie_email_translation.class%'])
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.translation.payment_method_translation', MollieGatewayConfigTranslationType::class)
        ->args(['%sylius_mollie.model.mollie_gateway_config_translation.class%'])
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.countries_restriction_choice', CountriesRestrictionChoiceType::class)
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.payment_type_choice', PaymentTypeChoiceType::class)
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.payment_surcharge_type_choice', PaymentSurchargeFeeTypeChoiceType::class)
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.logger_level_choice', LoggerLevelChoiceType::class)
        ->tag('form.type');

    $services->set('sylius_mollie.form.type.payment_link', PaymentLinkType::class)
        ->tag('form.type');
};
