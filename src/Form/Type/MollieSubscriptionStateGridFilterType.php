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

use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MollieSubscriptionStateGridFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'state',
            ChoiceType::class,
            [
                'label' => false,
                'choices' => [
                    MollieSubscriptionInterface::STATE_NEW,
                    MollieSubscriptionInterface::STATE_ACTIVE,
                    MollieSubscriptionInterface::STATE_PROCESSING,
                    MollieSubscriptionInterface::STATE_PAUSED,
                    MollieSubscriptionInterface::STATE_CANCELED,
                    MollieSubscriptionInterface::STATE_COMPLETED,
                    MollieSubscriptionInterface::STATE_ABORTED,
                ],
                'choice_label' => fn (string $value): string => sprintf('sylius_mollie.ui.subscription.state.%s', $value),
                'expanded' => true,
                'multiple' => true,
            ],
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'state' => null,
            ])
            ->setAllowedTypes('state', ['array', 'null'])
        ;
    }
}
