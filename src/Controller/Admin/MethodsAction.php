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

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Exceptions\MollieMethodsException;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Updater\MollieMethodsUpdaterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

final class MethodsAction
{
    public function __construct(
        private readonly MollieLoggerActionInterface $loggerAction,
        private readonly RequestStack $requestStack,
        private readonly MollieMethodsUpdaterInterface $mollieMethodsUpdater,
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

            $this->mollieMethodsUpdater->update($gateway, true);

            $session->getFlashBag()->add('success', 'sylius_mollie.admin.success_got_methods');

            return new Response('OK', Response::HTTP_OK);
        } catch (MollieMethodsException $e) {
            $this->loggerAction->addNegativeLog($e->getMessage());

            $session->getFlashBag()->add('error', $e->getMessage());

            throw new UpdateHandlingException(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));
        }
    }
}
