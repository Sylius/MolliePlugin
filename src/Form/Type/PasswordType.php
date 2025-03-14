<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PasswordType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'always_empty' => true,
            'trim' => false,
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'password';
    }
}
