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

namespace spec\Sylius\MolliePlugin\Provider\Form;

use Sylius\MolliePlugin\Entity\ProductVariantInterface;
use Sylius\MolliePlugin\Provider\Form\ResolverGroupProvider;
use Sylius\MolliePlugin\Provider\Form\ResolverGroupProviderInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Component\Form\FormInterface;

final class ResolverGroupProviderSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(ResolverGroupProvider::class);
        $this->shouldImplement(ResolverGroupProviderInterface::class);
    }

    function it_provides_when_product_variant_is_recurring(
        FormInterface $form,
        ProductVariantInterface $data
    ): void {
        $form->getData()->willReturn($data);
        $data->isRecurring()->willReturn(true);

        $this->provide($form)->shouldReturn([
            'sylius',
            'recurring_product_variant'
        ]);
    }

    function it_provides_when_product_variant_is_not_recurring(
        FormInterface $form,
        ProductVariantInterface $data
    ): void {
        $form->getData()->willReturn($data);
        $data->isRecurring()->willReturn(false);

        $this->provide($form)->shouldReturn([
            'sylius',
            'non_recurring_product_variant'
        ]);
    }

    function it_provides_when_wrong_type_provided(
        FormInterface $form,
        CustomerInterface $data
    ): void {
        $form->getData()->willReturn($data);

        $this->provide($form)->shouldReturn([
            'sylius'
        ]);
    }

    function it_provides_when_null_data_provided(
        FormInterface $form
    ): void {
        $form->getData()->willReturn(null);

        $this->provide($form)->shouldReturn([
            'sylius'
        ]);
    }
}
