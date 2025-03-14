<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Creator\OnboardingWizard;

use Sylius\MolliePlugin\Context\Admin\AdminUserContextInterface;
use Sylius\MolliePlugin\Entity\OnboardingWizardStatusInterface;
use Sylius\MolliePlugin\Exceptions\AdminUserNotFound;
use Sylius\MolliePlugin\Resolver\OnboardingWizard\StatusResolverInterface;
use Doctrine\ORM\EntityManagerInterface;

final class StatusCreator implements StatusCreatorInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AdminUserContextInterface */
    private $adminUserContext;

    /** @var StatusResolverInterface */
    private $statusResolver;

    public function __construct(
        EntityManagerInterface $entityManager,
        AdminUserContextInterface $adminUserContext,
        StatusResolverInterface $statusResolver
    ) {
        $this->entityManager = $entityManager;
        $this->adminUserContext = $adminUserContext;
        $this->statusResolver = $statusResolver;
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
