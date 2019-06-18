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

function initBraintreeCard() {
  braintree.client.create({
    authorization,
  }, (clientErr, clientInstance) => {
    if (clientErr) {
      $('#bt-card-error-msg').show().text(bt_translations.client);
      return;
    }

    braintree.hostedFields.create({
      client: clientInstance,
      styles: {
        input: {
          color: '#999999',
          'font-size': '14px',
          'font-family': 'PayPal Forward, sans-serif',
        },
      },
      fields: {
        number: {
          selector: '#card-number',
          placeholder: bt_translations.card_nmb,
        },
        cvv: {
          selector: '#cvv',
          placeholder: bt_translations.cvc,
        },
        expirationDate: {
          selector: '#expiration-date',
          placeholder: bt_translations.date,
        },
      },
    }, (hostedFieldsErr, hostedFieldsInstance) => {
      if (hostedFieldsErr) {
        $('#bt-card-error-msg').show().text(bt_translations.hosted);
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
      });

      hostedFieldsInstance.on('blur', (event) => {
        let popup_message = '';
        const blur_field_info = event.fields[event.emittedBy];
        if (blur_field_info.isEmpty) {
          popup_message = `${bt_translations[event.emittedBy]} ${bt_translations.empty_field}`;
        } else if (!blur_field_info.isValid) {
          popup_message = `${bt_translations.invalid} ${bt_translations[event.emittedBy]}`;
        }
        if (popup_message) {
          $('#bt-card-error-msg').show().text(popup_message);
        } else {
          $('#bt-card-error-msg').hide();
        }
      });
      bt_hosted_fileds = hostedFieldsInstance;
      bt_client_instance = clientInstance;
    });
  });
}

function BraintreeSubmitPayment() {
  const bt_form = document.querySelector('#braintree-card-form');

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
          $('#bt-card-error-msg').show().text(popup_message);
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
            $('#bt-card-error-msg').show().text(popup_message);
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
        var popup_message = '';
        switch (tokenizeErr.code) {
          case 'HOSTED_FIELDS_FIELDS_EMPTY':
            popup_message = bt_translations.empty;
            break;
          case 'HOSTED_FIELDS_FIELDS_INVALID':
            popup_message = bt_translations.invalid + tokenizeErr.details.invalidFieldKeys;
            break;
          case 'HOSTED_FIELDS_FAILED_TOKENIZATION':
            popup_message = bt_translations.token;
            break;
          case 'HOSTED_FIELDS_TOKENIZATION_NETWORK_ERROR':
            popup_message = bt_translations.network;
            break;
          default:
            popup_message = bt_translations.tkn_failed;
        }
        $('#bt-card-error-msg').show().text(popup_message);
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
            $('#bt-card-error-msg').show().text(popup_message);
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
              $('#bt-card-error-msg').show().text(popup_message);
              return false;
            }

            document.querySelector('#braintree-card-form #payment_method_nonce').value = three_d_secure_response.nonce;
            document.querySelector('#braintree-card-form #braintree_card_type').value = payload.details.cardType;
            bt_form.submit();
          });
        });
      } else {
        document.querySelector('#braintree-card-form #payment_method_nonce').value = payload.nonce;
        bt_form.submit();
      }
    });
  }
}
