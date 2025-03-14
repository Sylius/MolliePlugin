<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Action\Api;

use Sylius\MolliePlugin\Entity\MollieCustomer;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Request\Api\CreateCustomer;
use Mollie\Api\Exceptions\ApiException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class CreateCustomerAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    /** @var RepositoryInterface */
    private $mollieCustomerRepository;

    public function __construct(MollieLoggerActionInterface $loggerAction, RepositoryInterface $mollieCustomerRepository)
    {
        $this->loggerAction = $loggerAction;
        $this->mollieCustomerRepository = $mollieCustomerRepository;
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
