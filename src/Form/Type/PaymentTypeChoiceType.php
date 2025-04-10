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

use Sylius\MolliePlugin\Model\ApiType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentTypeChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'sylius_mollie.ui.payment_type',
            'choices' => ApiType::getAllAvailable(),
            'help' => 'sylius_mollie.ui.payment_methods_doc',
            'help_html' => true,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
