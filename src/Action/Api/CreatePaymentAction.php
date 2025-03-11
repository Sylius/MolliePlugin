<?php


declare(strict_types=1);

namespace SyliusMolliePlugin\Action\Api;

use Mollie\Api\Types\PaymentMethod;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Parser\Response\GuzzleNegativeResponseParserInterface;
use SyliusMolliePlugin\Request\Api\CreatePayment;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Payment;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Webmozart\Assert\Assert;

final class CreatePaymentAction extends BaseApiAwareAction
{
    use GatewayAwareTrait;

    public function __construct(
        private MollieLoggerActionInterface $loggerAction,
        private GuzzleNegativeResponseParserInterface $guzzleNegativeResponseParser,
        private RequestStack $requestStack,
        private EntityRepository $customerRepository
    ) {
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
                'cardToken' => $details['metadata']['cartToken'],
                'amount' => $details['amount'],
                'customerId' => $details['customerId'] ?? null,
                'description' => $details['description'],
                'redirectUrl' => $details['backurl'],
                'webhookUrl' => $details['webhookUrl'],
                'metadata' => $details['metadata'],
            ];

            if ($details['metadata']['molliePaymentMethods'] === PaymentMethod::ALMA ||
                $details['metadata']['molliePaymentMethods'] === PaymentMethod::TRUSTLY) {
                $paymentDetails['billingAddress'] = $details['billingAddress'];
            }

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

                if (null !== $existingCustomer) {
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
            /** @var Session $session */
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add('info', $message);

            return;
        } catch (\Exception $e) {
            $this->loggerAction->addNegativeLog(sprintf('Error with create payment with: %s', $e->getMessage()));

            throw new ApiException(sprintf('Error with create payment with: %s', $e->getMessage()));
        }

        $details['payment_mollie_id'] = $payment->id;

        $this->loggerAction->addLog(sprintf('Create payment in mollie with id: %s', $payment->id));

        if (null === $payment->getCheckoutUrl()) {
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
