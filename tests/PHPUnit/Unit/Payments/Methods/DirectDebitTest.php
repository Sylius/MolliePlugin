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
use Sylius\MolliePlugin\Payments\Methods\DirectDebit;

final class DirectDebitTest extends TestCase
{
    private DirectDebit $directDebit;

    protected function setUp(): void
    {
        $this->directDebit = new DirectDebit();
    }
    function testGetsMethodId(): void
    {
        $this->assertSame(PaymentMethod::DIRECTDEBIT, $this->directDebit->getMethodId());
    }

    function testGetsPaymentType(): void
    {
        $this->assertSame(AbstractMethod::PAYMENT_API, $this->directDebit->getPaymentType());
    }
}
