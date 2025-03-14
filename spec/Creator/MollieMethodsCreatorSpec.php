<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Creator;

use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Creator\MollieMethodsCreator;
use Sylius\MolliePlugin\Creator\MollieMethodsCreatorInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Factory\MethodsFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieGatewayConfigFactoryInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use Sylius\MolliePlugin\Payments\Methods\MethodInterface;
use Sylius\MolliePlugin\Payments\MethodsInterface;
use Doctrine\ORM\EntityManagerInterface;
use Mollie\Api\Resources\Method;
use Mollie\Api\Resources\MethodCollection;
use PhpSpec\ObjectBehavior;

final class MollieMethodsCreatorSpec extends ObjectBehavior
{
    function let(
        MethodsFactoryInterface $methodsFactory,
        EntityManagerInterface $entityManager,
        MollieGatewayConfigFactoryInterface $factory
    ): void {
        $this->beConstructedWith(
            $methodsFactory,
            $entityManager,
            $factory
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(MollieMethodsCreator::class);
    }

    function it_should_implement_interface(): void
    {
        $this->shouldImplement(MollieMethodsCreatorInterface::class);
    }

    function it_creates_methods_when_all_mollie_methods_are_empty(
        MethodsFactoryInterface $methodsFactory,
        MethodsInterface $methods,
        MethodInterface $method,
        MethodCollection $methodCollection,
        GatewayConfigInterface $gatewayConfig,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        MollieGatewayConfigFactoryInterface $factory,
        EntityManagerInterface $entityManager
    ): void {
        $methodsFactory->createNew()->willReturn($methods);

        $methodsCollectionArray = [];
        $methodsCollectionArrayIterator = new \ArrayObject($methodsCollectionArray);

        $methodCollection->getIterator()->willReturn($methodsCollectionArrayIterator);

        $methods->getAllEnabled()->willReturn([$method]);
        $factory->create($method,$gatewayConfig,0)->willReturn($mollieGatewayConfig);

        $entityManager->persist($mollieGatewayConfig)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->createMethods($methodCollection, $gatewayConfig);
    }

    function it_creates_methods_when_all_mollie_methods_are_unsupported(
        MethodsFactoryInterface $methodsFactory,
        MethodsInterface $methods,
        MethodInterface $method,
        MethodCollection $methodCollection,
        GatewayConfigInterface $gatewayConfig,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        MollieGatewayConfigFactoryInterface $factory,
        EntityManagerInterface $entityManager,
        MollieApiClient $mollieApiClient
    ): void {
        $methodsFactory->createNew()->willReturn($methods);

        $mollieMethod = new Method($mollieApiClient->getWrappedObject());
        $mollieMethod->id = 'inghomepay';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayObject($methodsCollectionArray);

        $methodCollection->getIterator()->willReturn($methodsCollectionArrayIterator);

        $methods->add($mollieMethod)->shouldNotBeCalled();

        $methods->getAllEnabled()->willReturn([$method]);
        $factory->create($method,$gatewayConfig,0)->willReturn($mollieGatewayConfig);

        $entityManager->persist($mollieGatewayConfig)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->createMethods($methodCollection, $gatewayConfig);
    }

    function it_creates_methods_when_all_mollie_methods_are_not_unsupported_and_factory_name_is_not_subscription_and_supported_is_false_and_initial_is_true(
        MethodsFactoryInterface $methodsFactory,
        MethodsInterface $methods,
        MethodInterface $method,
        MethodCollection $methodCollection,
        GatewayConfigInterface $gatewayConfig,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        MollieGatewayConfigFactoryInterface $factory,
        EntityManagerInterface $entityManager,
        MollieApiClient $mollieApiClient
    ): void {
        $methodsFactory->createNew()->willReturn($methods);

        $mollieMethod = new Method($mollieApiClient->getWrappedObject());
        $mollieMethod->id = 'bancontact';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayObject($methodsCollectionArray);

        $gatewayConfig->getFactoryName()->willReturn('not_subscription');
        $methodCollection->getIterator()->willReturn($methodsCollectionArrayIterator);

        $methods->add($mollieMethod)->shouldBeCalled();

        $methods->getAllEnabled()->willReturn([$method]);
        $factory->create($method,$gatewayConfig,0)->willReturn($mollieGatewayConfig);

        $entityManager->persist($mollieGatewayConfig)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->createMethods($methodCollection, $gatewayConfig);
    }

    function it_creates_methods_when_all_mollie_methods_are_supported_and_factory_name_is_not_subscription_and_both_supported_and_initial_are_false(
        MethodsFactoryInterface $methodsFactory,
        MethodsInterface $methods,
        MethodInterface $method,
        MethodCollection $methodCollection,
        GatewayConfigInterface $gatewayConfig,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        MollieGatewayConfigFactoryInterface $factory,
        EntityManagerInterface $entityManager,
        MollieApiClient $mollieApiClient
    ): void {
        $methodsFactory->createNew()->willReturn($methods);

        $mollieMethod = new Method($mollieApiClient->getWrappedObject());
        $mollieMethod->id = 'not_inghomepay';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayObject($methodsCollectionArray);

        $gatewayConfig->getFactoryName()->willReturn('not_subscription');
        $methodCollection->getIterator()->willReturn($methodsCollectionArrayIterator);

        $methods->add($mollieMethod)->shouldBeCalled();

        $methods->getAllEnabled()->willReturn([$method]);
        $factory->create($method,$gatewayConfig,0)->willReturn($mollieGatewayConfig);

        $entityManager->persist($mollieGatewayConfig)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->createMethods($methodCollection, $gatewayConfig);
    }

    function it_creates_methods_when_all_mollie_methods_are_supported_and_factory_name_is_not_subscription_and_supported_is_true_and_initial_is_false(
        MethodsFactoryInterface $methodsFactory,
        MethodsInterface $methods,
        MethodInterface $method,
        MethodCollection $methodCollection,
        GatewayConfigInterface $gatewayConfig,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        MollieGatewayConfigFactoryInterface $factory,
        EntityManagerInterface $entityManager,
        MollieApiClient $mollieApiClient
    ): void {
        $methodsFactory->createNew()->willReturn($methods);

        $mollieMethod = new Method($mollieApiClient->getWrappedObject());
        $mollieMethod->id = 'directdebit';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayObject($methodsCollectionArray);

        $gatewayConfig->getFactoryName()->willReturn('not_subscription');
        $methodCollection->getIterator()->willReturn($methodsCollectionArrayIterator);

        $methods->add($mollieMethod)->shouldBeCalled();

        $methods->getAllEnabled()->willReturn([$method]);
        $factory->create($method,$gatewayConfig,0)->willReturn($mollieGatewayConfig);

        $entityManager->persist($mollieGatewayConfig)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->createMethods($methodCollection, $gatewayConfig);
    }

    function it_creates_methods_when_all_mollie_methods_are_supported_and_factory_name_is_subscription_and_supported_is_false_and_initial_is_true(
        MethodsFactoryInterface $methodsFactory,
        MethodsInterface $methods,
        MethodInterface $method,
        MethodCollection $methodCollection,
        GatewayConfigInterface $gatewayConfig,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        MollieGatewayConfigFactoryInterface $factory,
        EntityManagerInterface $entityManager,
        MollieApiClient $mollieApiClient
    ): void {
        $methodsFactory->createNew()->willReturn($methods);

        $mollieMethod = new Method($mollieApiClient->getWrappedObject());
        $mollieMethod->id = 'bancontact';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayObject($methodsCollectionArray);

        $gatewayConfig->getFactoryName()->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);
        $methodCollection->getIterator()->willReturn($methodsCollectionArrayIterator);

        $methods->add($mollieMethod)->shouldBeCalled();

        $methods->getAllEnabled()->willReturn([$method]);
        $factory->create($method,$gatewayConfig,0)->willReturn($mollieGatewayConfig);

        $entityManager->persist($mollieGatewayConfig)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->createMethods($methodCollection, $gatewayConfig);
    }

