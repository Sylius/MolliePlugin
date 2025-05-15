document.addEventListener('DOMContentLoaded', function () {
    const testApiKeyButtons = document.getElementsByClassName('test-api-key-button');

    Array.from(testApiKeyButtons).forEach((button) => {
        button.addEventListener('click', function () {
            const testApiDataDiv = document.querySelector('.test-api-key-div');
            const testApiKey = document.getElementById('sylius_admin_payment_method_gatewayConfig_config_api_key_test');
            const liveApiKey = document.getElementById('sylius_admin_payment_method_gatewayConfig_config_api_key_live');

            button.classList.add('loading');
            button.disabled = true;

            const url = button.dataset.url;
            const params = new URLSearchParams({
                api_key_test: testApiKey?.value || '',
                api_key_live: liveApiKey?.value || ''
            });

            fetch(`${url}?${params.toString()}`, {
                method: 'GET'
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text(); // assuming HTML is returned
                })
                .then(data => {
                    if (testApiDataDiv) {
                        testApiDataDiv.classList.remove('message', 'red');
                        testApiDataDiv.innerHTML = data;
                    }

                    Array.from(testApiKeyButtons).forEach(btn => {
                        btn.classList.remove('loading');
                        btn.disabled = false;
                    });
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    // Optionally handle errors visually
                    Array.from(testApiKeyButtons).forEach(btn => {
                        btn.classList.remove('loading');
                        btn.disabled = false;
                    });
                });
        });
    });
});
