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

namespace Sylius\MolliePlugin\Purifier;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;

final class MolliePaymentMethodPurifier implements MolliePaymentMethodPurifierInterface
{
    public function __construct(private readonly RepositoryInterface $repository)
    {
    }

    public function removeAllNoLongerSupportedMethods(): void
    {
        foreach (MollieMethodsResolverInterface::UNSUPPORTED_METHODS as $methodId) {
            $this->removeMethod($methodId);
        }
    }

    public function removeMethod(string $methodId): void
    {
        $methodConfig = $this->repository->findOneBy(['methodId' => $methodId]);

        if ($methodConfig instanceof MollieGatewayConfigInterface) {
            $this->repository->remove($methodConfig);
        }
    }
}
