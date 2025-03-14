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

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Webmozart\Assert\Assert;

final class PaymentMethodConfigResolver implements PaymentMethodConfigResolverInterface
{
    public function __construct(private readonly RepositoryInterface $mollieMethodRepository)
    {
    }

    public function getConfigFromMethodId(string $methodId): MollieGatewayConfigInterface
    {
        /** @var ?MollieGatewayConfigInterface $paymentMethod */
        $paymentMethod = $this->mollieMethodRepository->findOneBy(['methodId' => $methodId]);

        Assert::notNull($paymentMethod, sprintf('Cannot find payment method config with method id %s', $methodId));

        return $paymentMethod;
    }
}
