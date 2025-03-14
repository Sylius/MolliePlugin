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

namespace Sylius\MolliePlugin\Factory\OnboardingWizard;

use Sylius\MolliePlugin\Entity\OnboardingWizardStatusInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class StatusFactory implements StatusFactoryInterface
{
    public function __construct(private readonly FactoryInterface $factory)
    {
    }

    public function createNew(): OnboardingWizardStatusInterface
    {
        /** @var OnboardingWizardStatusInterface $statusFactory */
        $statusFactory = $this->factory->createNew();

        return $statusFactory;
    }

    public function create(AdminUserInterface $adminUser, bool $completed): OnboardingWizardStatusInterface
    {
        $onboardingWizardStatus = $this->createNew();

        $onboardingWizardStatus->setAdminUser($adminUser);
        $onboardingWizardStatus->setCompleted($completed);

        return $onboardingWizardStatus;
    }
}
