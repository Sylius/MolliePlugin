<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Factory\OnboardingWizard;

use Sylius\MolliePlugin\Entity\OnboardingWizardStatusInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class StatusFactory implements StatusFactoryInterface
{
    /** @var FactoryInterface */
    private $factory;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
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
