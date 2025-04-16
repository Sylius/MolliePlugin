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
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigTranslationInterface;
use Sylius\MolliePlugin\Entity\ProductType;
use Sylius\MolliePlugin\Form\Type\Translation\MollieGatewayConfigTranslationType;
use Sylius\MolliePlugin\Model\ApiType;
use Sylius\MolliePlugin\Model\ApiTypeRestrictedPaymentMethods;
use Sylius\MolliePlugin\Payum\Factory\MollieSubscriptionGatewayFactory;
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
        array $validationGroups,
        private readonly string $defaultLocale,
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius_mollie.ui.enable',
            ])
            ->add('applePayDirectButton', CheckboxType::class, [
                'label' => 'sylius_mollie.ui.enabled_buy_now_button',
                'help' => 'sylius_mollie.form.enabled_buy_now_button_help',
            ])
            ->add('defaultCategory', EntityType::class, [
                'class' => ProductType::class,
                'label' => 'sylius_mollie.form.product_type_default',
                'placeholder' => 'sylius_mollie.form.no_category',
                'empty_data' => null,
                'help' => 'sylius_mollie.form.product_type_default_help',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'label' => 'sylius_mollie.ui.payment_name',
                'entry_type' => MollieGatewayConfigTranslationType::class,
            ])
            ->add('paymentType', PaymentTypeChoiceType::class)
            ->add('qrCodeEnabled', CheckboxType::class, [
                'label' => 'sylius_mollie.ui.qr_code',
            ])
            ->add('paymentDescription', TextType::class, [
                'label' => 'sylius_mollie.form.payment_methods.payment_description',
                'help' => 'sylius_mollie.form.payment_methods.payment_description_help',
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
            ->add('country_restriction', CountriesRestrictionChoiceType::class)
            ->add('countryLevel_excluded', CountryType::class, [
                'label' => 'sylius_mollie.ui.country_level_exclude',
                'required' => false,
                'multiple' => true,
            ])
            ->add('countryLevel_allowed', CountryType::class, [
                'label' => 'sylius_mollie.ui.country_level_allow',
                'required' => false,
                'multiple' => true,
            ])
            ->add('countryLevel', CountryType::class, [
                'label' => 'sylius_mollie.ui.country_level_restriction',
                'required' => false,
                'multiple' => true,
            ])
            ->add('orderExpirationDays', ChoiceType::class, [
                'label' => 'sylius_mollie.ui.order_expiration_days',
                'required' => false,
                'choices' => array_combine(
                    range(1, 100, 1),
                    range(1, 100, 1),
                ),
            ])
            ->add('loggerEnabled', CheckboxType::class, [
                'label' => 'sylius_mollie.ui.debug_level_enabled',
            ])
            ->add('loggerLevel', LoggerLevelChoiceType::class, [
                'log_type' => LoggerLevelChoiceType::TYPE_DEBUG,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                /** @var MollieGatewayConfigInterface $object */
                $object = $event->getData();
                $form = $event->getForm();

                $gateway = $object->getGateway();
                $factoryName = $gateway->getFactoryName();

                if (MollieSubscriptionGatewayFactory::FACTORY_NAME === $factoryName) {
                    $form->remove('paymentType');
                    $form->add('paymentType', PaymentTypeChoiceType::class, [
                        'empty_data' => ApiType::PAYMENT_API_VALUE,
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

                if (in_array($object->getMethodId(), ApiTypeRestrictedPaymentMethods::onlyOrderApi(), true)) {
                    $form->remove('paymentType');
                    $form->add('paymentType', PaymentTypeChoiceType::class, [
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

                if (in_array($object->getMethodId(), ApiTypeRestrictedPaymentMethods::onlyOrderApi(), true)) {
                    $data['paymentType'] = ApiType::ORDER_API_VALUE;
                }

                if (in_array($object->getMethodId(), ApiTypeRestrictedPaymentMethods::onlyPaymentApi(), true)) {
                    $data['paymentType'] = ApiType::PAYMENT_API_VALUE;
                }

                $event->setData($data);
            });
    }

    public function getBlockPrefix(): string
    {
        return 'mollie_payment_method';
    }

    /** @return array<array-key, class-string> */
    public static function getExtendedTypes(): array
    {
        return [ProductFormType::class];
    }
}
