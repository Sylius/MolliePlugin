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

namespace Sylius\MolliePlugin\Checker\ApplePay;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Mollie\Api\Types\PaymentMethod;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class ApplePayEnabledChecker implements ApplePayEnabledCheckerInterface
{
    /** @var RepositoryInterface */
    private $mollieGatewayConfigurationRepository;

    public function __construct(RepositoryInterface $mollieGatewayConfigurationRepository)
    {
        $this->mollieGatewayConfigurationRepository = $mollieGatewayConfigurationRepository;
    }

    public function isEnabled(): bool
    {
        $applePayConfig = $this->mollieGatewayConfigurationRepository->findOneBy([
            'methodId' => PaymentMethod::APPLEPAY,
        ]);

        if ($applePayConfig instanceof MollieGatewayConfigInterface) {
            Assert::notNull($applePayConfig->isApplePayDirectButton());

            return $applePayConfig->isApplePayDirectButton() && $applePayConfig->isEnabled();
        }

        return false;
    }
}
