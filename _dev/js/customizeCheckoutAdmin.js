/**
 * 2007-2019 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author 202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
    const usePayPal = $('input[name="braintreeofficial_activate_paypal"]');
    const enable3DSecure = $('input[name="braintreeofficial_3DSecure"]');
    const showPayPalBenefits = $('input[name="braintreeofficial_show_paypal_benefits"]');
    const Amount3DSecure = $('input[name="braintreeofficial_3DSecure_amount"]');
    const checkoutInContext = $('select[name="braintreeofficial_express_checkout_in_context"]');
    const blockPreviewButton = $('.block-preview-button-context');

    // Activate paypal payment method and add additional config for it (Display block with paypal benefits)
    if (usePayPal.prop('checked') == false) {
      this.hideConfiguration(showPayPalBenefits);
      this.hideConfiguration(checkoutInContext);
      this.hideConfiguration(blockPreviewButton);
    } else {
      this.showConfiguration(showPayPalBenefits);
      this.showConfiguration(checkoutInContext);
      this.showConfiguration(blockPreviewButton);
    }

      // Activate card verification for existence and validity
      if (enable3DSecure.prop('checked') == false) {
          this.hideConfiguration(Amount3DSecure);
      } else {
          this.showConfiguration(Amount3DSecure);
      }
  },

  // Hide block while switch inactive
  hideConfiguration(elem) {
    const configuration = $(elem);
    const formGroup = configuration.closest('.form-group');
    if (configuration.attr('type') == 'radio') {
      this.disableConfiguration(elem);
    }
    formGroup.hide();
  },

  // Show block while switch is active
  showConfiguration(elem) {
    const configuration = $(elem);
    const formGroup = configuration.closest('.form-group');
    formGroup.show();
  },

  // Disable configuration for depending settings
  disableConfiguration(elem) {
    let name = $(elem).attr('name');
    $(`input#${name}_on`).prop('checked', '');
    $(`input#${name}_off`).prop('checked', 'checked');
  },
};

$(document).ready(() => CustomizeCheckout.init());
