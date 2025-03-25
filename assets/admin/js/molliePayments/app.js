$ = window.$;
$(function () {
    const mollieFormIncluded = document.getElementById('mollie-payment-form');
    const liveApiValue = '1';

    if (!mollieFormIncluded) {
        return;
    }

    $('#get_methods').on('click', function () {
        let form = $('.ui.form');
        form.addClass('loading');

        $.ajax({
            type: 'GET',
            url: $(this).data('url'),
            success: function (data) {
                location.reload();
            },
            error: function () {
                location.reload();
            },
        });
    });

    $('.ui.dropdown').dropdown();

    $('.form_button--delete-img').each(function (index, value) {
        $(this).on('click', function () {
            let form = $('.ui.form');
            let value = $(this).data('value');
            form.addClass('loading');

            $.ajax({
                data: {method: value},
                type: 'DELETE',
                url: $(this).data('url'),
                success: function (data) {
                    location.reload();
                },
                error: function () {
                    form.removeClass('loading');
                },
            });
        });
    });

    $('[id$="_paymentType"]').each(function (index) {
        setPaymentDescription($(this), index);

        $(this).on('change', function (event) {
            setPaymentDescription($(event.target), index);
        });
    });

    function setPaymentDescription(select) {
        const $targetMethod = select.closest('.js-draggable');
        const $inputOrderNumber = $targetMethod.find('[id$="_paymentDescription"]');
        const $descriptionOrderNumber = $targetMethod.find('[id^="payment_description_"]');

        if (select.find(':selected').val() === 'PAYMENT_API') {
            $inputOrderNumber.show();
            $descriptionOrderNumber.show();
        } else {
            $inputOrderNumber.hide();
            $descriptionOrderNumber.hide();
        }
    }

    $('[id$="_paymentSurchargeFee_type"]').each(function () {
        const value = $(this).val();
        const index = $(this).closest('[data-method-id]').data('method-id');

        setPaymentFeeFields(value, index);

        $(this).on('change', () => {
            const value = $(this).val();
            const index = $('.content.active[data-method-id]').data('method-id');
            setPaymentFeeFields(value, index);
        });
    });

    function setPaymentFeeFields(value, index) {
        const fixedAmount =
            'sylius_payment_method_gatewayConfig_mollieGatewayConfig_' + index + '_paymentSurchargeFee_fixedAmount';
        const percentage =
            'sylius_payment_method_gatewayConfig_mollieGatewayConfig_' + index + '_paymentSurchargeFee_percentage';
        const surchargeLimit =
            'sylius_payment_method_gatewayConfig_mollieGatewayConfig_' + index + '_paymentSurchargeFee_surchargeLimit';
        const fixedAmountItems = $('label[for=' + fixedAmount + '], input#' + fixedAmount + '');
        const percentageItems = $('label[for=' + percentage + '], input#' + percentage + '');
        const surchargeLimitItems = $('label[for=' + surchargeLimit + '], input#' + surchargeLimit + '');

        switch (value) {
            case 'no_fee':
                fixedAmountItems.hide();
                percentageItems.hide();
                surchargeLimitItems.hide();
                break;
            case 'percentage':
                fixedAmountItems.hide();
                percentageItems.show();
                surchargeLimitItems.show();
                break;
            case 'fixed_fee':
                fixedAmountItems.show();
                percentageItems.hide();
                surchargeLimitItems.hide();
                break;
            case 'fixed_fee_and_percentage':
                fixedAmountItems.show();
                percentageItems.show();
                surchargeLimitItems.show();
                break;
            default:
                break;
        }
    }

    $('[id$="_country_restriction"]').each(function (index) {
        const value = $(this).find(':selected').val();
        setCountryRestriction(value, index);

        $(this).on('change', function () {
            const value = $(this).val();
            setCountryRestriction(value, index);
        });
    });

    function setCountryRestriction(value, index) {
        const excludeCountries = $('#country-excluded_' + index);
        const allowCountries = $('#country-allowed_' + index);

        if (value === 'ALL_COUNTRIES') {
            excludeCountries.show();
            allowCountries.hide();
        }
        if (value === 'SELECTED_COUNTRIES') {
            excludeCountries.hide();
            allowCountries.show();
        }
    }

    const addRequired = (child) => {
        $(child).closest('.field').addClass('required');
    };

    const removeRequired = (child) => {
        $(child).closest('.field').removeClass('required');
    };

    const conditionalFieldHandler = (handledField, expectedValue, requiredField) => {
        if (handledField.val() === expectedValue) {
            addRequired(requiredField);
        } else if (handledField.val() !== expectedValue) {
            removeRequired(requiredField);
        }
    };

    const turnOnHandlers = () => {
        const environmentField = $('#sylius_payment_method_gatewayConfig_config_environment');
        const liveApiFieldIndicator = '#sylius_payment_method_gatewayConfig_config_api_key_live';

        if (environmentField) {
            conditionalFieldHandler(environmentField, liveApiValue, liveApiFieldIndicator);
            environmentField.on('change', () => {
                conditionalFieldHandler(environmentField, liveApiValue, liveApiFieldIndicator);
            });
        }
    };

    const hideProfileIdInput = () => {
        let labelFor = "sylius_payment_method_gatewayConfig_config_profile_id";
        let labelElement = document.querySelector(`label[for="${labelFor}"]`);

        if (labelElement) {
            let parentElement = labelElement.parentNode;

            if (parentElement) {
                parentElement.classList.remove('required');
            }
        }
    };

    turnOnHandlers();
    hideProfileIdInput();
});
