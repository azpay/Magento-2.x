define([
    'jquery'
], function ($) {
    "use strict";

    return function () {
        $.validator.addMethod(
            'validate-minimum-age',
            function (value) {
                // Some custom validation stuff here
                return false;
            },
            $.mage.__('Your validation error message')
        );
    }
});