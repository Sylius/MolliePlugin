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

namespace Sylius\MolliePlugin\Controller\Admin;

use Mollie\Api\Exceptions\ApiException;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Purifier\MolliePaymentMethodPurifierInterface;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

final class MethodsAction
{
    public function __construct(
        private readonly MollieLoggerActionInterface $loggerAction,
        private readonly RequestStack $requestStack,
        private readonly MollieMethodsResolverInterface $mollieMethodsResolver,
        private readonly MolliePaymentMethodPurifierInterface $methodPurifier,
        private readonly EntityRepository $gatewayConfigRepository,
    ) {
    }

    public function __invoke(int $id, Request $request): Response
    {
        /** @var Session $session */
        $session = $this->requestStack->getSession();

        try {
            /** @var GatewayConfigInterface $gateway */
            $gateway = $this->gatewayConfigRepository->find($id);

            $this->mollieMethodsResolver->createForGateway($gateway);

            $this->methodPurifier->removeAllNoLongerSupportedMethods();
            $session->getFlashBag()->add('success', 'sylius_mollie.admin.success_got_methods');

            return new Response('OK', Response::HTTP_OK);
        } catch (ApiException $e) {
            $this->loggerAction->addNegativeLog(sprintf('API call failed: %s', $e->getMessage()));

            $session->getFlashBag()->add('error', $e->getMessage());

            throw new UpdateHandlingException(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));
        }
    }
}
