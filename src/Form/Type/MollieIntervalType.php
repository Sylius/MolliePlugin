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

use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

final class MollieIntervalType extends AbstractType
{
    public function __construct(private readonly DataTransformerInterface $transformer)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('amount', NumberType::class, [
            'error_bubbling' => false,
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'groups' => ['recurring_product_variant'],
                ]),
                new GreaterThan([
                    'value' => 0,
                    'groups' => ['recurring_product_variant'],
                ]),
            ],
        ]);
        $builder->add('step', ChoiceType::class, [
            'choices' => array_combine(
                MollieSubscriptionConfigurationInterface::SUPPORTED_INTERVAL_STEPS,
                MollieSubscriptionConfigurationInterface::SUPPORTED_INTERVAL_STEPS,
            ),
            'label' => false,
            'error_bubbling' => false,
            'choice_label' => fn (string $value): string => sprintf(
                'sylius_mollie.form.product_variant.interval_configuration.steps.%s',
                $value,
            ),
        ]);
        $builder->addViewTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compound' => true,
            'label_format' => 'sylius_mollie.form.product_variant.interval_configuration.%name%',
            'error_bubbling' => true,
        ]);
    }
}
