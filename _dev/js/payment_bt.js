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
    initBraintreeCard();
  }
});

let bt_hosted_fileds;
let bt_client_instance;

const initBraintreeCard = () => {
  braintree.client.create({
    authorization,
  }, (clientErr, clientInstance) => {
    if (clientErr) {
      $('[data-bt-card-error-msg]').show().text(bt_translations.client);
      return;
    }

    braintree.hostedFields.create({
      client: clientInstance,
      styles: {
        input: {
          color: '#999999',
          'background': '#fff',
          'height': '20px',
          'font-size': '14px',
          'font-family': 'PayPal Forward, sans-serif',
        }
      },
      fields: {
        number: {
          selector: '#card-number',
          placeholder: bt_translations.card_nmb,
        },
        cvv: {
          selector: '#cvv',
          placeholder: '123',
        },
        expirationDate: {
          selector: '#expiration-date',
          placeholder: bt_translations.date,
        },
      },
    }, (hostedFieldsErr, hostedFieldsInstance) => {
      if (hostedFieldsErr) {
        $('[data-bt-card-error-msg]').show().text(bt_translations.hosted);
        return;
      }

      hostedFieldsInstance.on('empty', (event) => {
        $('.braintree-card #card-image').removeClass();
      });

      hostedFieldsInstance.on('cardTypeChange', (event) => {

        // Change card bg depending on card type
        if (event.cards.length === 1) {
          $('.braintree-card #card-image').removeClass().addClass(event.cards[0].type);
        }

        // Change placeholder value for CVV depending on card type
        if (event.cards[0].code.size === 4) {          
          hostedFieldsInstance.setAttribute({
            field: 'cvv',
            attribute: 'placeholder',
            value: '1234'
          });
        }

      });

      hostedFieldsInstance.on('blur', (event) => {        
        const blur_field_info = event.fields[event.emittedBy];
        setErrorMsg(event.emittedBy, blur_field_info);      
      });

      hostedFieldsInstance.on('focus', (event) => {
        const focused_field_info = event.fields[event.emittedBy];
        const $el = $(`#${focused_field_info.container.id}`);
        $el.parent().find('[data-bt-error-msg]').hide();        
      });

      bt_hosted_fileds = hostedFieldsInstance;
      bt_client_instance = clientInstance;
    });
  });
}

const setErrorMsg = (el, field) => {
  let popup_message = '';
  const $el = $(`#${field.container.id}`);
  const $msgBlock = $el.parent().find('[data-bt-error-msg]');
  if (field.isEmpty) {          
    popup_message = `${bt_translations[el]} ${field.container.id !== 'cvv' ? `${bt_translations.empty_field}` : ''}`;
  } else if (!field.isValid) {
    popup_message = `${bt_translations[el]} ${bt_translations.invalid}`;
  }
  if (popup_message) {  
    $el.addClass('braintree-hosted-fields-invalid'); 
    $msgBlock.show();       
    $msgBlock.html(popup_message);
  } else {             
    $el.removeClass('braintree-hosted-fields-invalid'); 
    $msgBlock.hide();
  }
}

const BraintreeSubmitPayment = () => {
  const bt_form = $('[data-braintree-card-form]');

  // use vaulted card
  if ($('select[name=bt_vaulting_token]').val()) {
    if (check3DS) {
      braintree.threeDSecure.create({
        client: bt_client_instance,
      }, (ThreeDSecureerror, threeDSecure) => {
        if (ThreeDSecureerror) {
          switch (ThreeDSecureerror.code) {
            case 'THREEDS_HTTPS_REQUIRED':
              popup_message = bt_translations.https;
              break;
            default:
              popup_message = bt_translations.load_3d;
          }
          $('[data-bt-card-error-msg]').show().text(popup_message);
          return false;
        }
        threeDSecure.verifyCard({
          amount: bt_amount,
          nonce: $('select[name=bt_vaulting_token] option:checked').data('nonce'),
          addFrame(err, iframe) {
            $.fancybox.open([
              {
                type: 'inline',
                autoScale: true,
                minHeight: 30,
                content: `<p class="braintree-iframe">${iframe.outerHTML}</p>`,
              },
            ]);
          },
          removeFrame() {
          },
        }, (err, three_d_secure_response) => {
          if (err) {
            let popup_message = '';
            switch (err.code) {
              case 'CLIENT_REQUEST_ERROR':
                popup_message = bt_translations.request_problem;
                break;
              default:
                popup_message = bt_translations.failed_3d;
            }
            $('[data-bt-card-error-msg]').show().text(popup_message);
            return false;
          }
          bt_form.submit();
        });
      });
    } else {
      bt_form.submit();
    }
  } else {
    bt_hosted_fileds.tokenize((tokenizeErr, payload) => {
      if (tokenizeErr) {        
        Object.entries(bt_hosted_fileds._state.fields).forEach(entry => {
          setErrorMsg(entry[0], entry[1]);
        });       
      
        var popup_message = '';
        if (tokenizeErr.code !== 'HOSTED_FIELDS_FIELDS_EMPTY' && tokenizeErr.code !== 'HOSTED_FIELDS_FIELDS_INVALID') {      
          switch (tokenizeErr.code) {
            case 'HOSTED_FIELDS_FAILED_TOKENIZATION':
              popup_message = bt_translations.token;
              break;
            case 'HOSTED_FIELDS_TOKENIZATION_NETWORK_ERROR':
              popup_message = bt_translations.network;
              break;
            default:
              popup_message = bt_translations.tkn_failed;
          }
          $('[data-bt-card-error-msg]').show().text(popup_message);
        }
        return false;
      }
      if (check3DS) {
        braintree.threeDSecure.create({
          client: bt_client_instance,
        }, (ThreeDSecureerror, threeDSecure) => {
          if (ThreeDSecureerror) {
            switch (ThreeDSecureerror.code) {
              case 'THREEDS_HTTPS_REQUIRED':
                popup_message = bt_translations.https;
                break;
              default:
                popup_message = bt_translations.load_3d;
            }
            $('[data-bt-card-error-msg]').show().text(popup_message);
            return false;
          }
          threeDSecure.verifyCard({
            nonce: payload.nonce,
            amount: bt_amount,
            addFrame(err, iframe) {
              $.fancybox.open([
                {
                  type: 'inline',
                  autoScale: true,
                  minHeight: 30,
                  content: `<p class="braintree-iframe">${iframe.outerHTML}</p>`,
                },
              ]);
            },
            removeFrame() {
            },
          }, (err, three_d_secure_response) => {
            if (err) {
              let popup_message = '';
              switch (err.code) {
                case 'CLIENT_REQUEST_ERROR':
                  popup_message = bt_translations.request_problem;
                  break;
                default:
                  popup_message = bt_translations.failed_3d;
              }
              $('[data-bt-card-error-msg]').show().text(popup_message);
              return false;
            }

            $('[data-bt-payment-method-nonce]').val(three_d_secure_response.nonce);
            $('[data-bt-card-type]').val(payload.details.cardType);
            bt_form.submit();
          });
        });
      } else {
        $('[data-bt-payment-method-nonce]').val(payload.nonce);
        bt_form.submit();
      }
    });
  }
}

// Make function BraintreeSubmitPayment global for call in main module file
window.BraintreeSubmitPayment = BraintreeSubmitPayment;