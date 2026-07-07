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

namespace Tests\Sylius\MolliePlugin\Unit\Action;

use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\Resources\Payment;
use Payum\Core\Bridge\Spl\ArrayObject;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Client\MollieApiClient;
use Sylius\MolliePlugin\Client\Parser\ApiExceptionParserInterface;
use Sylius\MolliePlugin\Logger\MollieLoggerActionInterface;
use Sylius\MolliePlugin\Payum\Action\CreateOnDemandPaymentAction;
use Sylius\MolliePlugin\Payum\Request\Subscription\CreateOnDemandSubscriptionPayment;

final class CreateOnDemandPaymentActionTest extends TestCase
{
    private MollieLoggerActionInterface $logger;

    private ApiExceptionParserInterface $apiExceptionParser;

    private MollieApiClient $mollieApiClient;

    private PaymentEndpoint $paymentEndpoint;

    private CreateOnDemandPaymentAction $action;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(MollieLoggerActionInterface::class);
        $this->apiExceptionParser = $this->createMock(ApiExceptionParserInterface::class);

        $this->action = new CreateOnDemandPaymentAction($this->logger, $this->apiExceptionParser);

        $this->mollieApiClient = $this->createMock(MollieApiClient::class);
        $this->paymentEndpoint = $this->createMock(PaymentEndpoint::class);
        $this->mollieApiClient->payments = $this->paymentEndpoint;

        $this->action->setApi($this->mollieApiClient);
    }

    public function testItChargesRecurringWithoutCardTokenAndMethodWhenAbsent(): void
    {
        $details = [
            'amount' => ['value' => '10.00', 'currency' => 'EUR'],
            'customerId' => 'cst_abc',
            'mandateId' => 'mdt_xyz',
            'description' => 'Subscription renewal',
            'webhookUrl' => 'https://shop.example.com/update-payment',
            'metadata' => [
                'sequenceType' => 'recurring',
                'molliePaymentMethods' => null,
                'cartToken' => null,
            ],
        ];

        $payment = new Payment($this->mollieApiClient);
        $payment->id = 'tr_123';

        $this->paymentEndpoint
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (array $payload): bool {
                return false === array_key_exists('cardToken', $payload) &&
                    false === array_key_exists('method', $payload) &&
                    'recurring' === $payload['sequenceType'] &&
                    'cst_abc' === $payload['customerId'] &&
                    'mdt_xyz' === $payload['mandateId'];
            }))
            ->willReturn($payment)
        ;

        $this->action->execute(new CreateOnDemandSubscriptionPayment(new ArrayObject($details)));
    }

    public function testItKeepsMethodAndCardTokenWhenPresent(): void
    {
        $details = [
            'amount' => ['value' => '10.00', 'currency' => 'EUR'],
            'customerId' => 'cst_abc',
            'mandateId' => 'mdt_xyz',
            'description' => 'Subscription renewal',
            'webhookUrl' => 'https://shop.example.com/update-payment',
            'metadata' => [
                'sequenceType' => 'recurring',
                'molliePaymentMethods' => 'creditcard',
                'cartToken' => 'tkn_live',
            ],
        ];

        $payment = new Payment($this->mollieApiClient);
        $payment->id = 'tr_456';

        $this->paymentEndpoint
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (array $payload): bool {
                return 'creditcard' === ($payload['method'] ?? null) &&
                    'tkn_live' === ($payload['cardToken'] ?? null);
            }))
            ->willReturn($payment)
        ;

        $this->action->execute(new CreateOnDemandSubscriptionPayment(new ArrayObject($details)));
    }

    public function testItOmitsMethodAndCardTokenWhenBlankString(): void
    {
        $details = [
            'amount' => ['value' => '10.00', 'currency' => 'EUR'],
            'customerId' => 'cst_abc',
            'mandateId' => 'mdt_xyz',
            'description' => 'Subscription renewal',
            'webhookUrl' => 'https://shop.example.com/update-payment',
            'metadata' => [
                'sequenceType' => 'recurring',
                'molliePaymentMethods' => '',
                'cartToken' => '',
            ],
        ];

        $payment = new Payment($this->mollieApiClient);
        $payment->id = 'tr_789';

        $this->paymentEndpoint
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (array $payload): bool {
                return false === array_key_exists('cardToken', $payload) &&
                    false === array_key_exists('method', $payload) &&
                    'recurring' === $payload['sequenceType'] &&
                    'cst_abc' === $payload['customerId'] &&
                    'mdt_xyz' === $payload['mandateId'];
            }))
            ->willReturn($payment)
        ;

        $this->action->execute(new CreateOnDemandSubscriptionPayment(new ArrayObject($details)));
    }
}
