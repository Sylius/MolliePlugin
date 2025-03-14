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

namespace Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigTranslationInterface;

final class MollieCountriesRestrictionResolver implements MollieCountriesRestrictionResolverInterface
{
    public function __construct(private readonly MolliePaymentMethodImageResolverInterface $imageResolver)
    {
    }

    public function resolve(
        MollieGatewayConfigInterface $paymentMethod,
        array $methods,
        string $countryCode,
    ): ?array {
        if (MollieGatewayConfigInterface::ALL_COUNTRIES === $paymentMethod->getCountryRestriction()) {
            return $this->excludeCountryLevel($paymentMethod, $methods, $countryCode);
        }
        if (MollieGatewayConfigInterface::SELECTED_COUNTRIES === $paymentMethod->getCountryRestriction()) {
            return $this->allowCountryLevel($paymentMethod, $methods, $countryCode);
        }

        return $methods;
    }

    private function allowCountryLevel(
        MollieGatewayConfigInterface $paymentMethod,
        array $methods,
        string $countryCode,
    ): array {
        if (is_array($paymentMethod->getCountryLevelAllowed()) &&
            in_array($countryCode, $paymentMethod->getCountryLevelAllowed(), true)) {
            return $this->setData($methods, $paymentMethod);
        }

        return $methods;
    }

    private function excludeCountryLevel(
        MollieGatewayConfigInterface $paymentMethod,
        array $methods,
        string $countryCode,
    ): array {
        if (is_array($paymentMethod->getCountryLevelExcluded()) &&
            in_array($countryCode, $paymentMethod->getCountryLevelExcluded(), true)) {
            return $methods;
        }

        return $this->setData($methods, $paymentMethod);
    }

    private function setData(array $methods, MollieGatewayConfigInterface $paymentMethod): array
    {
        /** @var MollieGatewayConfigTranslationInterface $translation */
        $translation = $paymentMethod->getTranslation();
        $methods['data'][$translation->getName() ?? $paymentMethod->getName()] = $paymentMethod->getMethodId();
        $methods['image'][$paymentMethod->getMethodId()] = $this->imageResolver->resolve($paymentMethod);
        $methods['issuers'][$paymentMethod->getMethodId()] = $paymentMethod->getIssuers();
        $methods['paymentFee'][$paymentMethod->getMethodId()] = $paymentMethod->getPaymentSurchargeFee() ?? [];

        return $methods;
    }
}
