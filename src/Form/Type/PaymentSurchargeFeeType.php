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

use Sylius\MolliePlugin\Entity\PaymentSurchargeFee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;

final class PaymentSurchargeFeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', PaymentSurchargeFeeTypeChoiceType::class)
            ->add('fixedAmount', NumberType::class, [
                'label' => 'sylius_mollie.ui.fix_amount_surcharge',
                'attr' => ['class' => 'mollie-payment_fee-fixedAmount'],
                'constraints' => [
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'sylius_mollie.form.error.greater_than',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('percentage', NumberType::class, [
                'label' => 'sylius_mollie.ui.percentage_surcharge',
                'constraints' => [
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'sylius_mollie.form.error.greater_than',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('surchargeLimit', NumberType::class, [
                'label' => 'sylius_mollie.ui.surcharge_limit',
                'constraints' => [
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'sylius_mollie.form.error.greater_than',
                        'groups' => ['sylius'],
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => PaymentSurchargeFee::class,
            ]);
    }
}
