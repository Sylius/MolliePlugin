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

namespace Sylius\MolliePlugin\Registry;

use Mollie\Api\Resources\Method;
use Mollie\Api\Types\PaymentMethod;
use Sylius\MolliePlugin\Model\PaymentMethod\Alma;
use Sylius\MolliePlugin\Model\PaymentMethod\ApplePay;
use Sylius\MolliePlugin\Model\PaymentMethod\Bancomatpay;
use Sylius\MolliePlugin\Model\PaymentMethod\Bancontact;
use Sylius\MolliePlugin\Model\PaymentMethod\BankTransfer;
use Sylius\MolliePlugin\Model\PaymentMethod\Belfius;
use Sylius\MolliePlugin\Model\PaymentMethod\Billie;
use Sylius\MolliePlugin\Model\PaymentMethod\Blik;
use Sylius\MolliePlugin\Model\PaymentMethod\CreditCard;
use Sylius\MolliePlugin\Model\PaymentMethod\DirectDebit;
use Sylius\MolliePlugin\Model\PaymentMethod\Eps;
use Sylius\MolliePlugin\Model\PaymentMethod\GiftCard;
use Sylius\MolliePlugin\Model\PaymentMethod\Ideal;
use Sylius\MolliePlugin\Model\PaymentMethod\In3;
use Sylius\MolliePlugin\Model\PaymentMethod\Kbc;
use Sylius\MolliePlugin\Model\PaymentMethod\KlarnaOne;
use Sylius\MolliePlugin\Model\PaymentMethod\Klarnapaylater;
use Sylius\MolliePlugin\Model\PaymentMethod\KlarnaPayNow;
use Sylius\MolliePlugin\Model\PaymentMethod\Klarnasliceit;
use Sylius\MolliePlugin\Model\PaymentMethod\MealVoucher;
use Sylius\MolliePlugin\Model\PaymentMethod\MethodInterface;
use Sylius\MolliePlugin\Model\PaymentMethod\MyBank;
use Sylius\MolliePlugin\Model\PaymentMethod\Payconiq;
use Sylius\MolliePlugin\Model\PaymentMethod\PayPal;
use Sylius\MolliePlugin\Model\PaymentMethod\Przelewy24;
use Sylius\MolliePlugin\Model\PaymentMethod\Riverty;
use Sylius\MolliePlugin\Model\PaymentMethod\Satispay;
use Sylius\MolliePlugin\Model\PaymentMethod\SofortBanking;
use Sylius\MolliePlugin\Model\PaymentMethod\Trustly;
use Sylius\MolliePlugin\Model\PaymentMethod\Twint;

interface PaymentMethodRegistryInterface
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
