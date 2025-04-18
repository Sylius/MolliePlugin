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

namespace Tests\Sylius\MolliePlugin\Unit\Action\Api;

use Mollie\Api\Endpoints\CustomerEndpoint;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Customer;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Action\BaseApiAwareAction;
use Sylius\MolliePlugin\Payum\Action\Subscription\CancelRecurringSubscriptionAction;
use Sylius\MolliePlugin\Payum\Request\Subscription\CancelRecurringSubscription;

final class CancelRecurringSubscriptionActionTest extends TestCase
{
    private MollieLoggerActionInterface $loggerAction;

    private CancelRecurringSubscriptionAction $cancelRecurringSubscriptionAction;

    protected function setUp(): void
    {
        $this->loggerAction = $this->createMock(MollieLoggerActionInterface::class);
        $this->cancelRecurringSubscriptionAction = new CancelRecurringSubscriptionAction($this->loggerAction);
    }

    public function testItImplementsActionInterface(): void
    {
        $this->assertInstanceOf(ActionInterface::class, $this->cancelRecurringSubscriptionAction);
    }

    public function testItImplementsApiAwareInterface(): void
    {
        $this->assertInstanceOf(ApiAwareInterface::class, $this->cancelRecurringSubscriptionAction);
    }

    public function testItExtendsBaseApiAware(): void
    {
        $this->assertInstanceOf(BaseApiAwareAction::class, $this->cancelRecurringSubscriptionAction);
    }

    public function testItExecutesCancelRecurringSubscriptionAction(): void
    {
        $request = $this->createMock(CancelRecurringSubscription::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $subscription = $this->createMock(MollieSubscriptionInterface::class);
        $configuration = $this->createMock(MollieSubscriptionConfigurationInterface::class);
        $customerEndpoint = $this->createMock(CustomerEndpoint::class);
        $customer = $this->createMock(Customer::class);

        $mollieApiClient->customers = $customerEndpoint;
        $this->cancelRecurringSubscriptionAction->setApi($mollieApiClient);

        $request->method('getModel')->willReturn($subscription);
        $subscription->method('getSubscriptionConfiguration')->willReturn($configuration);
        $configuration->method('getCustomerId')->willReturn('id_1');
        $configuration->method('getSubscriptionId')->willReturn('sub_id_1');
        $customerEndpoint->method('get')->with('id_1')->willReturn($customer);

        $this->loggerAction->expects($this->once())
            ->method('addLog')
            ->with('Cancel recurring subscription with id:  sub_id_1')
        ;

        $customer->expects($this->once())
            ->method('cancelSubscription')
            ->with('sub_id_1')
        ;

        $this->cancelRecurringSubscriptionAction->execute($request);
    }

    public function testItThrowsApiExceptionDuringExecution(): void
    {
        $request = $this->createMock(CancelRecurringSubscription::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $subscription = $this->createMock(MollieSubscriptionInterface::class);
        $configuration = $this->createMock(MollieSubscriptionConfigurationInterface::class);
        $customerEndpoint = $this->createMock(CustomerEndpoint::class);

        $mollieApiClient->customers = $customerEndpoint;
        $this->cancelRecurringSubscriptionAction->setApi($mollieApiClient);

        $request->method('getModel')->willReturn($subscription);
        $subscription->method('getSubscriptionConfiguration')->willReturn($configuration);
        $configuration->method('getCustomerId')->willReturn('id_1');
        $configuration->method('getSubscriptionId')->willReturn('sub_id_1');
        $exception = new \Exception('error_test');
        $customerEndpoint->method('get')->with('id_1')->willThrowException($exception);

        $this->loggerAction->expects($this->once())
            ->method('addNegativeLog')
            ->with(sprintf('Error with get customer in recurring subscription with: %s', 'error_test'))
        ;

        $this->expectException(ApiException::class);
        $this->cancelRecurringSubscriptionAction->execute($request);
    }

    public function testItSupportsCancelRecurringSubscriptionRequestAndSubscriptionModel(): void
    {
        $request = $this->createMock(CancelRecurringSubscription::class);
        $subscription = $this->createMock(MollieSubscriptionInterface::class);

        $request->method('getModel')->willReturn($subscription);

        $this->assertTrue($this->cancelRecurringSubscriptionAction->supports($request));
    }
}
