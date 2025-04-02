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

use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Shipping\Model\ShipmentUnitInterface;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

final class ShippingUnitsChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        Assert::keyExists($options, 'multiple');
        if (true === $options['multiple']) {
            $builder->addViewTransformer(new CollectionToArrayTransformer(), true);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choice_label' => static function (ShipmentUnitInterface $orderItemUnit): string {
                    /** @var ProductVariantInterface $shippable */
                    $shippable = $orderItemUnit->getShippable();

                    $name = $shippable->getName();
                    Assert::notNull($name);
                    $product = $shippable->getProduct();
                    Assert::notNull($product);
                    $productName = $product->getName();
                    Assert::notNull($productName);

                    if ('' !== $name) {
                        return sprintf('%s (%s)', $productName, $name);
                    }

                    return $productName;
                },
                'multiple' => false,
                'expanded' => true,
            ])
        ;
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'mollie_shipping_units_choice';
    }
}
