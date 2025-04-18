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

namespace Tests\Sylius\MolliePlugin\Unit\Payments\Methods;

use Mollie\Api\Types\PaymentMethod;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Model\ApiType;
use Sylius\MolliePlugin\Model\PaymentMethod\DirectDebit;

final class DirectDebitTest extends TestCase
{
    private DirectDebit $directDebit;

    protected function setUp(): void
    {
        $this->directDebit = new DirectDebit();
    }

    public function testGetsMethodId(): void
    {
        $this->assertSame(PaymentMethod::DIRECTDEBIT, $this->directDebit->getMethodId());
    }

    public function testGetsPaymentType(): void
    {
        $this->assertSame(ApiType::PAYMENT_API_VALUE, $this->directDebit->getPaymentType());
    }
}
