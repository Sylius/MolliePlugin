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

namespace Sylius\MolliePlugin\Payum\Resolver;

use Mollie\Api\Resources\Order as MollieOrder;
use Mollie\Api\Resources\Payment as MolliePayment;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Security\TokenInterface;

final class ExistingMollieSessionResolver implements ExistingMollieSessionResolverInterface
{
    /** `created` is the Order API's equivalent of a payment sitting `open`. */
    private const OPEN_STATUSES = [
        PaymentStatus::STATUS_OPEN,
        OrderStatus::STATUS_CREATED,
    ];

    public function resolve(
        MollieOrder|MolliePayment $resource,
        ArrayObject $details,
        ?TokenInterface $token,
    ): ExistingMollieSessionDecision {
        if (!in_array($resource->status, self::OPEN_STATUSES, true)) {
            return ExistingMollieSessionDecision::LeaveToStatusFlow;
        }

        // Mollie redirects back to the token that created the session after every attempt,
        // abandoned ones included, so this is a return and not a new intent (#329).
        if (null !== $token && $token->getTargetUrl() === $resource->redirectUrl) {
            return ExistingMollieSessionDecision::LeaveToStatusFlow;
        }

        $requestedMethod = $details['molliePaymentMethods']
            ?? $details['metadata']['molliePaymentMethods']
            ?? null;

        if (!$this->isMethodChangeRequested($requestedMethod, $resource->method)) {
            return ExistingMollieSessionDecision::Resume;
        }

        return ExistingMollieSessionDecision::Replace;
    }

    /** Only a form submission writes the method into details, so a mismatch means the customer picked another one. */
    private function isMethodChangeRequested(?string $requestedMethod, ?string $currentMethod): bool
    {
        if (null === $requestedMethod || null === $currentMethod) {
            return false;
        }

        return $requestedMethod !== $currentMethod;
    }
}
