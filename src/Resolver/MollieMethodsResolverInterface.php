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

namespace SyliusMolliePlugin\Resolver;

use SyliusMolliePlugin\Entity\GatewayConfigInterface;
use Mollie\Api\Types\PaymentMethod;

interface MollieMethodsResolverInterface
{
    /** @var string[] */
    public const PARAMETERS = [
        'include' => 'issuers',
        'includeWallets' => 'applepay',
        'resource' => 'orders',
    ];

    public const PARAMETERS_RECURRING = [
        'include' => 'issuers',
        'sequenceType' => 'recurring',
    ];

    /** @var string[] */
    public const PARAMETERS_AVAILABLE = [
        'include' => 'issuers',
    ];

    /** @var string[] */
    public const UNSUPPORTED_METHODS = [
        PaymentMethod::INGHOMEPAY,
    ];

    /** @var string[] */
    public const RECURRING_PAYMENT_SUPPORTED_METHODS = [
        PaymentMethod::DIRECTDEBIT,
        PaymentMethod::CREDITCARD,
    ];

    public const RECURRING_PAYMENT_INITIAL_METHODS = [
        PaymentMethod::BANCONTACT,
        PaymentMethod::BELFIUS,
        PaymentMethod::EPS,
        PaymentMethod::GIROPAY,
        PaymentMethod::IDEAL,
        PaymentMethod::KBC,
        PaymentMethod::MYBANK,
        PaymentMethod::SOFORT,
    ];

    public function create(): void;

    public function createForGateway(GatewayConfigInterface $gateway): void;
}
