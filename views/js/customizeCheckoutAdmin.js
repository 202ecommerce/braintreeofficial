/**
 * 2007-2019 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 */



var CustomizeCheckout = {
    init: function() {
        $('input').change(function () {
            CustomizeCheckout.checkConfigurations()
        });
        this.checkConfigurations();
    },

    checkConfigurations: function () {
        var usePayPal = $('input[name="braintree_activate_paypal"]');
        var enable3DSecure = $('input[name="braintree_3DSecure"]');
        var enableCardVerification = $('input[name="braintree_card_verification"]');

        if (usePayPal.prop("checked") == false) {
            this.hideConfiguration('braintree_show_paypal_benefits');
        } else {
            this.showConfiguration('braintree_show_paypal_benefits');
        }

        if (enableCardVerification. prop("checked") == false) {
            this.hideConfiguration('braintree_3DSecure');
            this.hideConfiguration('braintree_3DSecure_amount');
        } else {
            this.showConfiguration('braintree_3DSecure');
        }

        if (enable3DSecure. prop("checked") == false) {
            this.hideConfiguration('braintree_3DSecure_amount');
        } else {
            this.showConfiguration('braintree_3DSecure_amount');
        }
    },

    hideConfiguration: function (name) {
        var selector = 'input[name="' + name + '"]';
        var configuration = $(selector);
        var formGroup = configuration.closest('.form-group');
        if (configuration.attr('type') == 'radio') {
            this.disableConfiguration(name)
        }
        formGroup.hide();

    },

    showConfiguration: function (name) {
        var selector = 'input[name="' + name + '"]';
        var configuration = $(selector);
        var formGroup = configuration.closest('.form-group');
        formGroup.show();
    },

    disableConfiguration: function (name) {
        $('input#' + name + '_on').prop("checked", false);
        $('input#' + name + '_off').prop("checked", true)
    }
}

document.addEventListener("DOMContentLoaded", function () {
    CustomizeCheckout.init();
});