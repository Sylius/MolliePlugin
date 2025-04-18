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

namespace Tests\SyliusMolliePlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use SyliusMolliePlugin\Entity\GatewayConfigInterface;
use SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use SyliusMolliePlugin\Factory\MollieGatewayFactory;
use SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Purifier\MolliePaymentMethodPurifierInterface;
use SyliusMolliePlugin\Repository\MollieGatewayConfigRepositoryInterface;
use SyliusMolliePlugin\Resolver\MollieMethodsResolverInterface;
use Doctrine\ORM\EntityManager;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class MollieContext implements Context
{
    /** @var SharedStorageInterface */
    private $sharedStorage;

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var ExampleFactoryInterface */
    private $paymentMethodExampleFactory;

    /** @var EntityManager */
    private $paymentMethodManager;

    private string $mollieApiKeyTest;

    private string $mollieProfileId;

    private MollieMethodsResolverInterface $mollieMethodsResolver;

    private MolliePaymentMethodPurifierInterface $molliePaymentMethodPurifier;

    private RepositoryInterface $gatewayConfigRepository;

    private MollieGatewayConfigRepositoryInterface $mollieConfigurationRepository;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ExampleFactoryInterface $paymentMethodExampleFactory,
        EntityManager $paymentMethodManager,
        string $mollieApiKeyTest,
        string $mollieProfileId,
        MollieMethodsResolverInterface $mollieMethodsResolver,
        MolliePaymentMethodPurifierInterface $molliePaymentMethodPurifier,
        RepositoryInterface $gatewayConfigRepository,
        MollieGatewayConfigRepositoryInterface $mollieConfigurationRepository
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodExampleFactory = $paymentMethodExampleFactory;
        $this->paymentMethodManager = $paymentMethodManager;
        $this->mollieApiKeyTest = $mollieApiKeyTest;
        $this->mollieProfileId = $mollieProfileId;
        $this->mollieMethodsResolver = $mollieMethodsResolver;
        $this->molliePaymentMethodPurifier = $molliePaymentMethodPurifier;
        $this->gatewayConfigRepository = $gatewayConfigRepository;
        $this->mollieConfigurationRepository = $mollieConfigurationRepository;
    }

    /**
     * @Given the store has a payment method :paymentMethodName with a code :paymentMethodCode and Mollie payment gateway
     */
    public function theStoreHasAPaymentMethodWithACodeAndMolliePaymentGateway(string $paymentMethodName, string $paymentMethodCode): void
    {
        $paymentMethod = $this->createPaymentMethodMollie(
            $paymentMethodName,
            $paymentMethodCode,
            MollieGatewayFactory::FACTORY_NAME,
            'Mollie'
        );

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);
        $gatewayConfig->setConfig([
            'api_key' => 'test',
            'payum.http_client' => '@sylius_mollie_plugin.mollie_api_client',
            'api_key_test' => $this->mollieApiKeyTest,
            'profile_id' => $this->mollieProfileId,
            'environment' => null,
            'loggerLevel' => MollieLoggerActionInterface::LOG_EVERYTHING,
        ]);

        $this->paymentMethodManager->flush();
    }

    /**
     * @Given gateway :paymentMethodCode has all methods loaded and enabled
     */
    public function gatewayHasAllMethodsLoadedAndEnabled(string $paymentMethodCode): void
    {
        $gatewayConfig = $this->gatewayConfigRepository
            ->findOneBy([
                'gatewayName' => $paymentMethodCode,
            ]);

        Assert::notNull($gatewayConfig);
        Assert::isInstanceOf($gatewayConfig, GatewayConfigInterface::class);
        $this->loadMolliePaymentMethods($gatewayConfig);

        $this->enableAllMolliePaymentMethods();
    }

    /**
     * @Given the store has a payment method :paymentMethodName with a code :paymentMethodCode and Mollie Subscription payment gateway
     */
    public function theStoreHasAPaymentMethodWithACodeAndMollieSubscriptionPaymentGateway(string $paymentMethodName, string $paymentMethodCode): void
    {
        $paymentMethod = $this->createPaymentMethodMollie(
            $paymentMethodName,
            $paymentMethodCode,
            MollieSubscriptionGatewayFactory::FACTORY_NAME,
            'Mollie Subscription'
        );

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);
        $gatewayConfig->setConfig([
            'api_key' => 'test',
            'payum.http_client' => '@sylius_mollie_plugin.mollie_api_client',
            'api_key_test' => $this->mollieApiKeyTest,
            'profile_id' => $this->mollieProfileId,
            'environment' => null,
            'loggerLevel' => MollieLoggerActionInterface::LOG_EVERYTHING,
        ]);

        $this->paymentMethodManager->flush();
    }

    private function loadMolliePaymentMethods(GatewayConfigInterface $gatewayConfig): void
    {
        $this->mollieMethodsResolver->createForGateway($gatewayConfig);

        $this->molliePaymentMethodPurifier->removeAllNoLongerSupportedMethods();
    }

    private function enableAllMolliePaymentMethods(): void
    {
        $molliePaymentMethods = $this->mollieConfigurationRepository->findAll();

        /** @var MollieGatewayConfigInterface $molliePaymentMethod */
        foreach ($molliePaymentMethods as $molliePaymentMethod) {
            $molliePaymentMethod->enable();
            $molliePaymentMethod->setCountryRestriction(
                MollieGatewayConfigInterface::ALL_COUNTRIES
            );
        }
    }

    private function createPaymentMethodMollie(
        string $name,
        string $code,
        string $factoryName,
        string $description = '',
        bool $addForCurrentChannel = true,
        int $position = null
    ): PaymentMethodInterface {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => ucfirst($name),
            'code' => $code,
            'description' => $description,
            'gatewayName' => $factoryName,
            'gatewayFactory' => $factoryName,
            'enabled' => true,
            'channels' => ($addForCurrentChannel && $this->sharedStorage->has('channel')) ? [$this->sharedStorage->get('channel')] : [],
        ]);

        if (null !== $position) {
            $paymentMethod->setPosition($position);
        }

        $this->sharedStorage->set('payment_method', $paymentMethod);
        $this->paymentMethodRepository->add($paymentMethod);

        return $paymentMethod;
    }
}
