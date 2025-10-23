document.addEventListener('DOMContentLoaded', function () {
    const mollieFormIncluded = document.getElementById('mollie-payment-form');
    const liveApiValue = '1';

    if (!mollieFormIncluded) {
        return;
    }

    const getMethodsButton = document.getElementById('get_methods');
    if (getMethodsButton) {
        getMethodsButton.addEventListener('click', function () {
            const form = document.querySelector('form[name="sylius_admin_payment_method"]');
            if (form) {
                form.classList.add('loading');
            }

            fetch(this.dataset.url, {
                method: 'GET',
            })
                .then(() => location.reload())
                .catch(() => location.reload());
        });
    }

    document.querySelectorAll('.form_button--delete-img').forEach((btn) => {
        btn.addEventListener('click', function () {
            fetch(this.dataset.url, {
                method: 'DELETE',
            })
                .then(() => location.reload())
                .catch();
        });
    });

    document.querySelectorAll('[id$="_paymentType"]').forEach((select, index) => {
        setPaymentDescription(select);

        select.addEventListener('change', function (event) {
            setPaymentDescription(event.target);
        });
    });

    function setPaymentDescription(select) {
        const targetMethod = select.closest('.js-draggable');
        if (!targetMethod) return;

        const paymentDescription = targetMethod.querySelector('.js-onboardingWizard-order-number');
        if (!paymentDescription) return;

        if (select.value === 'PAYMENT_API') {
            paymentDescription.style.display = '';
        } else {
            paymentDescription.style.display = 'none';
        }
    }

    document.querySelectorAll('[id$="_paymentSurchargeFee_type"]').forEach((element) => {
        const value = element.value;
        const index = element.closest('[data-method-id]').dataset.methodId;

        setPaymentFeeFields(value, index);

        element.addEventListener('change', function () {
            const value = this.value;
            const index = this.closest('[data-method-id]').dataset.methodId;
            setPaymentFeeFields(value, index);
        });
    });

    function setPaymentFeeFields(value, index) {
        const fixedAmount = document.getElementById(`sylius_admin_payment_method_gatewayConfig_mollieGatewayConfig_${index}_paymentSurchargeFee_fixedAmount`);
        const percentage = document.getElementById(`sylius_admin_payment_method_gatewayConfig_mollieGatewayConfig_${index}_paymentSurchargeFee_percentage`);
        const surchargeLimit = document.getElementById(`sylius_admin_payment_method_gatewayConfig_mollieGatewayConfig_${index}_paymentSurchargeFee_surchargeLimit`);

        const fixedAmountItems = fixedAmount?.parentElement;
        const percentageItems = percentage?.parentElement;
        const surchargeLimitItems = surchargeLimit?.parentElement;

        if (!fixedAmountItems || !percentageItems || !surchargeLimitItems) return;

        switch (value) {
            case 'no_fee':
                fixedAmountItems.style.display = 'none';
                percentageItems.style.display = 'none';
                surchargeLimitItems.style.display = 'none';
                break;
            case 'percentage':
                fixedAmountItems.style.display = 'none';
                percentageItems.style.display = '';
                surchargeLimitItems.style.display = '';
                break;
            case 'fixed_fee':
                fixedAmountItems.style.display = '';
                percentageItems.style.display = 'none';
                surchargeLimitItems.style.display = 'none';
                break;
            case 'fixed_fee_and_percentage':
                fixedAmountItems.style.display = '';
                percentageItems.style.display = '';
                surchargeLimitItems.style.display = '';
                break;
            default:
                break;
        }
    }

    document.querySelectorAll('[id$="_country_restriction"]').forEach((select, index) => {
        setCountryRestriction(select.value, index);

        select.addEventListener('change', function () {
            setCountryRestriction(this.value, index);
        });
    });

    function setCountryRestriction(value, index) {
        const excludeCountries = document.getElementById('country-excluded_' + index);
        const allowCountries = document.getElementById('country-allowed_' + index);

        if (!excludeCountries || !allowCountries) return;

        if (value === 'ALL_COUNTRIES') {
            excludeCountries.style.display = '';
            allowCountries.style.display = 'none';
        } else if (value === 'SELECTED_COUNTRIES') {
            excludeCountries.style.display = 'none';
            allowCountries.style.display = '';
        }
    }

    const addRequired = (selector) => {
        const field = document.querySelector(selector);
        if (field) {
            const parent = field.closest('.field');
            if (parent) parent.classList.add('required');
        }
    };

    const removeRequired = (selector) => {
        const field = document.querySelector(selector);
        if (field) {
            const parent = field.closest('.field');
            if (parent) parent.classList.remove('required');
        }
    };

    const conditionalFieldHandler = (field, expectedValue, requiredFieldSelector) => {
        if (field.value === expectedValue) {
            addRequired(requiredFieldSelector);
        } else {
            removeRequired(requiredFieldSelector);
        }
    };

    const turnOnHandlers = () => {
        const environmentField = document.getElementById('sylius_admin_payment_method_gatewayConfig_config_environment');
        const liveApiFieldSelector = '#sylius_admin_payment_method_gatewayConfig_config_api_key_live';

        if (environmentField) {
            conditionalFieldHandler(environmentField, liveApiValue, liveApiFieldSelector);
            environmentField.addEventListener('change', function () {
                conditionalFieldHandler(environmentField, liveApiValue, liveApiFieldSelector);
            });
        }
    };

    turnOnHandlers();
});
