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

namespace Tests\Sylius\MolliePlugin\Unit\Provider\Form;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\MolliePlugin\Entity\ProductVariantInterface;
use Sylius\MolliePlugin\Form\Resolver\ProductVariantValidationGroupsResolver;
use Sylius\MolliePlugin\Form\Resolver\ValidationGroupsResolverInterface;
use Symfony\Component\Form\FormInterface;

final class ResolverGroupProviderTest extends TestCase
{
    private ProductVariantValidationGroupsResolver $productVariantValidationGroupsResolver;

    protected function setUp(): void
    {
        $this->productVariantValidationGroupsResolver = new ProductVariantValidationGroupsResolver();
    }

    public function testItsAValidationGroupsResolver(): void
    {
        $this->assertInstanceOf(
            ValidationGroupsResolverInterface::class,
            $this->productVariantValidationGroupsResolver,
        );
    }

    public function testProvidesWhenProductVariantIsRecurring(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $dataMock = $this->createMock(ProductVariantInterface::class);

        $formMock->expects($this->once())->method('getData')->willReturn($dataMock);
        $dataMock->expects($this->once())->method('isRecurring')->willReturn(true);

        $this->assertSame([
            'sylius',
            'recurring_product_variant',
        ], $this->productVariantValidationGroupsResolver->resolve($formMock));
    }

    public function testProvidesWhenProductVariantIsNotRecurring(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $dataMock = $this->createMock(ProductVariantInterface::class);

        $formMock->expects($this->once())->method('getData')->willReturn($dataMock);
        $dataMock->expects($this->once())->method('isRecurring')->willReturn(false);

        $this->assertSame([
            'sylius',
            'non_recurring_product_variant',
        ], $this->productVariantValidationGroupsResolver->resolve($formMock));
    }

    public function testProvidesWhenWrongTypeProvided(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $dataMock = $this->createMock(CustomerInterface::class);

        $formMock->expects($this->once())->method('getData')->willReturn($dataMock);

        $this->assertSame(['sylius'], $this->productVariantValidationGroupsResolver->resolve($formMock));
    }

    public function testProvidesWhenNullDataProvided(): void
    {
        $formMock = $this->createMock(FormInterface::class);
        $formMock->expects($this->once())->method('getData')->willReturn(null);

        $this->assertSame(['sylius'], $this->productVariantValidationGroupsResolver->resolve($formMock));
    }
}
