<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Grid\Filter;

use Sylius\MolliePlugin\Grid\Filter\MollieSubscriptionState;
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
