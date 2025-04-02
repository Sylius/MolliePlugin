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

namespace Sylius\MolliePlugin\Resolver\OnboardingWizard;

use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\OnboardingWizardStatus;
use Sylius\MolliePlugin\Entity\OnboardingWizardStatusInterface;
use Sylius\MolliePlugin\Factory\OnboardingWizard\StatusFactoryInterface;

final class StatusResolver implements StatusResolverInterface
{
    public function __construct(private readonly RepositoryInterface $statusRepository, private readonly StatusFactoryInterface $statusFactory)
    {
    }

    public function resolve(AdminUserInterface $adminUser): OnboardingWizardStatusInterface
    {
        $onboardingWizardStatus = $this->statusRepository->findOneBy(['adminUser' => $adminUser]);

        if (!$onboardingWizardStatus instanceof OnboardingWizardStatus) {
            $onboardingWizardStatus = $this->statusFactory->create($adminUser, true);
        }

        return $onboardingWizardStatus;
    }
}
