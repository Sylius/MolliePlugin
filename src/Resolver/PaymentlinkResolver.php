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

namespace SyliusMolliePlugin\Resolver;

use SyliusMolliePlugin\Client\MollieApiClient;
use SyliusMolliePlugin\Entity\MollieGatewayConfig;
use SyliusMolliePlugin\Factory\MollieGatewayFactory;
use SyliusMolliePlugin\Factory\MollieSubscriptionGatewayFactory;
use SyliusMolliePlugin\Form\Type\MollieGatewayConfigurationType;
use SyliusMolliePlugin\Helper\IntToStringConverterInterface;
use SyliusMolliePlugin\Preparer\PaymentLinkEmailPreparerInterface;
use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Sylius\AdminOrderCreationPlugin\Provider\PaymentTokenProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class PaymentlinkResolver implements PaymentlinkResolverInterface
{
    /** @var MollieApiClient */
    private $mollieApiClient;

    /** @var IntToStringConverterInterface */
    private $intToStringConverter;

    /** @var RepositoryInterface */
    private $orderRepository;

    /** @var PaymentLinkEmailPreparerInterface */
    private $emailPreparer;

    /** @var PaymentTokenProviderInterface */
    private $paymentTokenProvider;

    public function __construct(
        MollieApiClient $mollieApiClient,
        IntToStringConverterInterface $intToStringConverter,
        RepositoryInterface $orderRepository,
        PaymentLinkEmailPreparerInterface $emailPreparer,
        PaymentTokenProviderInterface $paymentTokenProvider
    ) {
        $this->mollieApiClient = $mollieApiClient;
        $this->intToStringConverter = $intToStringConverter;
        $this->orderRepository = $orderRepository;
        $this->emailPreparer = $emailPreparer;
        $this->paymentTokenProvider = $paymentTokenProvider;
    }

    public function resolve(
        OrderInterface $order,
        array $data,
        string $templateName
    ): string {
        $methodsArray = [];
        $methods = $data['methods'] ?? $data['methods'] = [];

        /** @var PaymentInterface $syliusPayment */
        $syliusPayment = $order->getPayments()->last();
        $firstPayment = $order->getPayments()->first();

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $syliusPayment->getMethod();

        Assert::notNull($paymentMethod->getGatewayConfig());
        if (false === in_array(
                $paymentMethod->getGatewayConfig()->getFactoryName(),
                [MollieGatewayFactory::FACTORY_NAME, MollieSubscriptionGatewayFactory::FACTORY_NAME],
                true
            )) {
            throw new NotFoundException('No method mollie found in order');
        }

        $modusKey = $this->getModus($paymentMethod->getGatewayConfig()->getConfig());

        /** @var MollieGatewayConfig $method */
        foreach ($methods as $method) {
            if (in_array($method->getMethodId(), self::NO_AVAILABLE_METHODS, true)) {
                continue;
            }

            $methodsArray[] = $method->getMethodId();
        }

        $this->mollieApiClient->setApiKey($modusKey);
        $details = $firstPayment->getDetails();

        if (!isset($details['webhookUrl'])) {
            return '';
        }

        try {
            $token = $this->paymentTokenProvider->getPaymentToken($syliusPayment);
            $redirectURL = $token->getTargetUrl();
        } catch (\Exception $e) {
            $redirectURL = $details['backurl'];
        }

        Assert::notNull($syliusPayment->getAmount());
        Assert::notNull($order->getCustomer());

        $data = [
            'method' => $methodsArray,
            'amount' => [
                'currency' => (string) $syliusPayment->getCurrencyCode(),
                'value' => $this->intToStringConverter->convertIntToString($syliusPayment->getAmount()),
            ],
            'description' => $order->getNumber(),
            'redirectUrl' => $redirectURL,
            'webhookUrl' => $details['webhookUrl'],
            'metadata' => [
                'order_id' => $order->getId(),
                'refund_token' => $details['refund_token'] ?? null,
                'customer_id' => $order->getCustomer()->getId(),
            ],
        ];

        $payment = $this->mollieApiClient->payments->create($data);
        $details['payment_mollie_id'] = $payment->id;
        $details['order_mollie_id'] = null;
        $details['metadata']['refund_token'] = $details['refund_token'] ?? null;
        $details['payment_mollie_link'] = $payment->_links->checkout->href;
        $details['backurl'] = $redirectURL;

        $syliusPayment->setDetails($details);

        $this->orderRepository->add($order);

        $this->emailPreparer->prepare($order, $templateName);

        return $payment->_links->checkout->href;
    }

    private function getModus(array $config): string
    {
        if ($config['environment']) {
            return $config[MollieGatewayConfigurationType::API_KEY_LIVE];
        }

        return $config[MollieGatewayConfigurationType::API_KEY_TEST];
    }
}
