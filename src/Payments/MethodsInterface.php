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

namespace Sylius\MolliePlugin\Payments;

use Mollie\Api\Resources\Method;
use Sylius\MolliePlugin\Payments\Methods\Alma;
use Sylius\MolliePlugin\Payments\Methods\ApplePay;
use Sylius\MolliePlugin\Payments\Methods\Bancomatpay;
use Sylius\MolliePlugin\Payments\Methods\Bancontact;
use Sylius\MolliePlugin\Payments\Methods\BankTransfer;
use Sylius\MolliePlugin\Payments\Methods\Belfius;
use Sylius\MolliePlugin\Payments\Methods\Billie;
use Sylius\MolliePlugin\Payments\Methods\Blik;
use Sylius\MolliePlugin\Payments\Methods\CreditCard;
use Sylius\MolliePlugin\Payments\Methods\DirectDebit;
use Sylius\MolliePlugin\Payments\Methods\Eps;
use Sylius\MolliePlugin\Payments\Methods\GiftCard;
use Sylius\MolliePlugin\Payments\Methods\Ideal;
use Sylius\MolliePlugin\Payments\Methods\In3;
use Sylius\MolliePlugin\Payments\Methods\Kbc;
use Sylius\MolliePlugin\Payments\Methods\KlarnaOne;
use Sylius\MolliePlugin\Payments\Methods\Klarnapaylater;
use Sylius\MolliePlugin\Payments\Methods\KlarnaPayNow;
use Sylius\MolliePlugin\Payments\Methods\Klarnasliceit;
use Sylius\MolliePlugin\Payments\Methods\MealVoucher;
use Sylius\MolliePlugin\Payments\Methods\MyBank;
use Sylius\MolliePlugin\Payments\Methods\Payconiq;
use Sylius\MolliePlugin\Payments\Methods\PayPal;
use Sylius\MolliePlugin\Payments\Methods\Przelewy24;
use Sylius\MolliePlugin\Payments\Methods\Riverty;
use Sylius\MolliePlugin\Payments\Methods\Satispay;
use Sylius\MolliePlugin\Payments\Methods\SofortBanking;
use Sylius\MolliePlugin\Payments\Methods\Trustly;
use Sylius\MolliePlugin\Payments\Methods\Twint;

interface MethodsInterface
{
    public const GATEWAYS = [
        Alma::class,
        ApplePay::class,
        Bancontact::class,
        BankTransfer::class,
        Belfius::class,
        CreditCard::class,
        Eps::class,
        GiftCard::class,
        Ideal::class,
        Kbc::class,
        KlarnaOne::class,
        Klarnapaylater::class,
        Klarnasliceit::class,
        KlarnaPayNow::class,
        MyBank::class,
        PayPal::class,
        Przelewy24::class,
        SofortBanking::class,
        MealVoucher::class,
        DirectDebit::class,
        In3::class,
        Billie::class,
        Twint::class,
        Blik::class,
        Riverty::class,
        Trustly::class,
        Bancomatpay::class,
        Payconiq::class,
        Satispay::class,
    ];

    public function getAllEnabled(): array;

    public function add(Method $mollieMethod): void;
}
