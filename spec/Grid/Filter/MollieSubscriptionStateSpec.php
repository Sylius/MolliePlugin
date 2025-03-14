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

namespace spec\SyliusMolliePlugin\Grid\Filter;

use SyliusMolliePlugin\Grid\Filter\MollieSubscriptionState;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Data\ExpressionBuilderInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

final class MollieSubscriptionStateSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieSubscriptionState::class);
    }

    function it_should_implement_filter_interface(): void
    {
        $this->shouldImplement(FilterInterface::class);
    }

    function it_applies_when_array_key_exists(
        DataSourceInterface $source,
        ExpressionBuilderInterface $builder
    ): void {
        $data = ['state' => ['not empty array']];
        $source->getExpressionBuilder()->willReturn($builder);

        $this->apply($source,'name', $data, []);

        $source->restrict($builder->getWrappedObject()->in('state', $data['state']))->shouldBeCalledOnce();
    }

    function it_returns_when_array_key_does_not_exists(
        DataSourceInterface $source,
        ExpressionBuilderInterface $builder
    ): void {
        $data = ['not state' => []];
        $source->getExpressionBuilder()->willReturn($builder);

        $this->apply($source,'name', $data, []);

        $source->restrict($builder->getWrappedObject()->in('not state', $data['not state']))->shouldNotBeCalled();
    }

    function it_returns_when_array_key_has_empty_value(
        DataSourceInterface $source,
        ExpressionBuilderInterface $builder
    ): void {
        $data = ['state' => []];
        $source->getExpressionBuilder()->willReturn($builder);

        $this->apply($source,'name', $data, []);

        $source->restrict($builder->getWrappedObject()->in('state', $data['state']))->shouldNotBeCalled();
    }
}
