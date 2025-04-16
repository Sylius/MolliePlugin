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

use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Sylius\MolliePlugin\Entity\TemplateMollieEmail;
use Sylius\MolliePlugin\Entity\TemplateMollieEmailInterface;
use Sylius\MolliePlugin\Form\Type\Translation\TemplateMollieEmailTranslationType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

final class TemplateMollieEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'sylius_mollie.ui.template_type',
                'choices' => [
                    'sylius_mollie.ui.payment_link' => TemplateMollieEmailInterface::PAYMENT_LINK,
                    'sylius_mollie.ui.payment_link_abandoned' => TemplateMollieEmailInterface::PAYMENT_LINK_ABANDONED,
                ],
            ])
            ->add('styleCss', TextareaType::class, [
                'label' => 'sylius_mollie.ui.style_css',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'label' => 'sylius_mollie.ui.template_contents',
                'entry_type' => TemplateMollieEmailTranslationType::class,
                'validation_groups' => ['sylius'],
                'constraints' => [
                    new Valid(),
                ],
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_mollie_plugin_template_mollie_email';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TemplateMollieEmail::class,
            'constraints' => [
                new UniqueEntity(['fields' => ['type']]),
            ],
        ]);
    }
}
