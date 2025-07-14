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

use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PaymentLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('methods', EntityType::class, [
                'class' => MollieGatewayConfig::class,
                'multiple' => false,
                'label' => 'sylius_mollie.form.methods',
                'required' => false,
                'autocomplete' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'sylius_mollie.payment_link.method.not_blank',
                    ]),
                ],
            ])
        ;
    }
}
