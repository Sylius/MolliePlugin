document.addEventListener('DOMContentLoaded', function () {
    const testApiKeyButton = document.getElementById('api_key_test');
    const liveApiKeyButton = document.getElementById('api_key_live');

    if (testApiKeyButton) {
        testApiKeyButton.addEventListener('click', function () {
            toggleVisibility(this);
        });
    }

    if (liveApiKeyButton) {
        liveApiKeyButton.addEventListener('click', function () {
            toggleVisibility(this);
        });
    }

    function toggleVisibility(button) {
        const keyInput = document.getElementById(button.dataset.input);
        if (keyInput) {
            keyInput.type = keyInput.type === 'password' ? 'text' : 'password';
        }
    }
});
