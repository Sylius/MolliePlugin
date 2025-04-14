$ = window.$;
$(function () {
    const testApiKeyButton = document.getElementsByClassName(' test-api-key-button');

    $(testApiKeyButton).on('click', function (event) {
        const testApiDataDiv = document.getElementsByClassName('test-api-key-div');
        const testApiKey = document.getElementById('sylius_payment_method_gatewayConfig_config_api_key_test');
        const liveApiKey = document.getElementById('sylius_payment_method_gatewayConfig_config_api_key_live');

        $(this).addClass('loading');
        $(this).attr('disabled', true);

        $.ajax({
            type: 'GET',
            url: $(this).data('url'),
            data: {
                api_key_test: $(testApiKey).val(),
                api_key_live: $(liveApiKey).val(),
            },
            success: function (data) {
                $(testApiDataDiv).removeClass('message red');

                $(testApiKeyButton).removeClass('loading');
                $(testApiKeyButton).removeAttr('disabled');
                $(testApiDataDiv).html(data);
            },
            error: function (error) {},
        });
    });
});
