<?php

namespace SyliusMolliePlugin\Payments\Methods;


class Trustly extends AbstractMethod
{
    public function getMethodId(): string
    {
        return 'trustly';
    }

    public function getPaymentType(): string
    {
        return self::PAYMENT_API;
    }
}
