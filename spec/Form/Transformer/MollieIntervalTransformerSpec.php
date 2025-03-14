<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Form\Transformer;

use Sylius\MolliePlugin\Form\Transformer\MollieIntervalTransformer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\DataTransformerInterface;

final class MollieIntervalTransformerSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieIntervalTransformer::class);
    }

    function it_should_implement_data_transformer_interface(): void
    {
        $this->shouldImplement(DataTransformerInterface::class);
    }

    function it_transforms_days(): void
    {
        $value = '1 days';
        $this->transform($value)->shouldReturn([
            0 => "1 days",
            'amount' => "1",
            1 => "1",
            'step' => "days",
            2 => "days",
        ]);
    }

    function it_transforms_weeks(): void
    {
        $value = '3 weeks';
        $this->transform($value)->shouldReturn([
            0 => "3 weeks",
            'amount' => "3",
            1 => "3",
            'step' => "weeks",
            2 => "weeks",
        ]);
    }

    function it_transforms_months(): void
    {
        $value = '12 months';
        $this->transform($value)->shouldReturn([
            0 => "12 months",
            'amount' => "12",
            1 => "12",
            'step' => "months",
            2 => "months",
        ]);
    }

    function it_returns_empty_array_when_unsupported_format_was_provided(): void
    {
        $value = 'one months';
        $this->transform($value)->shouldReturn([]);
    }

    function it_return_array_with_nulls_when_bad_type(): void
    {
        $value = 12;
        $this->transform($value)->shouldReturn([
            'amount' => null,
            'step' => null,
        ]);
    }

    function it_reverses_transform(): void
    {
        $value = [
            'amount' => '3',
            'step' => 'days',
        ];

        $this->reverseTransform($value)->shouldReturn('3 days');
    }

    function it_returns_null_when_no_amount_or_step_key_in_array(): void
    {
        $value = [
            'definitelyNotAmount' => '3',
            'maybeStep' => 'days',
        ];

        $this->reverseTransform($value)->shouldReturn(null);
    }
}
