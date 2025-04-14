const {Mollie} = window;

$(function () {
    var disableValidationMollieComponents = false;
    let selectedValue = false;
    let mollieData = $('.online-online-payment__container');
    let orderId = null;
    let qrCodeInterval = null;
    const initialOrderTotal = $('#sylius-summary-grand-total').text();
    const cardActiveClass = 'online-payment__item--active';
    const orderTotalRow = $('#sylius-summary-grand-total');
    const components = Boolean(mollieData.data('components'));
    let creditCardTranslations = {};

    if (mollieData && mollieData[0]) {
        let fetchTranslationsUrl = mollieData[0].getAttribute('data-fetchTranslations');
        fetchTranslations(fetchTranslationsUrl);
    }

    $('input[id*="sylius_checkout_select_payment_"][type=radio]').on('change', ({currentTarget}) => {
        if (!currentTarget.classList.contains('mollie-payments')) {
            let paymentMethodsContainer = document.getElementsByClassName('online-online-payment__container');
            if (mollieData && mollieData[0]) {
                let removeQrCodeUrl = mollieData[0].getAttribute('data-removeQrCode');
                removeQrCode(removeQrCodeUrl);
            }
            restoreOrderTotalValue();
            $(`.${cardActiveClass} input[type="radio"]`).prop('checked', false);
            $(`.${cardActiveClass}`).removeClass(cardActiveClass);
        }
    });

    $('.online-payment__input').on('change', ({currentTarget}) => {
        let currentItem = $(currentTarget).parent('.online-payment__item');
        currentItem.siblings().removeClass('online-payment__item--active');
        currentItem.addClass('online-payment__item--active');
        selectedValue = currentTarget.value;

        if (!$('.mollie-payments').prop('checked')) {
            $('.mollie-payments').prop('checked', true);
        }

        if (currentItem.data('feeurl')) {
            getPaymentFee(currentItem.data('feeurl'));
        }
    });

    function getPaymentFee(url) {
        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                const paymentFeeRow = $('#mollie-paymentFee-row');

                if (paymentFeeRow.length && data.view) {
                    paymentFeeRow.replaceWith(data.view);
                    orderTotalRow.text(data.orderTotal);
                } else if (data.view) {
                    $('#sylius-checkout-subtotal .ui.large.header').before(data.view);
                    orderTotalRow.text(data.orderTotal);
                } else {
                    restoreOrderTotalValue();
                }
            });
    }

    function restoreOrderTotalValue() {
        $('#mollie-paymentFee-row').replaceWith('');
        orderTotalRow.text(initialOrderTotal);
    }

    if (mollieData.length > 0 && true === components) {
        let paymentMethods = document.querySelectorAll('div[class*="online-payment__item--"]');

        for (let i = 0; i < paymentMethods.length; i++) {
            paymentMethods[i].onchange = function (event) {
                let target = event.target;
                let creditCartComponents = document.querySelectorAll('div[data-testid*="mollie-container--"]');

                if (target.value === 'creditcard' && creditCartComponents.length === 0) {
                    toggleMollieComponents();
                    initializeCreditCartFields(selectedValue);

                    if (isSavedCreditCardCheckboxChecked()) {
                        const mollieComponentFields = document.querySelector('.mollie-component-fields');
                        if (!mollieComponentFields) {
                            return;
                        }

                        hideMollieComponents(mollieComponentFields);
                    }
                }

                if (isOrderSummaryPage()) {
                    let removeQrCodeUrl = mollieData[0].getAttribute('data-removeQrCode');
                    removeQrCode(removeQrCodeUrl);

                    return;
                }

                if (target && (target.value === 'bancontact' || target.value === 'ideal')) {
                    createMolliePayment(target.getAttribute('data-qrcode'), target.value);
                } else {
                    let removeQrCodeUrl = mollieData[0].getAttribute('data-removeQrCode');
                    removeQrCode(removeQrCodeUrl);
                }
            }
        }
    }

    function fetchIssuer(target) {
        let parentDiv = target.parentNode;
        let img = parentDiv.querySelector('.online-payment__image');
        if (img) {
            let urlParts = img.src.split('/');
            let filename = urlParts[urlParts.length - 1];
            let issuer = filename.replace('.svg', '');

            return 'ideal_' + issuer;
        }

        return null;
    }

    function isSavedCreditCardCheckboxChecked() {
        let checkbox = document.getElementById('mollie-sylius-use-saved-credit-card');
        if (!checkbox) {
            return null;
        }
        let parentElement = checkbox.parentNode;

        return parentElement.classList.contains('checked') ? 1 : 0;
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
                    let cartVariantDetails = document.getElementById('cart-variant-details')
                    if (cartVariantDetails) {
                        let thankYouPageUrl = cartVariantDetails.getAttribute('data-thankYouPage');
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
        let cartVariantDetails = document.getElementById('cart-variant-details');

        if (cartVariantDetails) {
            let qrCodeGetUrl = cartVariantDetails.getAttribute('data-getQrCode');
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
        let parentElement = checkbox.parentNode;

        return parentElement.classList.contains('checked') ? 1 : 0;
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
        const environment = mollieData.data('environment');
        let testmode = true;

        if (environment === 1) {
            testmode = false;
        }

        const mollie = Mollie(mollieData.data('profile_id'), {
            locale: mollieData.data('locale'),
            testmode: testmode,
        });

        const form = document.getElementsByName('sylius_checkout_select_payment')[0];

        const formError = document.getElementById('form-error');
        const submitButton = document.getElementById('next-step') || document.getElementById('sylius-pay-link');
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
            useSavedCardsInput.value = isSavedCreditCardCheckboxChecked();

            if ($('.online-payment__input:checked').val() === 'creditcard' && disableValidationMollieComponents === false) {
                event.preventDefault();
                disableForm();

                formError.textContent = '';

                const {token, error} = await mollie.createToken();

                if (error) {
                    enableForm();
                    formError.textContent = error.message;
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
