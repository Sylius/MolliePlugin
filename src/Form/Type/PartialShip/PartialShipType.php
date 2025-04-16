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

namespace Sylius\MolliePlugin\Form\Type\PartialShip;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Core\Model\ShipmentInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

final class PartialShipType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tracking', TextType::class, [
                'required' => false,
                'label' => 'sylius_mollie.form.shipment.tracking_code',
                'attr' => ['placeholder' => 'sylius_mollie.form.shipment.tracking_code'],
            ])
            ->add('units', ShippingUnitsChoiceType::class, [
                'choices' => $options['shipment']->getUnits(),
                'label' => 'sylius_mollie.form.shipment.units',
                'multiple' => true,
                'constraints' => [
                    new Count(['min' => 1, 'groups' => ['sylius']]),
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, static function (FormEvent $formEvent): void {
            /** @var ShipmentInterface $shipment */
            $shipment = $formEvent->getData();

            foreach ($shipment->getUnits() as $unit) {
                /** @var ShipmentInterface $oldShipment */
                $oldShipment = $unit->getShipment();

                $oldShipment->removeUnit($unit);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('shipment')
            ->setDefaults(['validation_groups' => ['sylius']])
            ->setAllowedTypes('shipment', [ShipmentInterface::class])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'mollie_partial_shipment_ship';
    }
}
