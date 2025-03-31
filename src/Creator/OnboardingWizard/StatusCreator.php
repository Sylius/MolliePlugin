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

namespace Sylius\MolliePlugin\Creator\OnboardingWizard;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\MolliePlugin\Context\AdminUserContextInterface;
use Sylius\MolliePlugin\Entity\OnboardingWizardStatusInterface;
use Sylius\MolliePlugin\Exceptions\AdminUserNotFound;
use Sylius\MolliePlugin\Resolver\OnboardingWizard\StatusResolverInterface;

final class StatusCreator implements StatusCreatorInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly AdminUserContextInterface $adminUserContext, private readonly StatusResolverInterface $statusResolver)
    {
    }

    public function create(): OnboardingWizardStatusInterface
    {
        $adminUser = $this->adminUserContext->getAdminUser();

        if (null === $adminUser) {
            throw new AdminUserNotFound("Couldn't resolve admin user account.");
        }

        $onboardingWizardStatus = $this->statusResolver->resolve($adminUser);

        $this->entityManager->persist($onboardingWizardStatus);
        $this->entityManager->flush();

        return $onboardingWizardStatus;
    }
}
