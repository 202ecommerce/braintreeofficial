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

import {selectOption} from './functions.js'; 

$(document).ready(() => {
  if ($('#checkout-payment-step').hasClass('js-current-step')) {
    initPaypalBraintree('checkout');
  }
  function addMask() {
    $('[data-braintree-button]').removeClass('disabled');
    }
  // Init paypal braintree method with chosen option
  $(document).on('change', 'input[name=save_account_in_vault]', (e) => {
      $('[data-braintree-button]').addClass('disabled');
      setTimeout(addMask, 1500);      
      
      if (e.target.checked === true) {
        initPaypalBraintree('vault');
      } else {
        initPaypalBraintree('checkout');
      }
  });

  // Insert paypal info block after option name 
  $('.js-payment-option-form').each((i) => {    
    if ($(`#payment-option-${i}-additional-information .payment_module`).hasClass('paypal-braintree')) {
      $('[data-bt-paypal-info]').insertAfter($(`#payment-option-${i}-container label`));      
    }    
  });

  // Show block with paypal payment benefits  
  let configs = getConfigPopup();
  $('[data-bt-paypal-info-popover]').popover({
    placement: configs.popoverPlacement,
    trigger: configs.popoverTrigger
  }); 

  if ($(window).width() > 991) {
    hoverPopup();
  }

  // Show paypal button while choosing 'Add a new paypal account'
  let accountSelect = $('[data-bt-vaulting-token="pbt"]');    
  let accountForm = $('[data-form-new-account]');
  if (accountSelect) {
    selectOption(accountSelect, accountForm);
  }
});

const getConfigPopup = () => {
  let placement = 'right',
      trigger = 'hover';
  if ($(window).width() < 992) {
    placement = 'bottom';
    trigger = 'click';
  }
  return {
    popoverPlacement: placement,
    popoverTrigger: trigger
  }

}

$(document).click('[data-bt-paypal-info-popover] i', (e) => { 
  clickPopup(e);
});

const hidePopup = (el) => {
  $('[data-bt-paypal-info-popover]').popover('hide');
  $('body').removeClass('pp-popover'); 
  el.text('info');
}

const showPopup = (el) => {
  $('[data-bt-paypal-info-popover]').popover('show');
  $('body').addClass('pp-popover'); 
  el.text('cancel');
}

const clickPopup = (e) => {
  $('[data-bt-paypal-info-popover]').popover('toggle');
  if($(e.target).closest('[data-bt-paypal-info-popover]').length != 0) {
    e.preventDefault();
    if ($(e.target).text() == 'info') {
      showPopup($(e.target));
    } else {
      hidePopup($(e.target));
    }
  } else {
    hidePopup($('[data-bt-paypal-info] i'));
  }
}

const hoverPopup = () => {
  $('[data-bt-paypal-info-popover] i').on('mouseover', (e) => {
    e.target.innerText = 'cancel';
    $('body').addClass('pp-popover'); 
  })
  
  $('[data-bt-paypal-info-popover] i').on('mouseout', (e) => {
    e.target.innerText = 'info';
    if (!$('[data-pp-info]').is(':visible')) {
      $('body').removeClass('pp-popover');
    }
  })

  $('[data-bt-paypal-info-popover] i').on('click', (e) => {
    hidePopup($(e.target));
  })
} 


// Init paypal braintree
const initPaypalBraintree = (flow) => {

  braintree.client.create({
    authorization: paypal_braintree_authorization,
  }, (clientErr, clientInstance) => {
    // Stop if there was a problem creating the client.
    // This could happen if there is a network error or if the authorization
    // is invalid.
    if (clientErr) {
      console.error('Error creating client:', clientErr);
      return;
    }

    // Create a PayPal Checkout component.
    braintree.paypalCheckout.create({
      client: clientInstance,
    }, (paypalCheckoutErr, paypalCheckoutInstance) => {
      // Stop if there was a problem creating PayPal Checkout.
      // This could happen if there was a network error or if it's incorrectly
      // configured.
      if (paypalCheckoutErr) {
        $('[data-bt-pp-error-msg]').show().text(paypalCheckoutErr);
        return;
      }
      
      $('[data-braintree-button]').html('');
      paypal.Button.render({
        locale: envLocale,
        env: paypal_braintree_mode, // 'production' or 'sandbox'
        commit: true,
        style: {
          tagline: false
        },

        payment() {
          return paypalCheckoutInstance.createPayment({
            flow,
            amount: paypal_braintree_amount,
            currency: paypal_braintree_currency,
            billingAgreementDescription: '',
            enableShippingAddress: false,
            shippingAddressEditable: false,
          });
        },

        onAuthorize(data, actions) {
          return paypalCheckoutInstance.tokenizePayment(data)
            .then((payload) => {
              // Submit `payload.nonce` to your server.
              $('[data-payment-method-nonce="pbt"]').val(payload.nonce);
              $('[data-braintree-button]').hide();
              $('[data-bt-pp-error-msg]').hide();
              $('[data-bt-save-account]').hide();
              $('[data-bt-vault-info]').show().append(`${payload.details.firstName} ${payload.details.lastName} ${payload.details.email}`);
              $('#conditions-to-approve input').prop('checked', false);
            });
        },
        onError(err) {
          $('[data-bt-pp-error-msg]').show().text(err);
        },
      }, '[data-braintree-button]').then((e) => {

      });
    });
  });
  
}

// Order payment button action
const BraintreePaypalSubmitPayment = (e) => {
  // Show error message if there are not saving account chosen or a new paypal account is not activated
  if (!$('[data-payment-method-nonce="pbt"]').val() && !$('[data-bt-vaulting-token="pbt"]').val()) {
    $('[data-bt-pp-error-msg]').show().text(empty_nonce);
    return false;
  } else {
    // submit payment button
    $('[data-braintree-paypal-form]').submit();
  }
}

// Encapsulate function while webpack compiling
window.BraintreePaypalSubmitPayment = BraintreePaypalSubmitPayment;

