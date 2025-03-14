<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Controller\Action\Admin;

use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Purifier\MolliePaymentMethodPurifierInterface;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;
use Mollie\Api\Exceptions\ApiException;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

final class MethodsAction
{
    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    /** @var RequestStack */
    private $requestStack;

    /** @var MollieMethodsResolverInterface */
    private $mollieMethodsResolver;

    /** @var MolliePaymentMethodPurifierInterface */
    private $methodPurifier;

    /** @var EntityRepository */
    private $gatewayConfigRepository;

    public function __construct(
        MollieLoggerActionInterface $loggerAction,
        RequestStack $requestStack,
        MollieMethodsResolverInterface $mollieMethodsResolver,
        MolliePaymentMethodPurifierInterface $methodPurifier,
        EntityRepository $gatewayConfigRepository
    ) {
        $this->loggerAction = $loggerAction;
        $this->requestStack = $requestStack;
        $this->mollieMethodsResolver = $mollieMethodsResolver;
        $this->methodPurifier = $methodPurifier;
        $this->gatewayConfigRepository = $gatewayConfigRepository;
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
            $session->getFlashBag()->add('success', 'sylius_mollie_plugin.admin.success_got_methods');

            return new Response('OK', Response::HTTP_OK);
        } catch (ApiException $e) {
            $this->loggerAction->addNegativeLog(sprintf('API call failed: %s', $e->getMessage()));

            $session->getFlashBag()->add('error', $e->getMessage());

            throw new UpdateHandlingException(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));
        }
    }
}
