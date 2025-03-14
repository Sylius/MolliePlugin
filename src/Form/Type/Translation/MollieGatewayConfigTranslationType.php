<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Form\Type\Translation;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class MollieGatewayConfigTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'sylius_mollie_plugin.ui.payment_name',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'mollie_payment_method_translation';
    }
}
