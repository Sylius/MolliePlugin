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

namespace Sylius\MolliePlugin\Form\Type;

use Sylius\Bundle\ProductBundle\Form\Type\ProductType as ProductFormType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Sylius\MolliePlugin\Documentation\DocumentationLinksInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigTranslationInterface;
use Sylius\MolliePlugin\Entity\ProductType;
use Sylius\MolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Form\Type\Translation\MollieGatewayConfigTranslationType;
use Sylius\MolliePlugin\Options\Country\Options as CountryOptions;
use Sylius\MolliePlugin\Payments\Methods\AbstractMethod;
use Sylius\MolliePlugin\Payments\PaymentTerms\Options;
use Sylius\MolliePlugin\Validator\Constraints\PaymentSurchargeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class MollieGatewayConfigType extends AbstractResourceType
{
    public function __construct(
        string $dataClass,
        private readonly DocumentationLinksInterface $documentationLinks,
        private readonly string $defaultLocale,
        array $validationGroups = [],
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius_mollie_plugin.ui.enable',
            ])
            ->add('applePayDirectButton', CheckboxType::class, [
                'label' => 'sylius_mollie_plugin.ui.enabled_buy_now_button',
                'help' => 'sylius_mollie_plugin.form.enabled_buy_now_button_help',
            ])
            ->add('defaultCategory', EntityType::class, [
                'class' => ProductType::class,
                'label' => 'sylius_mollie_plugin.form.product_type_default',
                'placeholder' => 'sylius_mollie_plugin.form.no_category',
                'empty_data' => null,
                'help' => 'sylius_mollie_plugin.form.product_type_default_help',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'label' => 'sylius_mollie_plugin.ui.payment_name',
                'entry_type' => MollieGatewayConfigTranslationType::class,
            ])
            ->add('paymentType', ChoiceType::class, [
                'label' => 'sylius_mollie_plugin.ui.payment_type',
                'choices' => Options::getAvailablePaymentType(),
                'help' => $this->documentationLinks->getPaymentMethodDoc(),
                'help_html' => true,
            ])
            ->add('qrCodeEnabled', CheckboxType::class, [
                'label' => 'sylius_mollie_plugin.ui.qr_code',
            ])
            ->add('paymentDescription', TextType::class, [
                'label' => 'sylius_mollie_plugin.form.payment_methods.payment_description',
                'help' => 'sylius_mollie_plugin.form.payment_methods.payment_description_help',
                'empty_data' => '{ordernumber}',
                'attr' => [
                    'placeholder' => '{ordernumber}',
                ],
            ])
            ->add('paymentSurchargeFee', PaymentSurchargeFeeType::class, [
                'label' => false,
                'constraints' => [new PaymentSurchargeType(['groups' => 'sylius'])],
            ])
            ->add('amountLimits', MollieMinMaxType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('customizeMethodImage', CustomizeMethodImageType::class, [
                'label' => false,
            ])
            ->add('country_restriction', ChoiceType::class, [
                'label' => 'sylius_mollie_plugin.ui.country_level_restriction',
                'choices' => CountryOptions::getCountriesConfigOptions(),
            ])
            ->add('countryLevel_excluded', CountryType::class, [
                'label' => 'sylius_mollie_plugin.ui.country_level_exclude',
                'required' => false,
                'multiple' => true,
            ])
            ->add('countryLevel_allowed', CountryType::class, [
                'label' => 'sylius_mollie_plugin.ui.country_level_allow',
                'required' => false,
                'multiple' => true,
            ])
            ->add('countryLevel', CountryType::class, [
                'label' => 'sylius_mollie_plugin.ui.country_level_restriction',
                'required' => false,
                'multiple' => true,
            ])
            ->add('orderExpirationDays', ChoiceType::class, [
                'label' => 'sylius_mollie_plugin.ui.order_expiration_days',
                'required' => false,
                'choices' => array_combine(
                    range(1, 100, 1),
                    range(1, 100, 1),
                ),
            ])
            ->add('loggerEnabled', CheckboxType::class, [
                'label' => 'sylius_mollie_plugin.ui.debug_level_enabled',
            ])
            ->add('loggerLevel', ChoiceType::class, [
                'label' => 'sylius_mollie_plugin.ui.debug_level_log',
                'choices' => Options::getDebugLevels(),
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                /** @var MollieGatewayConfigInterface $object */
                $object = $event->getData();
                $form = $event->getForm();

                $gateway = $object->getGateway();
                $factoryName = $gateway->getFactoryName();

                if (MollieSubscriptionGatewayFactory::FACTORY_NAME === $factoryName) {
                    $form->remove('paymentType');
                    $form->add('paymentType', ChoiceType::class, [
                        'label' => 'sylius_mollie_plugin.ui.payment_type',
                        'choices' => Options::getAvailablePaymentType(),
                        'help' => $this->documentationLinks->getPaymentMethodDoc(),
                        'help_html' => true,
                        'empty_data' => Options::PAYMENT_API_VALUE,
                        'attr' => [
                            'disabled' => 'disabled',
                        ],
                    ]);
                }

                if (false === $object->hasTranslationLocale($this->defaultLocale)) {
                    /** @var MollieGatewayConfigTranslationInterface $translation */
                    $translation = $object->getTranslation($this->defaultLocale);
                    $translation->setName($object->getName());
                }
            })
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
                $form = $event->getForm();
                /** @var MollieGatewayConfigInterface $object */
                $object = $form->getData();
                $data = $event->getData();

                if (in_array($object->getMethodId(), Options::getOnlyOrderAPIMethods(), true)) {
                    $form->remove('paymentType');
                    $form->add('paymentType', ChoiceType::class, [
                        'label' => 'sylius_mollie_plugin.ui.payment_type',
                        'choices' => Options::getAvailablePaymentType(),
                        'help' => $this->documentationLinks->getPaymentMethodDoc(),
                        'help_html' => true,
                        'attr' => [
                            'disabled' => 'disabled',
                        ],
                    ]);
                }

                $event->setData($data);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                /** @var MollieGatewayConfigInterface $object */
                $object = $event->getForm()->getData();
                $data = $event->getData();

                if (in_array($object->getMethodId(), Options::getOnlyOrderAPIMethods(), true)) {
                    $data['paymentType'] = AbstractMethod::ORDER_API;
                }

                if (in_array($object->getMethodId(), Options::getOnlyPaymentAPIMethods(), true)) {
                    $data['paymentType'] = AbstractMethod::PAYMENT_API;
                }

                $event->setData($data);
            });
    }

    public function getBlockPrefix(): string
    {
        return 'mollie_payment_method';
    }

    public static function getExtendedTypes(): array
    {
        return [ProductFormType::class];
    }
}
