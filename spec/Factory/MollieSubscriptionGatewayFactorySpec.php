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

namespace spec\SyliusMolliePlugin\Factory;

use SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use Payum\Core\GatewayFactory;
use PhpSpec\ObjectBehavior;

final class MollieSubscriptionGatewayFactorySpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieSubscriptionGatewayFactory::class);
        $this->shouldHaveType(GatewayFactory::class);
    }

    function it_populateConfig_run(): void
    {
        $this->createConfig([]);
    }
}
