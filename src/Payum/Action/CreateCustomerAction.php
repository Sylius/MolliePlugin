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

namespace Sylius\MolliePlugin\Payum\Action;

use Mollie\Api\Exceptions\ApiException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\MolliePlugin\Entity\MollieCustomer;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Request\CreateCustomer;

final class CreateCustomerAction extends BaseApiAwareAction
{
    public function __construct(
        private readonly MollieLoggerActionInterface $loggerAction,
        private readonly RepositoryInterface $mollieCustomerRepository,
    ) {
    }

    /** @param CreateCustomer|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $data = [
            'name' => $model['fullName'],
            'email' => $model['email'],
        ];

        /** @var ?MollieCustomer $customer */
        $customer = $this->mollieCustomerRepository->findOneBy(['email' => $model['email']]);

        if (null === $customer) {
            $customer = new MollieCustomer();
            $customer->setEmail($model['email']);
        }

        try {
            if (null === $customer->getProfileId()) {
                $customerMollie = $this->mollieApiClient->customers->create($data);
                $customer->setProfileId($customerMollie->id);

                $this->mollieCustomerRepository->add($customer);
            }
        } catch (\Exception $e) {
            $this->loggerAction->addNegativeLog(sprintf('Error with create customer:  %s', $e->getMessage()));

            throw new ApiException('Error with create customer with' . $e->getMessage());
        }

        $this->loggerAction->addLog(sprintf('Create customer action with id:  %s', $customer->getProfileId()));

        $model['customer_mollie_id'] = $customer->getProfileId();
    }

    public function supports($request): bool
    {
        return
            $request instanceof CreateCustomer &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
