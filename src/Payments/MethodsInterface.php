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
use Mollie\Api\Types\PaymentMethod;
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
use Sylius\MolliePlugin\Payments\Methods\MethodInterface;
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
    /** @var array<string, class-string> */
    public const GATEWAYS = [
        PaymentMethod::ALMA => Alma::class,
        PaymentMethod::APPLEPAY => ApplePay::class,
        PaymentMethod::BANCONTACT => Bancontact::class,
        PaymentMethod::BANKTRANSFER => BankTransfer::class,
        PaymentMethod::BELFIUS => Belfius::class,
        PaymentMethod::CREDITCARD => CreditCard::class,
        PaymentMethod::EPS => Eps::class,
        PaymentMethod::GIFTCARD => GiftCard::class,
        PaymentMethod::IDEAL => Ideal::class,
        PaymentMethod::KBC => Kbc::class,
        PaymentMethod::KLARNA_ONE => KlarnaOne::class,
        PaymentMethod::KLARNA_PAY_LATER => Klarnapaylater::class,
        PaymentMethod::KLARNA_SLICE_IT => Klarnasliceit::class,
        PaymentMethod::KLARNA_PAY_NOW => KlarnaPayNow::class,
        PaymentMethod::MYBANK => MyBank::class,
        PaymentMethod::PAYPAL => PayPal::class,
        PaymentMethod::PRZELEWY24 => Przelewy24::class,
        PaymentMethod::SOFORT => SofortBanking::class,
        MealVoucher::MEAL_VOUCHERS => MealVoucher::class,
        PaymentMethod::DIRECTDEBIT => DirectDebit::class,
        PaymentMethod::IN3 => In3::class,
        PaymentMethod::BILLIE => Billie::class,
        PaymentMethod::TWINT => Twint::class,
        PaymentMethod::BLIK => Blik::class,
        PaymentMethod::RIVERTY => Riverty::class,
        PaymentMethod::TRUSTLY => Trustly::class,
        PaymentMethod::BANCOMATPAY => Bancomatpay::class,
        PaymentMethod::PAYCONIQ => Payconiq::class,
        PaymentMethod::SATISPAY => Satispay::class,
    ];

    /** @return MethodInterface[] */
    public function getAllEnabled(): array;

    public function add(Method $mollieMethod): void;
}
