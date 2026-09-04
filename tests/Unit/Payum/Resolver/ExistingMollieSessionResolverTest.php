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

namespace Tests\Sylius\MolliePlugin\Unit\Payum\Resolver;

use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Sylius\MolliePlugin\Payum\Resolver\ExistingMollieSessionDecision;
use Sylius\MolliePlugin\Payum\Resolver\ExistingMollieSessionResolver;

final class ExistingMollieSessionResolverTest extends TestCase
{
    private const TOKEN_URL = 'https://shop.test/payment/capture/current-token';

    private ExistingMollieSessionResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ExistingMollieSessionResolver();
    }

    /** @dataProvider nonOpenStatuses */
    public function testItLeavesAnythingThatIsNotOpenToTheStatusFlow(string $status): void
    {
        self::assertSame(
            ExistingMollieSessionDecision::LeaveToStatusFlow,
            $this->resolver->resolve($this->session($status), new ArrayObject(), $this->token()),
        );
    }

    /** @return iterable<array{string}> */
    public static function nonOpenStatuses(): iterable
    {
        yield [PaymentStatus::STATUS_CANCELED];
        yield [PaymentStatus::STATUS_FAILED];
        yield [PaymentStatus::STATUS_EXPIRED];
        yield [PaymentStatus::STATUS_PENDING];
        yield [PaymentStatus::STATUS_PAID];
        yield [PaymentStatus::STATUS_AUTHORIZED];
    }

    public function testItLeavesTheReturnFromMollieToTheStatusFlow(): void
    {
        $session = $this->session(PaymentStatus::STATUS_OPEN, redirectUrl: self::TOKEN_URL);

        self::assertSame(
            ExistingMollieSessionDecision::LeaveToStatusFlow,
            $this->resolver->resolve($session, new ArrayObject(), $this->token()),
        );
    }

    public function testItResumesWhenTheMethodDidNotChange(): void
    {
        $session = $this->session(PaymentStatus::STATUS_OPEN, method: 'ideal');
        $details = new ArrayObject(['molliePaymentMethods' => 'ideal']);

        self::assertSame(
            ExistingMollieSessionDecision::Resume,
            $this->resolver->resolve($session, $details, $this->token()),
        );
    }

    public function testItResumesWhenTheSessionHasNoMethodOfItsOwn(): void
    {
        $session = $this->session(PaymentStatus::STATUS_OPEN, method: null);
        $details = new ArrayObject(['molliePaymentMethods' => 'ideal']);

        self::assertSame(
            ExistingMollieSessionDecision::Resume,
            $this->resolver->resolve($session, $details, $this->token()),
        );
    }

    public function testItReplacesTheSessionOnASurchargeNeutralMethodChange(): void
    {
        $session = $this->session(PaymentStatus::STATUS_OPEN, method: 'ideal');
        $details = new ArrayObject(['molliePaymentMethods' => 'creditcard']);

        self::assertSame(
            ExistingMollieSessionDecision::Replace,
            $this->resolver->resolve($session, $details, $this->token()),
        );
    }

    public function testItReadsTheRequestedMethodFromMetadataWhenNotSetOnTopLevel(): void
    {
        $session = $this->session(PaymentStatus::STATUS_OPEN, method: 'ideal');
        $details = new ArrayObject(['metadata' => ['molliePaymentMethods' => 'ideal']]);

        self::assertSame(
            ExistingMollieSessionDecision::Resume,
            $this->resolver->resolve($session, $details, $this->token()),
        );
    }

    public function testItTreatsAnOrderApiSessionAwaitingPaymentAsOpen(): void
    {
        $session = $this->session(OrderStatus::STATUS_CREATED, method: 'klarna');
        $details = new ArrayObject(['molliePaymentMethods' => 'klarna']);

        self::assertSame(
            ExistingMollieSessionDecision::Resume,
            $this->resolver->resolve($session, $details, $this->token()),
        );
    }

    public function testItResumesWhenThereIsNoTokenToCompareAgainst(): void
    {
        $session = $this->session(PaymentStatus::STATUS_OPEN, method: 'ideal');
        $details = new ArrayObject(['molliePaymentMethods' => 'ideal']);

        self::assertSame(
            ExistingMollieSessionDecision::Resume,
            $this->resolver->resolve($session, $details, null),
        );
    }

    private function session(
        string $status,
        ?string $method = null,
        string $redirectUrl = 'https://shop.test/payment/capture/spent-token',
    ): Payment {
        $session = new Payment($this->createMock(\Mollie\Api\MollieApiClient::class));
        $session->id = 'tr_session';
        $session->status = $status;
        $session->method = $method;
        $session->redirectUrl = $redirectUrl;

        return $session;
    }

    private function token(): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getTargetUrl')->willReturn(self::TOKEN_URL);

        return $token;
    }
}
