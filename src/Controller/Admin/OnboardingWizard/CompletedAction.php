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

namespace Sylius\MolliePlugin\Controller\Admin\OnboardingWizard;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\MolliePlugin\Context\AdminUserContextInterface;
use Sylius\MolliePlugin\Entity\OnboardingStatusAwareInterface;
use Sylius\MolliePlugin\Exceptions\AdminUserNotFound;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CompletedAction
{
    public function __construct(
        private readonly AdminUserContextInterface $adminUserContext,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(): Response
    {
        try {
            /** @var OnboardingStatusAwareInterface $adminUser */
            $adminUser = $this->adminUserContext->getAdminUser();
            $adminUser->setMollieOnboardingCompleted(true);

            $this->entityManager->flush();
        } catch (AdminUserNotFound $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['completed' => $adminUser->isMollieOnboardingCompleted()]);
    }
}
