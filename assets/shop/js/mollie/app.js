const {Mollie} = window;

document.addEventListener('DOMContentLoaded', function () {
    const mollieData = document.querySelector('.online-online-payment__container');
    if (!mollieData) {
        return;
    }

    let disableValidationMollieComponents = false;
    let selectedValue = false;
    let orderId = null;
    let qrCodeInterval = null;
    const orderTotalRow = document.getElementById('sylius-shop-checkout-summary-order-total');
    const initialOrderTotal = orderTotalRow ? orderTotalRow.textContent : null;
    const cardActiveClass = 'online-payment__item--active';
    const components = mollieData ? Boolean(mollieData.getAttribute('data-components')) : false;
    let creditCardTranslations = {};

    if (mollieData) {
        let fetchTranslationsUrl = mollieData.getAttribute('data-fetchTranslations');
        fetchTranslations(fetchTranslationsUrl);
    }

    document.querySelectorAll('input[id*="sylius_shop_checkout_select_payment_"][type=radio], input[id*="sylius_checkout_select_payment_"][type=radio]').forEach(input => {
        input.addEventListener('change', (event) => {
            if (!input.classList.contains('mollie-payments')) {
                if (mollieData) {
                    let removeQrCodeUrl = mollieData.getAttribute('data-removeQrCode');
                    removeQrCode(removeQrCodeUrl);
                }
                restoreOrderTotalValue();

                document.querySelectorAll(`.${cardActiveClass} input[type="radio"]`).forEach(function(radio) {
                    radio.checked = false;
                });

                document.querySelectorAll(`.${cardActiveClass}`).forEach(function(elem) {
                    elem.classList.remove(cardActiveClass);
                });
            }
        });
    });

    document.querySelectorAll('.online-payment__input').forEach(input => {
        input.addEventListener('change', (event) => {
            const currentTarget = event.currentTarget;
            const currentItem = currentTarget.closest('.online-payment__item');

            const siblings = currentItem.parentElement.querySelectorAll('.online-payment__item');
            siblings.forEach(sibling => {
                sibling.classList.remove('online-payment__item--active');
            });

            currentItem.classList.add('online-payment__item--active');

            selectedValue = currentTarget.value;

            const molliePayments = document.querySelector('.mollie-payments');
            if (molliePayments && !molliePayments.checked) {
                molliePayments.checked = true;
            }

            const feeUrl = currentItem.getAttribute('data-feeurl');
            if (feeUrl) {
                getPaymentFee(feeUrl);
            }
        });
    });

    function getPaymentFee(url) {
        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                const orderTotalRow = document.getElementById('sylius-shop-checkout-summary-order-total');
                const orderTotalTr = orderTotalRow ? orderTotalRow.closest('tr') : null;

                const existingPaymentFeeRow = document.getElementById('mollie-paymentFee-row');
                if (existingPaymentFeeRow) {
                    existingPaymentFeeRow.remove();
                }

                if (data.view && orderTotalTr) {
                    orderTotalTr.insertAdjacentHTML('beforebegin', data.view);
                }

                if (data.orderTotal && orderTotalRow) {
                    orderTotalRow.textContent = data.orderTotal;
                }
            })
            .catch(() => {
                restoreOrderTotalValue();
            });
    }

    function restoreOrderTotalValue() {
        const paymentFeeRow = document.getElementById('mollie-paymentFee-row');
        if (paymentFeeRow) {
            paymentFeeRow.remove();
        }
        if (orderTotalRow) {
            fetch(mollieData.getAttribute('data-no-fee-url'))
                .then((response) => response.json())
                .then((data) => {
                    if (data.orderTotal) {
                        orderTotalRow.textContent = data.orderTotal;
                    }
                })
                .catch(() => {
                    orderTotalRow.textContent = initialOrderTotal;
                });
        }
    }

    if (mollieData && components === true) {
        const paymentMethods = document.querySelectorAll('div[class*="online-payment__item--"]');

        paymentMethods.forEach(method => {
            method.addEventListener('change', function (event) {
                const target = event.target;
                const creditCardComponents = document.querySelectorAll('div[data-testid*="mollie-container--"]');

                if (target.value === 'creditcard' && creditCardComponents.length === 0) {
                    toggleMollieComponents();
                    initializeCreditCartFields(selectedValue);

                    if (isSavedCreditCardCheckboxChecked()) {
                        const mollieComponentFields = document.querySelector('.mollie-component-fields');
                        if (!mollieComponentFields) return;
                        hideMollieComponents(mollieComponentFields);
                    }
                }

                if (isOrderSummaryPage()) {
                    const removeQrCodeUrl = mollieData.getAttribute('data-removeQrCode');
                    removeQrCode(removeQrCodeUrl);
                    return;
                }

                if (target && (target.value === 'bancontact' || target.value === 'ideal')) {
                    createMolliePayment(target.getAttribute('data-qrcode'), target.value);
                } else {
                    const removeQrCodeUrl = mollieData.getAttribute('data-removeQrCode');
                    removeQrCode(removeQrCodeUrl);
                }
            });
        });
    }

    function isSavedCreditCardCheckboxChecked() {
        let checkbox = document.getElementById('mollie-sylius-use-saved-credit-card');
        if (!checkbox) {
            return null;
        }
        return checkbox.checked ? 1 : 0;
    }

    function createMolliePayment(url, paymentMethod, issuer = null) {
        url = url + '?paymentMethod=' + paymentMethod;
        if (issuer) {
            url = url + '&issuer=' + issuer;
        }

        fetch(url)
            .then((response) => response.json())
            .then((data) => {
            });
    }

    function fetchQrCode(url) {
        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                let qrCode = data.qrCode;
                if (orderId === null) {
                    orderId = data.orderId;
                }

                if (qrCode) {
                    let qrCodeMethod = 'Bancontact';
                    if (qrCode.indexOf('ideal') !== -1) {
                        qrCodeMethod = 'iDeal';
                    }
                    createPopup(qrCode, qrCodeMethod);
                    qrCodeInterval = setInterval(() => checkQrCode(url + '?orderId=' + orderId), 1000);
                }
            });
    }

    function checkQrCode(url) {
        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                let qrCode = data.qrCode;
                if (!qrCode) {
                    let qrBoxElement = document.getElementById('sylius-shop-checkout-summary-qr-box')
                    if (qrBoxElement) {
                        let thankYouPageUrl = qrBoxElement.getAttribute('data-thankYouPage');
                        window.location.href = thankYouPageUrl + '?orderId=' + orderId;
                    }
                }
            });
    }

    function removeQrCode(url, shouldDeletePaymentId = true) {
        url = url + '?orderToken=' + extractOrderToken();
        url = shouldDeletePaymentId ? url + '&shouldDeletePaymentId=true' : url;

        fetch(url)
            .then((response) => response.json())
            .then((data) => {
            });
    }

    function isOrderSummaryPage() {
        let currentURL = window.location.href;
        let parts = currentURL.split('/');

        return !!(parts[parts.length - 2] && parts[parts.length - 2] === 'order');
    }

    function extractOrderToken() {
        let currentURL = window.location.href;
        let parts = currentURL.split('/');
        let orderToken = '';

        if (parts[parts.length - 2] && parts[parts.length - 2] === 'order') {
            orderToken = parts[parts.length - 1];
        }

        return orderToken;
    }

    function showQrCodePopUp() {
        let qrBoxElement = document.getElementById('sylius-shop-checkout-summary-qr-box');

        if (qrBoxElement) {
            let qrCodeGetUrl = qrBoxElement.getAttribute('data-getQrCode');
            fetchQrCode(qrCodeGetUrl);
        }
    }

    showQrCodePopUp();

    function createPopup(qrCode, qrCodeMethod) {
        // Create popup container
        var popupContainer = document.createElement('div');
        popupContainer.id = 'popup-container';
        popupContainer.classList.add('popup-container');

        // Create popup
        var popup = document.createElement('div');
        popup.classList.add('popup');

        // Create popup header
        var popupHeader = document.createElement('div');
        popupHeader.classList.add('popup-header');
        var title = document.createElement('h2');
        title.textContent = 'Scan QR';
        var hr = document.createElement('hr');
        popupHeader.appendChild(title);
        popupHeader.appendChild(hr);

        // Create popup content
        var popupContent = document.createElement('div');
        popupContent.classList.add('popup-content');
        var paragraph = document.createElement('p');
        paragraph.textContent = 'Open your ' + qrCodeMethod + ' app to scan the QR code.';
        var qrCodeImg = document.createElement('img');
        qrCodeImg.src = qrCode;
        qrCodeImg.width = 180;
        qrCodeImg.height = 180;
        popupContent.appendChild(paragraph);
        popupContent.appendChild(qrCodeImg);
        popupContent.insertAdjacentHTML('beforeend', '<p>Or</p>');

        // Create popup buttons
        var popupButtons = document.createElement('div');
        popupButtons.classList.add('popup-buttons');
        var continueButton = document.createElement('button');
        continueButton.textContent = 'CONTINUE WITHOUT QR CODE';
        continueButton.id = 'continue-button';
        var cancelButton = document.createElement('button');
        cancelButton.textContent = 'CANCEL';
        cancelButton.id = 'cancel-button';
        cancelButton.style.border = 'none'; // Remove button border
        popupButtons.appendChild(continueButton);
        popupButtons.appendChild(cancelButton);

        // Append elements to popup
        popup.appendChild(popupHeader);
        popup.appendChild(popupContent);
        popup.appendChild(popupButtons);

        // Append popup to container
        popupContainer.appendChild(popup);

        // Add event listeners to buttons
        cancelButton.addEventListener('click', function (event) {
            closePopup();
            window.location.href = 'select-payment';
        });

        continueButton.addEventListener('click', function (event) {
            closePopup();
            clearInterval(qrCodeInterval);
        });

        // Append popup container to body
        document.body.appendChild(popupContainer);
    }

    // Function to close the popup
    function closePopup() {
        var popupContainer = document.getElementById('popup-container');
        if (popupContainer) {
            popupContainer.parentNode.removeChild(popupContainer);
        }
    }

    function isSaveCreditCardForFutureUseChecked() {
        let checkbox = document.getElementById('mollie-sylius-save-credit-card');
        if (!checkbox) {
            return null;
        }

        return checkbox.checked ? 1 : 0;
    }

    function toggleMollieComponents() {
        const useSavedCreditCardCheckbox = document.getElementById('mollie-sylius-use-saved-credit-card');
        if (!useSavedCreditCardCheckbox) {
            return;
        }

        useSavedCreditCardCheckbox.addEventListener('change', function (event) {
            const mollieComponentFields = document.querySelector('.mollie-component-fields');
            if (!mollieComponentFields) {
                return;
            }

            if (event.target.checked) {
                hideMollieComponents(mollieComponentFields);
            } else {
                showMollieComponents(mollieComponentFields);
            }
        });
    }

    function showMollieComponents(mollieComponentFields) {
        disableValidationMollieComponents = false;
        mollieComponentFields.classList.remove('mollie-hidden');
        mollieComponentFields.classList.add('display-grid');
    }

    function hideMollieComponents(mollieComponentFields) {
        disableValidationMollieComponents = true;
        mollieComponentFields.classList.add('mollie-hidden');
        mollieComponentFields.classList.remove('display-grid');
    }

    function fetchTranslations(url) {
        fetch(url).then(response => response.json()).then(data => {
            creditCardTranslations = data.translations;
        });
    }

    function initializeCreditCartFields(selectedValue) {
        const environment = mollieData.getAttribute('data-environment');
        let testmode = true;

        if (environment === 1) {
            testmode = false;
        }

        const mollie = Mollie(mollieData.getAttribute('data-profile_id'), {
            locale: mollieData.getAttribute('data-locale'),
            testmode: testmode,
        });

        let form = document.getElementsByName('sylius_shop_checkout_select_payment')[0];
        if (!form) {
            form = document.getElementsByName('sylius_checkout_select_payment')[0];
        }

        const formError = document.getElementById('form-error');
        const submitButton = form.querySelector('button[type="submit"]');
        const tokenField = document.querySelector('[id*="_details_cartToken"]');
        const saveCardInfoInput = document.querySelector('[id*="_details_saveCardInfo"]');
        const useSavedCardsInput = document.querySelector('[id*="_details_useSavedCards"]');
        const cardHolder = mollie.createComponent('cardHolder');

        cardHolder.mount('#card-holder');

        const cardHolderError = document.getElementById('card-holder-error');
        cardHolder.addEventListener('change', (event) => {
            if (event.error && event.touched) {
                cardHolderError.textContent = creditCardTranslations.emptyCardHolder ? creditCardTranslations.emptyCardHolder : event.error;
            } else {
                cardHolderError.textContent = '';
            }
        });

        const cardNumber = mollie.createComponent('cardNumber');
        cardNumber.mount('#card-number');

        const cardNumberError = document.getElementById('card-number-error');

        cardNumber.addEventListener('change', (event) => {
            if (event.error && event.touched) {
                cardNumberError.textContent = creditCardTranslations.emptyCardNumber ? creditCardTranslations.emptyCardNumber : event.error;
            } else {
                cardNumberError.textContent = '';
            }
        });

        const expiryDate = mollie.createComponent('expiryDate');
        expiryDate.mount('#expiry-date');

        const expiryDateError = document.getElementById('expiry-date-error');

        expiryDate.addEventListener('change', (event) => {
            if (event.error && event.touched) {
                expiryDateError.textContent = creditCardTranslations.emptyExpiryDate ? creditCardTranslations.emptyExpiryDate : event.error;
            } else {
                expiryDateError.textContent = '';
            }
        });

        const verificationCode = mollie.createComponent('verificationCode');
        verificationCode.mount('#verification-code');

        const verificationCodeError = document.getElementById('verification-code-error');

        verificationCode.addEventListener('change', (event) => {
            if (event.error && event.touched) {
                verificationCodeError.textContent = creditCardTranslations.emptyVerificationCode ? creditCardTranslations.emptyVerificationCode : event.error;
            } else {
                verificationCodeError.textContent = '';
            }
        });

        function disableForm() {
            submitButton.disabled = true;
        }

        function enableForm() {
            submitButton.disabled = false;
        }

        form.addEventListener('submit', async (event) => {
            const selectedPaymentInput = document.querySelector('.online-payment__input:checked');
            useSavedCardsInput.value = isSavedCreditCardCheckboxChecked();

            if (selectedPaymentInput && selectedPaymentInput.value === 'creditcard' && disableValidationMollieComponents === false) {
                event.preventDefault();
                disableForm();

                formError.textContent = '';

                const { token, error } = await mollie.createToken();

                if (error) {
                    enableForm();
                    formError.textContent = creditCardTranslations.oneOrMoreInvalidFields;
                    form.classList.remove('loading');
                    return;
                }

                tokenField.value = token;
                saveCardInfoInput.value = isSaveCreditCardForFutureUseChecked();

                form.submit();
            }
        });
    }

    const applePay = document.getElementById('applepay');

    if (applePay) {
        if (window.ApplePaySession && (ApplePaySession && ApplePaySession.canMakePayments())) {
            applePay.style.display = 'block';
        }
    }
});
