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

namespace Sylius\MolliePlugin\Payum\Action\Subscription;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Factory\MollieSubscriptionFactoryInterface;
use Sylius\MolliePlugin\Payum\Action\BaseApiAwareAction;
use Sylius\MolliePlugin\Payum\Request\Subscription\CreateInternalRecurring;
use Sylius\MolliePlugin\Repository\MollieSubscriptionRepositoryInterface;
use Sylius\MolliePlugin\Repository\OrderRepositoryInterface;

final class CreateInternalSubscriptionAction extends BaseApiAwareAction
{
    public function __construct(
        private readonly MollieSubscriptionRepositoryInterface $subscriptionRepository,
        private readonly MollieSubscriptionFactoryInterface $subscriptionFactory,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
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
                $model['mandate_mollie_id'] ?? null,
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
