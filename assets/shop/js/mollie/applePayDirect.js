import { request } from './applePayRequest.js'; // Import the request function

$(function () {
    let applePaySession = () => {
        const version = 3;

        const applePayButton = document.getElementById(
            'mollie_applepay_button'
        );

        const divider =
            Number(applePayButton.getAttribute('data-divisor'));
        const mollieValidateMerchantUrl =
            applePayButton.getAttribute('data-url-validate');
        const molliePaymentUrl =
            applePayButton.getAttribute('data-url-payment');
        const mollieCurrency = applePayButton.getAttribute(
            'data-currency-order'
        );
        const mollieMerchantName =
            applePayButton.getAttribute('data-merchant-name');
        const mollieTotalOrderValue =
            Number(applePayButton.getAttribute('data-total-order'));

        let mollieTotalOrder = (mollieTotalOrderValue/divider).toFixed(2);

        const session = new ApplePaySession(
            version,
            request(
                'US',
                mollieCurrency,
                mollieMerchantName,
                mollieTotalOrder
            )
        );

        session.onvalidatemerchant = (applePayValidateMerchantEvent) => {
            jQuery.ajax({
                url: mollieValidateMerchantUrl,
                method: 'POST',
                data: {
                    validationUrl: applePayValidateMerchantEvent.validationURL,
                },
                success: (merchantSession) => {
                    if (merchantSession.success === true) {
                        session.completeMerchantValidation(
                            JSON.parse(merchantSession.data)
                        );
                    } else {
                        session.abort();
                    }
                },
                error: (XHR, status, error) => {
                    session.abort();
                },
            });
        };

        session.onpaymentauthorized = (ApplePayPayment) => {
            jQuery.ajax({
                url: molliePaymentUrl,
                method: 'POST',
                data: {
                    token: ApplePayPayment.payment.token,
                    shippingContact: ApplePayPayment.payment.shippingContact,
                    billingContact: ApplePayPayment.payment.billingContact,
                },
                success: (authorization) => {
                    let result = authorization.data;

                    if (authorization.success === true) {
                        redirectionUrl = result['returnUrl'];
                        session.completePayment(result['responseToApple']);
                        window.location.href = redirectionUrl;
                    } else {
                        session.completePayment(result);
                    }
                },
                error: (XHR, status, error) => {
                    session.abort();
                },
            });
        };

        session.begin();
    };

    const applePayMethodElement = document.querySelector(
        '#mollie_applepay_button'
    );

    const canShowButton =
        applePayMethodElement &&
        window.ApplePaySession &&
        ApplePaySession?.canMakePayments();
    if (canShowButton) {
        applePayMethodElement.style.display = 'block';
    }
    if (applePayMethodElement) {
        applePayMethodElement.addEventListener('click', (evt) => {
            applePaySession();
        });
    }
});
