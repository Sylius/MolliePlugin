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

use Sylius\MolliePlugin\Context\AdminUserContextInterface;
use Sylius\MolliePlugin\Entity\OnboardingStatusAwareInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class StatusAction
{
    public function __construct(private readonly AdminUserContextInterface $adminUserContext)
    {
    }

    public function __invoke(): Response
    {
        $adminUser = $this->adminUserContext->getAdminUser();
        if (null === $adminUser) {
            return new JsonResponse(['message' => "Couldn't resolve admin user account."], Response::HTTP_BAD_REQUEST);
        }

        $onboardingStatus = $adminUser instanceof OnboardingStatusAwareInterface
            ? $adminUser->isMollieOnboardingCompleted()
            : false
        ;

        return new JsonResponse(['completed' => $onboardingStatus]);
    }
}
