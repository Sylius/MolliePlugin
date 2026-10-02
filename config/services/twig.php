<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\Bundle\UiBundle\Twig\Component\ResourceFormComponent;
use Sylius\MolliePlugin\Form\Type\TemplateMollieEmailType;
use Sylius\MolliePlugin\Twig\Component\Order\CancelSubscriptionComponent;
use Sylius\MolliePlugin\Twig\Extension\ApplePayDirectEnabled;
use Sylius\MolliePlugin\Twig\Extension\CustomerCreditCards;
use Sylius\MolliePlugin\Twig\Extension\DivisorProvider;
use Sylius\MolliePlugin\Twig\Extension\LegacyRefundExtension;
use Sylius\MolliePlugin\Twig\Extension\MolliePluginLatestVersion;
use Sylius\MolliePlugin\Twig\Parser\ContentParser;
use Sylius\MolliePlugin\Twig\Parser\ContentParserInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius_mollie.twig.functions', ['sylius_mollie_render_email_template']);

    $services->set('sylius_mollie.twig.parser.content', ContentParser::class)
        ->public()
        ->args([
            service('twig'),
            '%sylius_mollie.twig.functions%',
            service('translator'),
        ]);

    $services->alias(ContentParserInterface::class, 'sylius_mollie.twig.parser.content');

    $services->set('sylius_mollie.twig.extension.mollie_plugin_latest_version', MolliePluginLatestVersion::class)
        ->tag('twig.extension');

    $services->set('sylius_mollie.twig.extension.customer_credit_cards', CustomerCreditCards::class)
        ->args([
            service('sylius_mollie.repository.mollie_customer'),
            service('sylius.context.customer'),
        ])
        ->tag('twig.extension');

    $services->set('sylius_mollie.twig.extension.apple_pay_direct_enabled', ApplePayDirectEnabled::class)
        ->args([service('sylius_mollie.apple_pay.checker.apple_pay_enabled')])
        ->tag('twig.extension');

    $services->set('sylius_mollie.twig.extension.divisor_provider', DivisorProvider::class)
        ->args([service('sylius_mollie.provider.divisor')])
        ->tag('twig.extension');

    $services->set('sylius_mollie.twig.extension.legacy_refund', LegacyRefundExtension::class)
        ->args([service('sylius_mollie.payum.checker.mollie_gateway_factory')])
        ->tag('twig.extension');

    $services->set('sylius_mollie.twig.component.email_template.form', ResourceFormComponent::class)
        ->args([
            service('sylius_mollie.repository.template_mollie_email'),
            service('form.factory'),
            '%sylius_mollie.model.template_mollie_email.class%',
            TemplateMollieEmailType::class,
        ])
        ->tag('sylius.live_component.admin', ['key' => 'sylius_mollie:admin:email_template:form']);

    $services->set('sylius_mollie.twig.component.order.cancel_subscription', CancelSubscriptionComponent::class)
        ->args([
            service('sylius_mollie.repository.mollie_subscription'),
            service('sylius_abstraction.state_machine'),
        ])
        ->tag('sylius.twig_component', ['key' => 'sylius_mollie:shop:order:cancel_subscription']);
};
