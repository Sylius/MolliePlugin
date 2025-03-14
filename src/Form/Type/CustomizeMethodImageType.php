<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

final class CustomizeMethodImageType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class, [
            'label' => 'sylius_mollie_plugin.ui.customize_image',
            'required' => false,
        ]);
    }
}
