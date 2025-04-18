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

namespace Tests\Sylius\MolliePlugin\Unit\Form\Transformer;

use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Form\Transformer\MollieIntervalTransformer;
use Symfony\Component\Form\DataTransformerInterface;

final class MollieIntervalTransformerTest extends TestCase
{
    private MollieIntervalTransformer $mollieIntervalTransformer;

    protected function setUp(): void
    {
        $this->mollieIntervalTransformer = new MollieIntervalTransformer();
    }

    public function testImplementDataTransformerInterface(): void
    {
        $this->assertInstanceOf(DataTransformerInterface::class, $this->mollieIntervalTransformer);
    }

    public function testTransformsDays(): void
    {
        $value = '1 days';
        $this->assertSame(
            [
                0 => '1 days',
                'amount' => '1',
                1 => '1',
                'step' => 'days',
                2 => 'days',
            ],
            $this->mollieIntervalTransformer->transform($value),
        );
    }

    public function testTransformsWeeks(): void
    {
        $value = '3 weeks';
        $this->assertSame(
            [
                0 => '3 weeks',
                'amount' => '3',
                1 => '3',
                'step' => 'weeks',
                2 => 'weeks',
            ],
            $this->mollieIntervalTransformer->transform($value),
        );
    }

    public function testTransformsMonths(): void
    {
        $value = '12 months';
        $this->assertSame(
            [
                0 => '12 months',
                'amount' => '12',
                1 => '12',
                'step' => 'months',
                2 => 'months',
            ],
            $this->mollieIntervalTransformer->transform($value),
        );
    }

    public function testReturnsEmptyArrayWhenUnsupportedFormatWasProvided(): void
    {
        $value = 'one months';
        $this->assertSame([], $this->mollieIntervalTransformer->transform($value));
    }

    public function testReturnArrayWithNullsWhenBadType(): void
    {
        $value = 12;
        $this->assertSame(
            [
                'amount' => null,
                'step' => null,
            ],
            $this->mollieIntervalTransformer->transform($value),
        );
    }

    public function testReversesTransform(): void
    {
        $value = [
            'amount' => '3',
            'step' => 'days',
        ];

        $this->assertSame('3 days', $this->mollieIntervalTransformer->reverseTransform($value));
    }

    public function testReturnsNullWhenNoAmountOrStepKeyInArray(): void
    {
        $value = [
            'definitelyNotAmount' => '3',
            'maybeStep' => 'days',
        ];

        $this->assertNull($this->mollieIntervalTransformer->reverseTransform($value));
    }
}
