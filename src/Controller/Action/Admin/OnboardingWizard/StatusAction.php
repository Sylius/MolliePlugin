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

namespace SyliusMolliePlugin\Controller\Action\Admin\OnboardingWizard;

use SyliusMolliePlugin\Context\Admin\AdminUserContextInterface;
use SyliusMolliePlugin\Entity\OnboardingWizardStatus;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class StatusAction
{
    /** @var RepositoryInterface */
    private $statusRepository;

    /** @var AdminUserContextInterface */
    private $adminUserContext;

    public function __construct(RepositoryInterface $statusRepository, AdminUserContextInterface $adminUserContext)
    {
        $this->statusRepository = $statusRepository;
        $this->adminUserContext = $adminUserContext;
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
