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

namespace Sylius\MolliePlugin\Form\Extension;

use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType as ProductVariantFormType;
use Sylius\MolliePlugin\Form\Resolver\ValidationGroupsResolverInterface;
use Sylius\MolliePlugin\Form\Type\MollieIntervalType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Valid;

final class ProductVariantRecurringExtension extends AbstractTypeExtension
{
    public function __construct(private readonly ValidationGroupsResolverInterface $validationGroupsResolver)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recurring', CheckboxType::class, [
                'label' => 'sylius_mollie.form.product_variant.recurring',
                'required' => false,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('times', NumberType::class, [
                'label' => 'sylius_mollie.form.product_variant.times',
                'help' => 'sylius_mollie.form.product_variant.times_help',
                'required' => true,
                'constraints' => [
                    new Range([
                        'min' => 2,
                        'minMessage' => 'sylius_mollie.times.min_range',
                        'groups' => ['recurring_product_variant'],
                    ]),
                    new NotBlank([
                        'groups' => ['recurring_product_variant'],
                    ]),
                    new IsNull([
                        'groups' => ['non_recurring_product_variant'],
                    ]),
                ],
            ])
            ->add('interval', MollieIntervalType::class, [
                'label' => 'sylius_mollie.form.product_variant.interval_configuration.amount',
                'required' => true,
                'attr' => [
                    'class' => 'inline fields',
                ],
                'constraints' => [
                    new Valid([
                        'groups' => ['recurring_product_variant'],
                    ]),
                    new NotBlank([
                        'message' => 'sylius_mollie.interval.not_blank',
                        'groups' => ['recurring_product_variant'],
                    ]),
                ],
            ])
        ;
    }

    public static function getExtendedTypes(): array
    {
        return [ProductVariantFormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault(
            'validation_groups',
            fn (FormInterface $form): array => $this->validationGroupsResolver->resolve($form),
        );
    }
}
