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

use Sylius\MolliePlugin\PaymentFee\PaymentSurchargeFeeType as FeeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentSurchargeFeeTypeChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'sylius_mollie_plugin.ui.payment_fee_type',
            'choices' => [
                'sylius_mollie_plugin.ui.no_fee' => FeeType::NONE,
                'sylius_mollie_plugin.ui.percentage_surcharge' => FeeType::PERCENTAGE,
                'sylius_mollie_plugin.ui.fix_amount_surcharge' => FeeType::FIXED,
                'sylius_mollie_plugin.ui.percentage_and_fix_amount_surcharge' => FeeType::FIXED_AND_PERCENTAGE,
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
