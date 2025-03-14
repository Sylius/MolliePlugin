<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Resolver;

use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Creator\MollieMethodsCreatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayFactory;
use Sylius\MolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolver;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;
use Mollie\Api\Endpoints\MethodEndpoint;
use Mollie\Api\Resources\MethodCollection;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class MollieMethodsResolverSpec extends ObjectBehavior
{
    function let(
        MollieLoggerActionInterface $loggerAction,
        MollieApiClient $mollieApiClient,
        RepositoryInterface $gatewayConfigRepository,
        MollieMethodsCreatorInterface $helper
    ):void {
        $this->beConstructedWith(
            $loggerAction,
            $mollieApiClient,
            $gatewayConfigRepository,
            $helper
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieMethodsResolver::class);
    }

    function it_should_implements_mollie_methods_creator_interface(): void
    {
        $this->shouldImplement(MollieMethodsResolverInterface::class);
    }

    function it_creates_mollie_methods_for_gateway_subscription(
        GatewayConfigInterface $gateway,
        MollieApiClient $mollieApiClient,
        MollieApiClient $client,
        MethodEndpoint $methodEndpoint,
        MollieMethodsCreatorInterface $helper,
        MollieLoggerActionInterface $loggerAction,
        \stdClass $class
    ): void {
        $gateway->getConfig()->willReturn([
            'environment' => 'test',
            'api_key_test' => 'test_key123',
            'times' => '5',
            'interval' => '12 months'
        ]);
        $mollieApiClient->setApiKey('test_key123')->willReturn($client);
        $client->setIsRecurringSubscription(true);
        $gateway->getFactoryName()->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);
        $client->methods = $methodEndpoint;
        $methodCollection = new MethodCollection(5,$class);
        $recurringCollection = ['test2'];
        $methodEndpoint->allActive(MollieMethodsResolverInterface::PARAMETERS)
            ->willReturn($methodCollection);
        $methodEndpoint->allActive(MollieMethodsResolverInterface::PARAMETERS_RECURRING)->willReturn($recurringCollection);

        $helper->createMethods($methodCollection, $gateway)->shouldBeCalled();
        $loggerAction->addLog(sprintf('Downloaded all methods from mollie API'))->shouldBeCalled();

        $this->createForGateway($gateway);
    }

    function it_creates_mollie_methods_for_gateway_non_subscription(
        GatewayConfigInterface $gateway,
        MollieApiClient $mollieApiClient,
        MollieApiClient $client,
        MethodEndpoint $methodEndpoint,
        MollieMethodsResolverInterface $helper,
        MollieLoggerActionInterface $loggerAction,
        \stdClass $class
    ): void {
        $gateway->getConfig()->willReturn([
            'environment' => 'test',
            'api_key_test' => 'test_key123',
            'times' => '5',
            'interval' => '12 months'
        ]);
        $mollieApiClient->setApiKey('test_key123')->willReturn($client);
        $client->setIsRecurringSubscription(true);
        $gateway->getFactoryName()->willReturn(MollieGatewayFactory::FACTORY_NAME);
        $client->methods = $methodEndpoint;
        $methodCollection = new MethodCollection(5,$class);
        $methodEndpoint->allActive(MollieMethodsResolverInterface::PARAMETERS)
            ->willReturn($methodCollection);

        $helper->createMethods($methodCollection, $gateway)->shouldBeCalled();
        $loggerAction->addLog(sprintf('Downloaded all methods from mollie API'))->shouldBeCalled();

        $this->createForGateway($gateway);
    }

    function it_creates_mollie_methods_for_gateway_error(
        GatewayConfigInterface $gateway,
        MollieApiClient $mollieApiClient,
        MollieApiClient $client,
        MollieLoggerActionInterface $loggerAction
    ): void {
        $gateway->getConfig()->willReturn([
            'environment' => 'test',
            'api_key_test' => 'test_key123',
            'times' => '5',
            'interval' => '12 months'
        ]);
        $mollieApiClient->setApiKey('test_key123')->willReturn($client);
        $client->setIsRecurringSubscription(true);
        $gateway->getFactoryName()->willReturn('not_mollie_for_sure');
        $gateway->getGatewayName()->willReturn('not_mollie');

        $loggerAction->addLog(sprintf('Unable to download methods for "%s"', 'not_mollie'))
            ->shouldBeCalled();

        $this->createForGateway($gateway);
    }
}
