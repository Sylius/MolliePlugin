<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Form\Type\Translation;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class TemplateMollieEmailTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'sylius_mollie_plugin.ui.template_name',
            ])
            ->add('subject', TextType::class, [
                'label' => 'sylius_mollie_plugin.ui.template_subject',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'sylius_mollie_plugin.ui.template_content',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_mollie_plugin_template_mollie_email_translation';
    }
}
