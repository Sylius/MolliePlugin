import { request } from './applePayRequest.js'; // Import the request function

document.addEventListener('DOMContentLoaded', () => {
    const applePaySession = () => {
        const version = 3;

        const applePayButton = document.getElementById('mollie_applepay_button');
        if (!applePayButton) return;

        const divider = Number(applePayButton.getAttribute('data-divisor'));
        const mollieValidateMerchantUrl = applePayButton.getAttribute('data-url-validate');
        const molliePaymentUrl = applePayButton.getAttribute('data-url-payment');
        const mollieCurrency = applePayButton.getAttribute('data-currency-order');
        const mollieMerchantName = applePayButton.getAttribute('data-merchant-name');
        const mollieTotalOrderValue = Number(applePayButton.getAttribute('data-total-order'));

        const mollieTotalOrder = (mollieTotalOrderValue / divider).toFixed(2);

        const session = new ApplePaySession(
            version,
            request('US', mollieCurrency, mollieMerchantName, mollieTotalOrder)
        );

        session.onvalidatemerchant = async (event) => {
            try {
                const response = await fetch(mollieValidateMerchantUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        validationUrl: event.validationURL,
                    }),
                });

                const merchantSession = await response.json();

                if (merchantSession.success === true) {
                    session.completeMerchantValidation(JSON.parse(merchantSession.data));
                } else {
                    session.abort();
                }
            } catch (error) {
                session.abort();
            }
        };

        session.onpaymentauthorized = async (event) => {
            try {
                const response = await fetch(molliePaymentUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        token: event.payment.token,
                        shippingContact: event.payment.shippingContact,
                        billingContact: event.payment.billingContact,
                    }),
                });

                const authorization = await response.json();

                if (authorization.success === true) {
                    const result = authorization.data;
                    session.completePayment(result.responseToApple);
                    window.location.href = result.returnUrl;
                } else {
                    session.completePayment(authorization.data);
                }
            } catch (error) {
                session.abort();
            }
        };

        session.begin();
    };

    const applePayMethodElement = document.getElementById('mollie_applepay_button');

    const canShowButton =
        applePayMethodElement &&
        window.ApplePaySession &&
        ApplePaySession?.canMakePayments();

    if (canShowButton) {
        applePayMethodElement.style.display = 'block';
    }

    if (applePayMethodElement) {
        applePayMethodElement.addEventListener('click', () => {
            applePaySession();
        });
    }
});
