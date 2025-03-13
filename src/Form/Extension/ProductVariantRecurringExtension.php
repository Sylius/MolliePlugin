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

namespace SyliusMolliePlugin\Form\Extension;

use SyliusMolliePlugin\Form\Type\MollieIntervalType;
use SyliusMolliePlugin\Provider\Form\ResolverGroupProviderInterface;
use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType as ProductVariantFormType;
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
    private ResolverGroupProviderInterface $groupProvider;

    public function __construct(ResolverGroupProviderInterface $groupProvider)
    {
        $this->groupProvider = $groupProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recurring', CheckboxType::class, [
                'label' => 'sylius_mollie_plugin.form.product_variant.recurring',
                'help' => 'sylius_mollie_plugin.form.product_variant.recurring_help',
                'required' => false,
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('times', NumberType::class, [
                'label' => 'sylius_mollie_plugin.form.product_variant.times',
                'help' => 'sylius_mollie_plugin.form.product_variant.times_help',
                'required' => false,
                'constraints' => [
                    new Range([
                        'min' => 2,
                        'minMessage' => 'sylius_mollie_plugin.times.min_range',
                        'groups' => ['recurring_product_variant'],
                    ]),
                    new IsNull([
                        'groups' => 'non_recurring_product_variant',
                    ]),
                ],
            ])
            ->add('interval', MollieIntervalType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'class' => 'inline fields',
                ],
                'constraints' => [
                    new Valid([
                        'groups' => ['recurring_product_variant'],
                    ]),
                    new NotBlank([
                        'message' => 'sylius_mollie_plugin.interval.not_blank',
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
        $resolver->setDefault('validation_groups', function (FormInterface $form): array {
            return $this->groupProvider->provide($form);
        });
    }
}
