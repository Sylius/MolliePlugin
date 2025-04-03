$ = window.$;
$(function () {
    const testApiKeyButton = document.getElementById('api_key_test');
    const liveApiKeyButton = document.getElementById('api_key_live');

    $(testApiKeyButton).on('click', function (event) {
        const testApiKeyInput = document.getElementById('sylius_payment_method_gatewayConfig_config_api_key_test');

        if (testApiKeyInput.type === 'password') {
            testApiKeyInput.type = 'text';

            return;
        }

        testApiKeyInput.type = 'password';
    });

    $(liveApiKeyButton).on('click', function (event) {
        const liveApiKeyInput = document.getElementById('sylius_payment_method_gatewayConfig_config_api_key_live');

        if (liveApiKeyInput.type === 'password') {
            liveApiKeyInput.type = 'text';

            return;
        }

        liveApiKeyInput.type = 'password';
    });
});
