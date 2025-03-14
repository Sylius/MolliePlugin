<?php

namespace Sylius\MolliePlugin\Form\Type;

use Sylius\MolliePlugin\Entity\MollieMinMax;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;

class MollieMinMaxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('minimumAmount', NumberType::class, [
                'label' => 'sylius_mollie_plugin.ui.min_amount',
                'required' => false,
                'constraints' => [
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'sylius_mollie_plugin.form.error.greater_than',
                        'groups' => ['sylius'],
                    ])
                ],
            ])
            ->add('maximumAmount', NumberType::class, [
                'label' => 'sylius_mollie_plugin.ui.max_amount',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => MollieMinMax::class,
            ]);
    }
}
