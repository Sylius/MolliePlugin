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
use Sylius\MolliePlugin\Entity\OnboardingWizardStatusInterface;

interface StatusResolverInterface
{
    public function resolve(AdminUserInterface $adminUser): OnboardingWizardStatusInterface;
}
