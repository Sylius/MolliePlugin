<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Form\Extension;

use Sylius\MolliePlugin\Form\Type\MollieGatewayConfigType;
use Sylius\Bundle\PayumBundle\Form\Type\GatewayConfigType;
use Sylius\MolliePlugin\Validator\Constraints\MollieGatewayConfigValidatorType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

final class GatewayConfigTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'mollieGatewayConfig',
            CollectionType::class,
            [
                'entry_type' => MollieGatewayConfigType::class,
                'validation_groups' => ['sylius'],
                'constraints' => [
                    new Valid(),
                    new MollieGatewayConfigValidatorType(['groups' => 'sylius'])
                ],
            ]
        );
    }

    public static function getExtendedTypes(): array
    {
        return [GatewayConfigType::class];
    }
}
