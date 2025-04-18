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

namespace Tests\Sylius\MolliePlugin\Unit\Grid\Filter;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Data\ExpressionBuilderInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;
use Sylius\MolliePlugin\Grid\Filter\MollieSubscriptionState;

final class MollieSubscriptionStateTest extends TestCase
{
    private FilterInterface $mollieSubscriptionState;

    protected function setUp(): void
    {
        $this->mollieSubscriptionState = new MollieSubscriptionState();
    }

    public function testImplementFilterInterface(): void
    {
        $this->assertInstanceOf(FilterInterface::class, $this->mollieSubscriptionState);
    }

    public function testAppliesWhenArrayKeyExists(): void
    {
        $sourceMock = $this->createMock(DataSourceInterface::class);
        $builderMock = $this->createMock(ExpressionBuilderInterface::class);

        $data = ['state' => ['not empty array']];

        $sourceMock->expects($this->once())
            ->method('getExpressionBuilder')
            ->willReturn($builderMock);

        $builderMock->expects($this->once())
            ->method('in')
            ->with('state', $data['state'])
            ->willReturn('expression');

        $sourceMock->expects($this->once())
            ->method('restrict')
            ->with('expression');

        $this->mollieSubscriptionState->apply($sourceMock, 'name', $data, []);
    }

    public function testReturnsWhenArrayKeyDoesNotExists(): void
    {
        $sourceMock = $this->createMock(DataSourceInterface::class);

        $data = ['not state' => []];

        $sourceMock->expects($this->never())
            ->method('restrict');

        $this->mollieSubscriptionState->apply($sourceMock, 'name', $data, []);
    }

    public function testReturnsWhenArrayKeyHasEmptyValue(): void
    {
        $sourceMock = $this->createMock(DataSourceInterface::class);

        $data = ['state' => []];

        $sourceMock->expects($this->never())
            ->method('restrict');

        $this->mollieSubscriptionState->apply($sourceMock, 'name', $data, []);
    }
}
