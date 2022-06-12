define([
    'jquery',
    'mage/url'
], function ($, urlBuilder) {

    return function ajaxlogin() {

        let loginOverlay = $('.login-overlay');

        let accountLinkLogin = $('a.auth-popup');

        let sectionNav = $('.action.nav-toggle');

        let loginBox = loginOverlay.find('.popup-login-form');
        let registerBox = loginOverlay.find('.popup-register-form');

        let loginButton = $(loginBox).find('button[type=submit]');
        let registerButton = $(registerBox).find('button[type=submit]');

        let loginUrl = urlBuilder.build('quickajaxlogin/ajax/login');
        let registerUrl = urlBuilder.build('quickajaxlogin/ajax/register');

        let loginText = 'Login';
        let loggingText = 'Please Wait...';
        let registerText = 'Sign Up';
        let registeringText = 'Please Wait...';

        $(document).on('click', '.authorization-link a', function (e) {
            e.preventDefault(e);
            sectionNav.click();
            loginBox.show();
            loginOverlay.show();
        });

        $(document).on('click', '.header.links li:last-child a', function (e) {
            e.preventDefault(e);
            sectionNav.click();
            loginOverlay.show();
            registerBox.show();
        });

        accountLinkLogin.click(function (e) {
            e.preventDefault(e);
            loginOverlay.show();
            loginBox.show();
            registerBox.hide();
        });

        $('.cancelbtn').click(function () {
            loginOverlay.hide();
            loginBox.hide();
            registerBox.hide();
        });

        $('.login-link a').click(function (e) {
            e.preventDefault(e);
            loginBox.show();
            registerBox.hide();
        });

        $('.register-link a').click(function (e) {
            e.preventDefault(e);
            loginBox.hide();
            registerBox.show();
        });

        $('.popup-login-form').submit(function () {
            if ($(".popup-login-form").valid()) {
                loginButton.text(loggingText);
                loginButton.attr("disabled", "disabled");
                let formData = getFormData($(this));
                $.ajax({
                    type: "POST",
                    url: loginUrl,
                    data: formData,
                    dataType: "json",
                    success: function (data) {
                        showResponse(data);
                        if (data.errors) {
                            loginButton.text(loginText);
                            loginButton.removeAttr('disabled');
                        } else {
                            location.reload();
                        }
                    }
                });
                return false;
            }

        });

        $('.popup-register-form').submit(function () {
            if ($(".popup-register-form").valid()) {
                registerButton.text(registeringText);
                registerButton.attr("disabled", "disabled");
                let formData = getFormData($(this));
                $.ajax({
                    type: "POST",
                    url: registerUrl,
                    data: formData,
                    dataType: "json",
                    success: function (data) {
                        showResponse(data);
                        if (data.errors) {
                            registerButton.text(registerText);
                            registerButton.removeAttr('disabled');
                        } else {
                            location.reload();
                        }
                    }
                });
                return false;
            }
        });

        function getFormData(formElem) {
            let unindexed_array = formElem.serializeArray();
            let indexed_array = {};
            jQuery.map(unindexed_array, function (n, i) {
                indexed_array[n['name']] = n['value'];
            });
            return indexed_array;
        }

        function showResponse(data) {
            if (data.errors) {
                $('.response-msg').html("<div class='error'>" + data.message + "</div>");
            } else {
                $('.response-msg').html("<div class='success'>" + data.message + "</div>");
            }
            setTimeout(function () {
                $('.response-msg').html(null);
            }, 5000);
        }
    }
});
