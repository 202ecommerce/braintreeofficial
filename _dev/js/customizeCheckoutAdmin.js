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

// Import functions for scrolling effect to necessary block on click
import {hoverConfig, hoverTabConfig} from './functions.js'; 

var CustomizeCheckout = {
  init() {
    // Check configuration while changing values of switch config buttons
    $('input').change(() => {
      CustomizeCheckout.checkConfigurations();
    });
    this.checkConfigurations();

    // Scroll to necessary block
    $('[data-bt-link-settings]').on('click', (e) => {
      let el = $(e.target.attributes.href.value);
      if (el.length) {
        hoverConfig(el);
      } else {
        hoverTabConfig();
      }
    });

    // Remove effect after leaving cursor from the block
    $('.defaultForm').on('mouseleave', (e) => {
      $(e.currentTarget).removeClass('bt-settings-link-on');
    });
  },

  checkConfigurations() {
    const usePayPal = $('input[name="braintree_activate_paypal"]');
    const enableCardVerification = $('input[name="braintree_card_verification"]');
    const enableVault = $('input[name="braintree_vaulting"]');
    const showPayPalBenefits = $('input[name="braintree_show_paypal_benefits"]');

    // Activate paypal payment method and add additional config for it (Display block with paypal benefits)
    if (usePayPal.prop('checked') == false) {
      this.hideConfiguration(showPayPalBenefits.attr('name'));
    } else {
      this.showConfiguration(showPayPalBenefits.attr('name'));
    }

    // Activate vaulting (saving cards/paypal account for next payments)
    if (enableVault.prop('checked') == false) {
      this.hideConfiguration(enableCardVerification.attr('name'));
    } else {
      this.showConfiguration(enableCardVerification.attr('name'));
    }
  },

  // Hide block while switch inactive
  hideConfiguration(name) {
    const selector = `input[name="${name}"]`;
    const configuration = $(selector);
    const formGroup = configuration.closest('.form-group');
    if (configuration.attr('type') == 'radio') {
      this.disableConfiguration(name);
    }
    formGroup.hide();
  },

  // Show block while switch is active
  showConfiguration(name) {
    const selector = `input[name="${name}"]`;
    const configuration = $(selector);
    const formGroup = configuration.closest('.form-group');
    formGroup.show();
  },

  // Disable configuration for depending settings
  disableConfiguration(name) {
    $(`input#${name}_on`).prop('checked', '');
    $(`input#${name}_off`).prop('checked', 'checked');
  },
};

$(document).ready(() => CustomizeCheckout.init());
