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

namespace Sylius\MolliePlugin\Form\Extension;

use Sylius\Bundle\ProductBundle\Form\Type\ProductType as ProductFormType;
use Sylius\MolliePlugin\Entity\ProductType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productType', EntityType::class, [
                'class' => ProductType::class,
                'label' => 'sylius_mollie.form.product_type',
                'placeholder' => 'sylius_mollie.form.product_type_none',
                'empty_data' => null,
            ]);
    }

    public static function getExtendedTypes(): array
    {
        return [ProductFormType::class];
    }
}