    function it_creates_methods_when_all_mollie_methods_are_supported_and_factory_name_is_subscription_and_both_supported_and_initial_are_false(
        MethodsFactoryInterface $methodsFactory,
        MethodsInterface $methods,
        MethodInterface $method,
        MethodCollection $methodCollection,
        GatewayConfigInterface $gatewayConfig,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        MollieGatewayConfigFactoryInterface $factory,
        EntityManagerInterface $entityManager,
        MollieApiClient $mollieApiClient
    ): void {
        $methodsFactory->createNew()->willReturn($methods);

        $mollieMethod = new Method($mollieApiClient->getWrappedObject());
        $mollieMethod->id = 'not_inghomepay';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayObject($methodsCollectionArray);

        $gatewayConfig->getFactoryName()->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);
        $methodCollection->getIterator()->willReturn($methodsCollectionArrayIterator);

        $methods->add($mollieMethod)->shouldNotBeCalled();

        $methods->getAllEnabled()->willReturn([$method]);
        $factory->create($method,$gatewayConfig,0)->willReturn($mollieGatewayConfig);

        $entityManager->persist($mollieGatewayConfig)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->createMethods($methodCollection, $gatewayConfig);
    }

    function it_creates_methods_when_all_mollie_methods_are_supported_and_factory_name_is_subscription_and_supported_is_true_and_initial_is_false(
        MethodsFactoryInterface $methodsFactory,
        MethodsInterface $methods,
        MethodInterface $method,
        MethodCollection $methodCollection,
        GatewayConfigInterface $gatewayConfig,
        MollieGatewayConfigInterface $mollieGatewayConfig,
        MollieGatewayConfigFactoryInterface $factory,
        EntityManagerInterface $entityManager,
        MollieApiClient $mollieApiClient
    ): void {
        $methodsFactory->createNew()->willReturn($methods);

        $mollieMethod = new Method($mollieApiClient->getWrappedObject());
        $mollieMethod->id = 'creditcard';
        $methodsCollectionArray = [$mollieMethod];
        $methodsCollectionArrayIterator = new \ArrayObject($methodsCollectionArray);

        $gatewayConfig->getFactoryName()->willReturn(MollieSubscriptionGatewayFactory::FACTORY_NAME);
        $methodCollection->getIterator()->willReturn($methodsCollectionArrayIterator);

        $methods->add($mollieMethod)->shouldBeCalled();

        $methods->getAllEnabled()->willReturn([$method]);
        $factory->create($method,$gatewayConfig,0)->willReturn($mollieGatewayConfig);

        $entityManager->persist($mollieGatewayConfig)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->createMethods($methodCollection, $gatewayConfig);
    }
}
