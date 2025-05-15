import $ from 'jquery';

$(function () {
    const testApiKeyButton = document.getElementById('api_key_test');
    const liveApiKeyButton = document.getElementById('api_key_live');

    $(testApiKeyButton).on('click', function (event) {
        toggleVisibility(this);
    });

    $(liveApiKeyButton).on('click', function (event) {
        toggleVisibility(this);
    });

    function toggleVisibility(button) {
        const keyInput = document.getElementById(button.dataset.input);

        keyInput.type = keyInput.type === 'password' ? 'text' : 'password';
    }
});
