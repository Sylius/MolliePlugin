<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Factory\MollieGatewayFactory;
use Payum\Core\GatewayFactory;
use PhpSpec\ObjectBehavior;

final class MollieGatewayFactorySpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieGatewayFactory::class);
        $this->shouldHaveType(GatewayFactory::class);
    }

    function it_populateConfig_run(): void
    {
        $this->createConfig([]);
    }
}
