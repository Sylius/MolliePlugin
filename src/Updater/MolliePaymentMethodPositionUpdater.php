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

namespace Sylius\MolliePlugin\Updater;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;

final class MolliePaymentMethodPositionUpdater implements MolliePaymentMethodPositionUpdaterInterface
{
    public function __construct(
        private readonly RepositoryInterface $mollieGatewayRepository,
        private readonly EntityManagerInterface $mollieGatewayEntityManager,
    ) {
    }

    public function update(array $positions): void
    {
        foreach ($positions as $position) {
            $method = $this->mollieGatewayRepository->findOneBy([
                'methodId' => $position['name'],
                'id' => $position['identifier'],
            ]);
            if ($method instanceof MollieGatewayConfigInterface) {
                $method->setPosition((int) $position['id']);
                $this->mollieGatewayEntityManager->persist($method);
            }
        }

        $this->mollieGatewayEntityManager->flush();
    }
}
