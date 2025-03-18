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

namespace spec\SyliusMolliePlugin\Payments\Methods;

use SyliusMolliePlugin\Payments\Methods\AbstractMethod;
use SyliusMolliePlugin\Payments\Methods\KlarnaPayNow;
use Mollie\Api\Types\PaymentMethod;
use PhpSpec\ObjectBehavior;

final class KlarnaPayNowSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(KlarnaPayNow::class);
    }

    function it_should_extends_abstract_method(): void
    {
        $this->shouldHaveType(AbstractMethod::class);
    }

    function it_gets_method_id(): void
    {
        $this->getMethodId()->shouldReturn(PaymentMethod::KLARNA_PAY_NOW);
    }

    function it_gets_payment_type(): void
    {
        $this->getPaymentType()->shouldReturn(AbstractMethod::ORDER_API);
    }
}
