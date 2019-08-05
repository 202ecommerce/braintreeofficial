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

import {selectOption} from './functions.js'; 

$(document).ready(() => {
  if ($('section#checkout-payment-step').hasClass('js-current-step')) {
    initPaypalBraintree('checkout');
  }

  // Init paypal braintree method with chosen option
  $(document).on('change', 'input[name=save_account_in_vault]', (e) => {
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
        env: paypal_braintree_mode, // 'production' or 'sandbox'
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

