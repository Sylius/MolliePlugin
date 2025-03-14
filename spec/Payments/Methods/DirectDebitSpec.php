<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Payments\Methods;

use Sylius\MolliePlugin\Payments\Methods\AbstractMethod;
use Sylius\MolliePlugin\Payments\Methods\DirectDebit;
use Mollie\Api\Types\PaymentMethod;
use PhpSpec\ObjectBehavior;

final class DirectDebitSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(DirectDebit::class);
    }

    function it_should_extends_abstract_method(): void
    {
        $this->shouldHaveType(AbstractMethod::class);
    }

    function it_gets_method_id(): void
    {
        $this->getMethodId()->shouldReturn(PaymentMethod::DIRECTDEBIT);
    }

    function it_gets_payment_type(): void
    {
        $this->getPaymentType()->shouldReturn(AbstractMethod::PAYMENT_API);
    }
}
