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

use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Validator\Constraints\LiveApiKeyIsNotBlank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

final class MollieGatewayConfigurationType extends AbstractType
{
    public const API_KEY_LIVE = 'api_key_live';

    public const API_KEY_TEST = 'api_key_test';

    public function __construct(private readonly MollieApiClient $apiClient)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('environment', ChoiceType::class, [
                'label' => 'sylius_mollie.ui.environment',
                'choices' => [
                    'sylius_mollie.ui.api_key_choice_test' => null,
                    'sylius_mollie.ui.api_key_choice_live' => true,
                ],
            ])
            ->add('profile_id', HiddenType::class, [
            ])
            ->add(self::API_KEY_TEST, PasswordType::class, [
                'always_empty' => false,
                'label' => 'sylius_mollie.ui.api_key_test',
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius_mollie.api_key.not_blank',
                        'groups' => ['sylius'],
                    ]),
                    new Regex([
                        'message' => 'sylius_mollie.api_key.invalid_test',
                        'groups' => ['sylius'],
                        'pattern' => '/^(test)_\w{0,}$/',
                    ]),
                    new Length([
                        'minMessage' => 'sylius_mollie.api_key.min_length',
                        'groups' => ['sylius'],
                        'min' => 35,
                    ]),
                ],
            ])
            ->add(self::API_KEY_LIVE, PasswordType::class, [
                'always_empty' => false,
                'required' => true,
                'label' => 'sylius_mollie.ui.api_key_live',
                'constraints' => [
                    new Regex([
                        'message' => 'sylius_mollie.api_key.invalid_live',
                        'groups' => ['sylius'],
                        'pattern' => '/^(live)_\w{0,}$/',
                    ]),
                    new Length([
                        'minMessage' => 'sylius_mollie.api_key.min_length',
                        'groups' => ['sylius'],
                        'min' => 35,
                    ]),
                ],
            ])
            ->add('abandoned_email_enabled', CheckboxType::class, [
                'label' => 'sylius_mollie.ui.abandoned_email_enabled',
                'help' => 'sylius_mollie.ui.abandoned_description',
            ])
            ->add('abandoned_hours', ChoiceType::class, [
                'label' => 'sylius_mollie.ui.abandoned_hours',
                'choices' => array_combine(
                    range(1, 200, 1),
                    range(1, 200, 1),
                ),
            ])
            ->add('loggerLevel', LoggerLevelChoiceType::class, [
                'log_type' => LoggerLevelChoiceType::TYPE_DEBUG,
            ])
            ->add('components', CheckboxType::class, [
                'label' => 'sylius_mollie.ui.enable_components',
                'attr' => ['class' => 'mollie-components'],
                'help_html' => true,
            ])
            ->add('single_click_enabled', CheckboxType::class, [
                'label' => 'sylius_mollie.ui.single_click_enabled',
                'attr' => ['class' => 'mollie-single-click-payment'],
                'help_html' => true,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $data = $event->getData();

                $data['payum.http_client'] = '@sylius_mollie.client.mollie_api';

                $event->setData($data);
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                $data = $event->getData();

                $apiKeyField = ((bool) $data['environment']) ? MollieGatewayConfigurationType::API_KEY_LIVE : MollieGatewayConfigurationType::API_KEY_TEST;

                $apiKey = $data[$apiKeyField] ?? '';
                if (!preg_match('/^(test|live)_\w{26,}$/', $apiKey)) {
                    return;
                }

                $this->apiClient->setApiKey($apiKey);
                $profile = $this->apiClient->profiles->getCurrent();

                $data['profile_id'] = $profile->id;
                $event->setData($data);
            });
    }

    public function getBlockPrefix(): string
    {
        return 'mollie_gateway_configuration_type';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaults = [
            'field' => self::API_KEY_LIVE,
            'groups' => ['sylius'],
        ];

        $resolver->setDefault('constraints', [
            new LiveApiKeyIsNotBlank($defaults),
        ]);
    }
}
