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
    if ($(`#pay-with-payment-option-${i}-form .payment_module`).hasClass('paypal-braintree')) {
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
    authorization: paypal_braintree.authorization,
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
        $('#bt-paypal-error-msg').show().text(paypalCheckoutErr);
        return;
      }
      
      $('[data-braintree-button]').html('');
      paypal.Button.render({
        env: paypal_braintree.mode, // 'production' or 'sandbox'
        style: {
          tagline: false
        },

        payment() {
          return paypalCheckoutInstance.createPayment({
            flow,
            amount: paypal_braintree.amount,
            currency: paypal_braintree.currency,
            billingAgreementDescription: '',
            enableShippingAddress: false,
            shippingAddressEditable: false,
          });
        },

        onAuthorize(data, actions) {
          return paypalCheckoutInstance.tokenizePayment(data)
            .then((payload) => {
              // Submit `payload.nonce` to your server.
              document.querySelector('input#braintree_payment_method_nonce').value = payload.nonce;
              $('[data-braintree-button]').hide();
              $('#braintree-error-msg').hide();
              $('#braintree-vault-info').show().append(`${payload.details.firstName} ${payload.details.lastName} ${payload.details.email}`);
            });
        },
        onError(err) {
          $('#braintree-error-msg').show().text(err);
        },
      }, '[data-braintree-button]').then((e) => {

      });
    });
  });
}

$('#payment-confirmation button').on('click', (event) => {
  selectedOption = $('input[name=payment-option]:checked').attr('id');
  if ($(`#pay-with-${selectedOption}-form .payment_module`).hasClass('paypal-braintree')) {
    return true;
  }
  if (!document.querySelector('input#braintree_payment_method_nonce').value && !$('select[name=braintree_vaulting_token]').val()) {
    event.preventDefault();
    event.stopPropagation();
    $('#braintree-error-msg').show().text(paypal_braintree.translations.empty_nonce);
  }
});