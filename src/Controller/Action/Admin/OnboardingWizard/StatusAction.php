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

namespace Sylius\MolliePlugin\Controller\Action\Admin\OnboardingWizard;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Context\AdminUserContextInterface;
use Sylius\MolliePlugin\Entity\OnboardingWizardStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class StatusAction
{
    public function __construct(private readonly RepositoryInterface $statusRepository, private readonly AdminUserContextInterface $adminUserContext)
    {
    }

    public function __invoke(): Response
    {
        $adminUser = $this->adminUserContext->getAdminUser();

        if (null === $adminUser) {
            return new JsonResponse(['message' => "Couldn't resolve admin user account."], Response::HTTP_BAD_REQUEST);
        }

        $onboardingWizardStatus = $this->statusRepository->findOneBy(['adminUser' => $adminUser]);

        if ($onboardingWizardStatus instanceof OnboardingWizardStatus) {
            return new JsonResponse(['completed' => $onboardingWizardStatus->isCompleted()]);
        }

        return new JsonResponse(['completed' => false]);
    }
}
