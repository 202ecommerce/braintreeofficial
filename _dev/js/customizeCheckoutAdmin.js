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

import * as functions from './functions.js'; 

var CustomizeCheckout = {
  init() {
    $('input').change(() => {
      CustomizeCheckout.checkConfigurations();
    });
    this.checkConfigurations();

    $('[data-bt-link-settings]').on('click', (e) => {
      let el = $(e.target.attributes.href.value);
      if (el.length) {
        functions.hoverConfig(el);
      } else {
        functions.hoverTabConfig();
      }
    });

    $('.defaultForm').on('mouseleave', (e) => {
      $(e.currentTarget).removeClass('bt-settings-link-on');
    });
  },

  checkConfigurations() {
    const usePayPal = $('input[name="braintree_activate_paypal"]');
    const enable3DSecure = $('input[name="braintree_3DSecure"]');
    const enableCardVerification = $('input[name="braintree_card_verification"]');
    const enableVault = $('input[name="braintree_vaulting"]');
    const showPayPalBenefits = $('input[name="braintree_show_paypal_benefits"]');
    const Amount3DSecure = $('input[name="braintree_3DSecure_amount"]');

    if (usePayPal.prop('checked') == false) {
      this.hideConfiguration(showPayPalBenefits.attr('name'));
    } else {
      this.showConfiguration(showPayPalBenefits.attr('name'));
    }

    if (enableVault.prop('checked') == false) {
      this.hideConfiguration(enableCardVerification.attr('name'));
    } else {
        this.showConfiguration(enableCardVerification.attr('name'));
    }

    if (enable3DSecure.prop('checked') == false) {
      this.hideConfiguration(Amount3DSecure.attr('name'));
    } else {
      this.showConfiguration(Amount3DSecure.attr('name'));
    }
  },

  hideConfiguration(name) {
    const selector = `input[name="${name}"]`;
    const configuration = $(selector);
    const formGroup = configuration.closest('.form-group');
    if (configuration.attr('type') == 'radio') {
      this.disableConfiguration(name);
    }
    formGroup.hide();
  },

  showConfiguration(name) {
    const selector = `input[name="${name}"]`;
    const configuration = $(selector);
    const formGroup = configuration.closest('.form-group');
    formGroup.show();
  },

  disableConfiguration(name) {
    $(`input#${name}_on`).prop('checked', false);
    $(`input#${name}_off`).prop('checked', true);
  },

  enableConfiguration(name) {
      $(`input#${name}_on`).prop('checked', true);
      $(`input#${name}_off`).prop('checked', false);
  }
};

$(document).ready(() => CustomizeCheckout.init());
