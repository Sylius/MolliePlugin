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

namespace spec\Sylius\MolliePlugin\Action\Api;

use Sylius\MolliePlugin\Action\Api\BaseApiAwareAction;
use Sylius\MolliePlugin\Action\Api\CreateCustomerAction;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Entity\MollieCustomer;
use Sylius\MolliePlugin\Entity\MollieCustomerInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Request\Api\CreateCustomer;
use Mollie\Api\Endpoints\CustomerEndpoint;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Customer;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class CreateCustomerActionSpec extends ObjectBehavior
{
    function let(MollieLoggerActionInterface $loggerAction, RepositoryInterface $mollieCustomerRepository): void
    {
        $this->beConstructedWith(
            $loggerAction,
            $mollieCustomerRepository
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(CreateCustomerAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldImplement(ActionInterface::class);
    }

    function it_implements_api_aware_interface(): void
    {
        $this->shouldImplement(ApiAwareInterface::class);
    }

    function it_extends_base_api_aware(): void
    {
        $this->shouldHaveType(BaseApiAwareAction::class);
    }

    function it_executes_create_customer_action(
        CreateCustomer $request,
        MollieApiClient $mollieApiClient,
        CustomerEndpoint $customerEndpoint,
        Customer $customerMollie,
        MollieCustomerInterface $customer,
        MollieLoggerActionInterface $loggerAction,
        RepositoryInterface $mollieCustomerRepository
    ): void {
        $mollieApiClient->customers = $customerEndpoint;
        $this->setApi($mollieApiClient);

        $customerMollie->id = 'id_1';
        $details = new ArrayObject([
            'fullName' => 'Jan Kowalski',
            'email' => 'shop@example.com',
            'customer_mollie_id' => 'id_11',
        ]);
        $request->getModel()->willReturn($details);

        $mollieCustomerRepository->findOneBy(['email' => 'shop@example.com'])->willReturn($customer);
        $customerEndpoint->create(['name' => 'Jan Kowalski', 'email' => 'shop@example.com'])->willReturn($customerMollie);
        $customer->getProfileId()->willReturn('id_11');

        $loggerAction->addLog(sprintf('Create customer action with id:  %s', 'id_11'))->shouldBeCalled();

        $this->execute($request);
    }

    function it_executes_create_customer_action_when_customer_is_null(
        CreateCustomer $request,
        MollieApiClient $mollieApiClient,
        CustomerEndpoint $customerEndpoint,
        Customer $customerMollie,
        MollieLoggerActionInterface $loggerAction,
        RepositoryInterface $mollieCustomerRepository
    ): void {
        $mollieApiClient->customers = $customerEndpoint;
        $this->setApi($mollieApiClient);

        $customerMollie->id = 'id_1';
        $details = new ArrayObject([
            'fullName' => 'Jan Kowalski',
            'email' => 'shop@example.com',
            'customer_mollie_id' => 'id_11',
        ]);

        $request->getModel()->willReturn($details);

        $customerEndpoint->create(['name' => 'Jan Kowalski', 'email' => 'shop@example.com'])->willReturn($customerMollie);
        $customer = new MollieCustomer();
        $customer->setEmail('shop@example.com');
        $customer->setProfileId('id_1');

        $mollieCustomerRepository->findOneBy(["email" => "shop@example.com"])->willReturn(null);

        $mollieCustomerRepository->add($customer)->shouldBeCalled();
        $loggerAction->addLog(sprintf('Create customer action with id:  %s', 'id_1'))->shouldBeCalled();

        $this->execute($request);
    }

    function it_executes_create_customer_action_and_throws_api_exception(
        CreateCustomer $request,
        MollieApiClient $mollieApiClient,
        CustomerEndpoint $customerEndpoint,
        Customer $customerMollie,
        MollieLoggerActionInterface $loggerAction
    ): void {
        $mollieApiClient->customers = $customerEndpoint;
        $this->setApi($mollieApiClient);

        $customerMollie->id = 'id_1';
        $details = new ArrayObject([
            'fullName' => 'Jan Kowalski',
            'email' => 'shop@example.com',
        ]);
        $request->getModel()->willReturn($details);

        $customer = new MollieCustomer();
        $customer->setEmail('shop@example.com');
        $customer->setProfileId('id_1');
        $e = new \Exception('test_error');
        $customerEndpoint->create(['name' => 'Jan Kowalski', 'email' => 'shop@example.com'])->willThrow($e);

        $loggerAction->addNegativeLog(sprintf('Error with create customer:  %s', 'test_error'))->shouldBeCalled();

        $this->shouldThrow(ApiException::class)
            ->during('execute', [$request]);
    }

    function it_supports_only_create_customer_request_and_array_access(
        CreateCustomer $request,
        \ArrayAccess $arrayAccess
    ): void {
        $request->getModel()->willReturn($arrayAccess);

        $this->supports($request)->shouldReturn(true);
    }
}
