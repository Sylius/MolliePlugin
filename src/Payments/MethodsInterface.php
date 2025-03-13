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

namespace SyliusMolliePlugin\Payments;

use SyliusMolliePlugin\Payments\Methods\Alma;
use SyliusMolliePlugin\Payments\Methods\ApplePay;
use SyliusMolliePlugin\Payments\Methods\Bancomatpay;
use SyliusMolliePlugin\Payments\Methods\Bancontact;
use SyliusMolliePlugin\Payments\Methods\BankTransfer;
use SyliusMolliePlugin\Payments\Methods\Belfius;
use SyliusMolliePlugin\Payments\Methods\Billie;
use SyliusMolliePlugin\Payments\Methods\Blik;
use SyliusMolliePlugin\Payments\Methods\CreditCard;
use SyliusMolliePlugin\Payments\Methods\DirectDebit;
use SyliusMolliePlugin\Payments\Methods\Eps;
use SyliusMolliePlugin\Payments\Methods\GiftCard;
use SyliusMolliePlugin\Payments\Methods\Ideal;
use SyliusMolliePlugin\Payments\Methods\In3;
use SyliusMolliePlugin\Payments\Methods\Kbc;
use SyliusMolliePlugin\Payments\Methods\KlarnaOne;
use SyliusMolliePlugin\Payments\Methods\Klarnapaylater;
use SyliusMolliePlugin\Payments\Methods\KlarnaPayNow;
use SyliusMolliePlugin\Payments\Methods\Klarnasliceit;
use SyliusMolliePlugin\Payments\Methods\MealVoucher;
use SyliusMolliePlugin\Payments\Methods\MyBank;
use SyliusMolliePlugin\Payments\Methods\Payconiq;
use SyliusMolliePlugin\Payments\Methods\PayPal;
use SyliusMolliePlugin\Payments\Methods\Przelewy24;
use SyliusMolliePlugin\Payments\Methods\Riverty;
use SyliusMolliePlugin\Payments\Methods\Satispay;
use SyliusMolliePlugin\Payments\Methods\SofortBanking;
use Mollie\Api\Resources\Method;
use SyliusMolliePlugin\Payments\Methods\Trustly;
use SyliusMolliePlugin\Payments\Methods\Twint;

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
        Satispay::class
    ];

    public function getAllEnabled(): array;

    public function add(Method $mollieMethod): void;
}
