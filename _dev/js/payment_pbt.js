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

$(document).ready(() => {
  if ($('section#checkout-payment-step').hasClass('js-current-step')) {
    initPaypalBraintree('checkout');
  }
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
  $('[data-bt-paypal-info-popover]').popover(); 
});


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
              $('[data-bt-payment-method-nonce]').val(payload.nonce);
              $('[data-braintree-button]').hide();
              $('[data-bt-pp-error-msg]').hide();
              $('#braintree-vault-info').show().append(`${payload.details.firstName} ${payload.details.lastName} ${payload.details.email}`);
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

const BraintreePaypalSubmitPayment = (e) => {
  let selectedOption = $('input[name=payment-option]:checked').attr('id');
  if (!$('[data-bt-payment-method-nonce]').val() && !$('[data-bt-vaulting-token="pbt"]').val()) {
    $('[data-bt-pp-error-msg]').show().text(empty_nonce);
    console.log(555);
    
    return false;
  }
  if ($(`#${selectedOption}-additional-information .payment_module`).hasClass('paypal-braintree')) {
    $('[data-braintree-paypal-form]').submit();
  }
  
}

window.BraintreePaypalSubmitPayment = BraintreePaypalSubmitPayment;

