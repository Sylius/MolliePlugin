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

namespace Tests\Sylius\MolliePlugin\PHPUnit\Unit\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Payments\Methods\AbstractMethod;
use Sylius\MolliePlugin\Payments\Methods\KlarnaPayNow;

final class KlarnaPayNowTest extends TestCase
{
    private KlarnaPayNow $klarnaPayNow;

    protected function setUp(): void
    {
        $this->klarnaPayNow = new KlarnaPayNow();
    }

    public function testGetsMethodId(): void
    {
        $this->assertSame(PaymentMethod::KLARNA_PAY_NOW, $this->klarnaPayNow->getMethodId());
    }

    public function testGetsPaymentType(): void
    {
        $this->assertSame(AbstractMethod::ORDER_API, $this->klarnaPayNow->getPaymentType());
    }
}
