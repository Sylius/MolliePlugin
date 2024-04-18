<?php


declare(strict_types=1);

namespace SyliusMolliePlugin\Action\Api;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Parser\Response\GuzzleNegativeResponseParserInterface;
use SyliusMolliePlugin\Repository\CustomerRepository;
use SyliusMolliePlugin\Request\Api\CreatePayment;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

final class CreatePaymentAction extends BaseApiAwareAction
{
    use GatewayAwareTrait;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    /** @var GuzzleNegativeResponseParserInterface */
    private $guzzleNegativeResponseParser;

    /** @var RequestStack */
    private $requestStack;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /** @var RepositoryInterface */
    private $methodRepository;

    public function __construct(
        MollieLoggerActionInterface $loggerAction,
        GuzzleNegativeResponseParserInterface $guzzleNegativeResponseParser,
        RequestStack $requestStack,
        EntityRepository $customerRepository,
        RepositoryInterface $methodRepository
    ) {
        $this->loggerAction = $loggerAction;
        $this->guzzleNegativeResponseParser = $guzzleNegativeResponseParser;
        $this->requestStack = $requestStack;
        $this->customerRepository = $customerRepository;
        $this->methodRepository = $methodRepository;
    }

    public function execute($request): void
    {
        /** @var array $details */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        try {
            Assert::keyExists($details, 'metadata');
            Assert::keyExists($details['metadata'], 'molliePaymentMethods');
            $paymentDetails = [
                'method' => $details['metadata']['molliePaymentMethods'],
                'issuer' => $details['metadata']['selected_issuer'] ?? null,
                'cardToken' => $details['metadata']['cartToken'],
                'amount' => $details['amount'],
                'customerId' => $details['customerId'] ?? null,
                'description' => $details['description'],
                'redirectUrl' => $details['backurl'],
                'webhookUrl' => $details['webhookUrl'],
                'metadata' => $details['metadata'],
            ];

            if (true === isset($details['locale'])) {
                $paymentDetails['locale'] = $details['locale'];
            }

            if (isset($details['metadata']['saveCardInfo']) && $details['metadata']['saveCardInfo'] === "0") {
                unset($paymentDetails['customerId']);
            }

            if (isset($details['metadata']['useSavedCards']) && $details['metadata']['useSavedCards'] === "1") {
                unset($paymentDetails['cardToken']);
            }

            /** @var Payment $payment */
            $payment = $this->mollieApiClient->payments->create($paymentDetails);

            if (isset($details['metadata']['saveCardInfo'])) {
                $valueToUpdate = $details['metadata']['saveCardInfo'] === '1' ? '1' : null;
                $existingCustomer = $this->customerRepository->findOneBy(['profileId' => $details['customerId']]);

                if ($existingCustomer) {
                    $existingCustomer->setIsCreditCardSaved($valueToUpdate);
                    $this->customerRepository->add($existingCustomer);
                }
            }
        } catch (ApiException $e) {
            $message = $this->guzzleNegativeResponseParser->parse($e);
            $this->loggerAction->addNegativeLog(sprintf('Error with create payment with: %s', $e->getMessage()));

            if ('' === $message) {
                throw new ApiException(sprintf('Error with create payment with: %s', $e->getMessage()));
            }

            $details['statusError'] = $message;

            $message = \sprintf('%s%s', 'sylius_mollie_plugin.credit_cart_error.', $details['statusError']);
            $this->requestStack->getSession()->getFlashBag()->add('info', $message);

            return;
        } catch (\Exception $e) {
            $this->loggerAction->addNegativeLog(sprintf('Error with create payment with: %s', $e->getMessage()));

            throw new ApiException(sprintf('Error with create payment with: %s', $e->getMessage()));
        }

        $details['payment_mollie_id'] = $payment->id;

        $this->loggerAction->addLog(sprintf('Create payment in mollie with id: %s', $payment->id));

        $method = $this->methodRepository->findOneBy(['methodId' => $details['metadata']['molliePaymentMethods']]);
        $qrCodeEnabled = $method->isQrCodeEnabled();

        if (null === $payment->getCheckoutUrl() || $qrCodeEnabled) {
            throw new HttpRedirect($details['backurl']);
        }

        throw new HttpRedirect($payment->getCheckoutUrl());
    }

    public function supports($request): bool
    {
        return
            $request instanceof CreatePayment &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
