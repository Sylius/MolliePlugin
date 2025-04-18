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
use Payum\Core\Bridge\Spl\ArrayObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieCustomer;
use Sylius\MolliePlugin\Entity\MollieCustomerInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Action\BaseApiAwareAction;
use Sylius\MolliePlugin\Payum\Action\CreateCustomerAction;
use Sylius\MolliePlugin\Payum\Request\CreateCustomer;

final class CreateCustomerActionTest extends TestCase
{
    private MollieLoggerActionInterface $loggerAction;

    private RepositoryInterface $mollieCustomerRepository;

    private CreateCustomerAction $createCustomerAction;

    protected function setUp(): void
    {
        $this->loggerAction = $this->createMock(MollieLoggerActionInterface::class);
        $this->mollieCustomerRepository = $this->createMock(RepositoryInterface::class);
        $this->createCustomerAction = new CreateCustomerAction($this->loggerAction, $this->mollieCustomerRepository);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(CreateCustomerAction::class, $this->createCustomerAction);
    }

    public function testItImplementsActionInterface(): void
    {
        $this->assertInstanceOf(ActionInterface::class, $this->createCustomerAction);
    }

    public function testItImplementsApiAwareInterface(): void
    {
        $this->assertInstanceOf(ApiAwareInterface::class, $this->createCustomerAction);
    }

    public function testItExtendsBaseApiAware(): void
    {
        $this->assertInstanceOf(BaseApiAwareAction::class, $this->createCustomerAction);
    }

    public function testItExecutesCreateCustomerAction(): void
    {
        $request = $this->createMock(CreateCustomer::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $customerEndpoint = $this->createMock(CustomerEndpoint::class);
        $customerMollie = $this->createMock(Customer::class);
        $customer = $this->createMock(MollieCustomerInterface::class);

        $mollieApiClient->customers = $customerEndpoint;
        $this->createCustomerAction->setApi($mollieApiClient);

        $details = new ArrayObject([
            'fullName' => 'Jan Kowalski',
            'email' => 'shop@example.com',
            'customer_mollie_id' => 'id_11',
        ]);

        $request->method('getModel')->willReturn($details);
        $customerMollie->id = 'id_1';

        $this->mollieCustomerRepository->method('findOneBy')
            ->with(['email' => 'shop@example.com'])
            ->willReturn($customer)
        ;

        $customerEndpoint->method('create')
            ->with(['name' => 'Jan Kowalski', 'email' => 'shop@example.com'])
            ->willReturn($customerMollie)
        ;

        $customer->method('getProfileId')->willReturn('id_11');

        $this->loggerAction->expects($this->once())
            ->method('addLog')
            ->with(sprintf('Create customer action with id:  %s', 'id_11'))
        ;

        $this->createCustomerAction->execute($request);
    }

    public function testItExecutesCreateCustomerActionWhenCustomerIsNull(): void
    {
        $request = $this->createMock(CreateCustomer::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $customerEndpoint = $this->createMock(CustomerEndpoint::class);
        $customerMollie = $this->createMock(Customer::class);

        $mollieApiClient->customers = $customerEndpoint;
        $this->createCustomerAction->setApi($mollieApiClient);

        $details = new ArrayObject([
            'fullName' => 'Jan Kowalski',
            'email' => 'shop@example.com',
            'customer_mollie_id' => 'id_11',
        ]);

        $request->method('getModel')->willReturn($details);
        $customerMollie->id = 'id_1';

        $customer = new MollieCustomer();
        $customer->setEmail('shop@example.com');
        $customer->setProfileId('id_1');

        $this->mollieCustomerRepository->method('findOneBy')
            ->with(['email' => 'shop@example.com'])
            ->willReturn(null)
        ;

        $customerEndpoint->method('create')
            ->with(['name' => 'Jan Kowalski', 'email' => 'shop@example.com'])
            ->willReturn($customerMollie)
        ;

        $this->mollieCustomerRepository->expects($this->once())
            ->method('add')
            ->with($customer)
        ;

        $this->loggerAction->expects($this->once())
            ->method('addLog')
            ->with(sprintf('Create customer action with id:  %s', 'id_1'))
        ;

        $this->createCustomerAction->execute($request);
    }

    public function testItThrowsApiExceptionDuringExecution(): void
    {
        $request = $this->createMock(CreateCustomer::class);
        $mollieApiClient = $this->createMock(MollieApiClient::class);
        $customerEndpoint = $this->createMock(CustomerEndpoint::class);

        $mollieApiClient->customers = $customerEndpoint;
        $this->createCustomerAction->setApi($mollieApiClient);

        $details = new ArrayObject([
            'fullName' => 'Jan Kowalski',
            'email' => 'shop@example.com',
        ]);

        $request->method('getModel')->willReturn($details);

        $exception = new \Exception('test_error');
        $customerEndpoint->method('create')
            ->with(['name' => 'Jan Kowalski', 'email' => 'shop@example.com'])
            ->willThrowException($exception)
        ;

        $this->loggerAction->expects($this->once())
            ->method('addNegativeLog')
            ->with(sprintf('Error with create customer:  %s', 'test_error'))
        ;

        $this->expectException(ApiException::class);
        $this->createCustomerAction->execute($request);
    }

    public function testItSupportsOnlyCreateCustomerRequestAndArrayAccess(): void
    {
        $request = $this->createMock(CreateCustomer::class);
        $arrayAccess = $this->createMock(\ArrayAccess::class);

        $request->method('getModel')->willReturn($arrayAccess);

        $this->assertTrue($this->createCustomerAction->supports($request));
    }
}
