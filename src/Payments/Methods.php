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

use SyliusMolliePlugin\Payments\Methods\MethodInterface;
use Mollie\Api\Resources\Method;

final class Methods implements MethodsInterface
{
    /** @var array */
    private $methods;

    public function add(Method $mollieMethod): void
    {
        foreach (self::GATEWAYS as $gateway) {
            $payment = new $gateway();

            if ($mollieMethod->id === $payment->getMethodId()) {
                $payment->setName($mollieMethod->description);
                $payment->setMinimumAmount((array) $mollieMethod->minimumAmount);
                $payment->setMaximumAmount((array) $mollieMethod->maximumAmount);
                $payment->setImage((array) $mollieMethod->image);

                /** @var array|null $issuers */
                $issuers = $mollieMethod->issuers;
                $payment->setIssuers((array) $issuers);

                $this->methods[] = $payment;
            }
        }
    }

    public function getAllEnabled(): array
    {
        $methods = [];
        /** @var MethodInterface $method */
        foreach ($this->methods as $method) {
            if (true === $method->isEnabled()) {
                $methods[] = $method->isEnabled();
            } else {
                $methods[] = $method;
            }
        }

        return $methods;
    }
}
