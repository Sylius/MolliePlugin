<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Action\Api;

use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionFactoryInterface;
use Sylius\MolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use Sylius\MolliePlugin\Repository\OrderRepositoryInterface;
use Sylius\MolliePlugin\Request\Api\CreateInternalRecurring;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

final class CreateInternalSubscriptionAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    private MollieSubscriptionRepositoryInterface $subscriptionRepository;

    private MollieSubscriptionFactoryInterface $subscriptionFactory;

    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        MollieSubscriptionRepositoryInterface $subscriptionRepository,
        MollieSubscriptionFactoryInterface $subscriptionFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->orderRepository = $orderRepository;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ('recurring' === $model['metadata']['sequenceType']) {
            return;
        }

        /** @var OrderInterface $rootOrder */
        $rootOrder = $this->orderRepository->find($model['metadata']['order_id']);

        foreach ($rootOrder->getRecurringItems() as $item) {
            $subscription = $this->subscriptionFactory->createFromFirstOrderWithOrderItemAndPaymentConfiguration(
                $rootOrder,
                $item,
                $model->getArrayCopy(),
                $model['mandate_mollie_id'] ?? null
            );
            $subscription->getSubscriptionConfiguration()->setCustomerId($model['customer_mollie_id']);
            $this->subscriptionRepository->add($subscription);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof CreateInternalRecurring &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
