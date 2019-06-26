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
  init() {
    $('[data-confirm-credentials]').click(() => {
      $('#bt_config_account').submit();
    });

    $('[data-bt-logout]').click((e) => {
      SetupAdmin.logoutAccount(e.target);
    });

    $(document).on('click', '#btn-check-requirements', () => {
      SetupAdmin.checkRequirements();
    });

    $('[data-bt-link-settings]').on('click', (e) => {
      hoverConfig($(e.target.attributes.href.value));
    });

    $('.defaultForm').on('mouseleave', (e) => {
      $(e.currentTarget).removeClass('bt-settings-link-on');
    });
  },

  logoutAccount(element) {
    const form = $(element).closest('form');
    form.find('div.current-account input').val('');
    form.submit();
  },

  checkRequirements() {
    $.ajax({
      url: controllerUrl,
      type: 'POST',
      data: {
        ajax: true,
        action: 'CheckCredentials',
      },
      success(response) {
        $('#status-block').html(response);
      },
    });
  },
};

$(document).ready(() => SetupAdmin.init());

const hoverConfig = (el) => {
  $('.defaultForm').removeClass('bt-settings-link-on');
  el.addClass('bt-settings-link-on');
  if (el.is('#subtab-AdminBraintreeCustomizeCheckout')) {
    el.addClass('bt__border-b-primary');
  } else {
    $('#subtab-AdminBraintreeCustomizeCheckout').removeClass('bt-settings-link-on bt__border-b-primary');
  }
  $('html, body').animate({
    scrollTop: el.offset().top - 200 + "px"
  }, 900);
}