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



var SetupAdmin = {
    init: function() {
        $('#confirmCredentials').click(function () {
            $('#configuration_form').submit();
        });

        $('#logoutAccount').click(function () {
            SetupAdmin.logoutAccount(this);
        });
    },

    logoutAccount: function (element) {
        var form = $(element).closest('form');
        form.find('div.current-account input').val('');
        form.submit();
    }
}

document.addEventListener("DOMContentLoaded", function () {
    SetupAdmin.init();
});