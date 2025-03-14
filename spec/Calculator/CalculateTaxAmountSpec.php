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

namespace spec\Sylius\MolliePlugin\Calculator;

use Sylius\MolliePlugin\Calculator\CalculateTaxAmount;
use Sylius\MolliePlugin\Calculator\CalculateTaxAmountInterface;
use Sylius\MolliePlugin\Helper\IntToStringConverterInterface;
use PhpSpec\ObjectBehavior;

final class CalculateTaxAmountSpec extends ObjectBehavior
{
    function let(IntToStringConverterInterface $converter): void
    {
        $this->beConstructedWith($converter);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(CalculateTaxAmount::class);
    }

    function it_should_implement_interface(): void
    {
        $this->shouldImplement(CalculateTaxAmountInterface::class);
    }

    function it_calculates(
        IntToStringConverterInterface $converter
    ): void {
        $converter->convertIntToString((int) 2,100)->willReturn('0.02');

        $this->calculate(15.5,2)->shouldReturn('0.02');
    }
}
