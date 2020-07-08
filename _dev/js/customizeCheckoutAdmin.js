/*
 * 2007-2020 PayPal
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author 2007-2020 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
    const usePayPalOptions = [
      $('input[name="braintreeofficial_show_paypal_benefits"]'),
      $('select[name="braintreeofficial_express_checkout_in_context"]'),
      $('.block-preview-button-context')
    ];
    const enable3DSecure = $('input[name="braintreeofficial_3DSecure"]');
    const enable3DSecureOptions = [
      $('input[name="braintreeofficial_3DSecure_amount"]')
    ];
    //const showPayPalBenefits = $('input[name="braintreeofficial_show_paypal_benefits"]');
    //const Amount3DSecure = $('input[name="braintreeofficial_3DSecure_amount"]');
    //const checkoutInContext = $('select[name="braintreeofficial_express_checkout_in_context"]');
    //const blockPreviewButton = $('.block-preview-button-context');
    const customOrderStatus = $('[name="braintreeofficial_customize_order_status"]');
    const statusOptions = [
      $('[name="braintreeofficial_os_refunded"]'),
      $('[name="braintreeofficial_os_canceled"]'),
      $('[name="braintreeofficial_os_accepted"]'),
      $('[name="braintreeofficial_os_capture_canceled"]'),
      $('[name="braintreeofficial_os_accepted_two"]'),
      $('[name="braintreeofficial_os_processing"]'),
      $('[name="braintreeofficial_os_pending"]'),
      $('.advanced-help-message'),
    ];

    // Activate paypal payment method and add additional config for it (Display block with paypal benefits)
    if (usePayPal.prop('checked') == false) {
      usePayPalOptions.forEach(CustomizeCheckout.hideConfiguration);
      //this.hideConfiguration(showPayPalBenefits);
      //this.hideConfiguration(checkoutInContext);
      //this.hideConfiguration(blockPreviewButton);
    } else {
      usePayPalOptions.forEach(CustomizeCheckout.showConfiguration);
      /*this.showConfiguration(showPayPalBenefits);
      this.showConfiguration(checkoutInContext);
      this.showConfiguration(blockPreviewButton);*/
    }

      // Activate card verification for existence and validity
      if (enable3DSecure.prop('checked') == false) {
        enable3DSecureOptions.forEach(CustomizeCheckout.hideConfiguration);
          //this.hideConfiguration(Amount3DSecure);
      } else {
        enable3DSecureOptions.forEach(CustomizeCheckout.showConfiguration);
          //this.showConfiguration(Amount3DSecure);
      }

      if (customOrderStatus.prop('checked') == false) {
        statusOptions.forEach(CustomizeCheckout.hideConfiguration);
      } else {
        statusOptions.forEach(CustomizeCheckout.showConfiguration);
      }
  },

  // Hide block while switch inactive
  hideConfiguration(elem) {
    const configuration = $(elem);
    const formGroup = configuration.closest('.form-group');
    if (configuration.attr('type') == 'radio') {
      CustomizeCheckout.disableConfiguration(elem);
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
