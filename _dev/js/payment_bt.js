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
    initBraintreeCard();
  }

  $('.js-payment-option-form').each((i) => {
    let index = i+1,   
        option_label = $(`label[for="payment-option-${index}"]`);     
    if ($(`#payment-option-${index}-additional-information .payment_module`).hasClass('braintree-card')) {
      option_label.addClass('bt__text-left');  
      option_label.find('img').css('width', '100%');
    }    
  });
});

let bt_hosted_fileds;
let bt_client_instance;

const initBraintreeCard = () => {
  braintree.client.create({
    authorization,
  }, (clientErr, clientInstance) => {
    if (clientErr) {
      $('[data-braintree-card-form]').hide();
      $('[data-bt-card-error-msg]').show().text( bt_translations_client);
      return;
    }

    // Show card form while choosing 'Add a new card'
    let cardSelect = $('[data-bt-vaulting-token="bt"]');    
    let cardForm = $('[data-form-new-card]');
    if (cardSelect) {
      selectOption(cardSelect, cardForm);
    }

    braintree.hostedFields.create({
      client: clientInstance,
      styles: {
        input: {
          'color': '#000',
          'background': '#fff',
          'height': '20px',
          'font-size': '14px',
          'font-family': 'PayPal Forward, sans-serif',
        },
        '.valid': {
          'color': '#349840'
        }
      },
      fields: {
        number: {
          selector: '#card-number',
          placeholder:  bt_translations_card_nmb,
        },
        cvv: {
          selector: '#cvv',
          placeholder: '123',
        },
        expirationDate: {
          selector: '#expiration-date',
          placeholder:  bt_translations_date,
        },
      },
    }, (hostedFieldsErr, hostedFieldsInstance) => {
      if (hostedFieldsErr) {
        $('[data-bt-card-error-msg]').show().text( bt_translations_hosted);
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
        if (blur_field_info.isEmpty || !blur_field_info.isValid) {
          setErrorMsg(event.emittedBy, blur_field_info);   
        }
      });

      hostedFieldsInstance.on('focus', (event) => {
        const focused_field_info = event.fields[event.emittedBy];
        removeErrorMsg($(`#${focused_field_info.container.id}`));
      });

      hostedFieldsInstance.on('validityChange', (event) => {
        const field = event.fields[event.emittedBy];
        if (field.isValid) {
          removeErrorMsg($(`#${field.container.id}`));
          switch (event.emittedBy) {
            case 'number':
              hostedFieldsInstance.focus('expirationDate');  
              break;
            case 'expirationDate':
              hostedFieldsInstance.focus('cvv');  
              break;
            case 'cvv':
              $(`#${field.container.id}`).removeClass('braintree-hosted-fields-focused');
              break; 
          }
        } else if (field.isPotentiallyValid) {
          removeErrorMsg($(`#${field.container.id}`));
        } else {          
          setErrorMsg(event.emittedBy, field);
        }
      });

      $('[data-bt-field]').on('click', (e) => {
        hostedFieldsInstance.focus(e.currentTarget.dataset.btField); 
      });

      bt_hosted_fileds = hostedFieldsInstance;
      bt_client_instance = clientInstance;
    });
  });
}



const removeErrorMsg = (el) => {
  el.removeClass('braintree-hosted-fields-valid');
  el.parent().find('[data-bt-error-msg]').hide();  
}

const setErrorMsg = (el, field) => {
  let popup_message = '';
  const $el = $(`#${field.container.id}`);
  const $msgBlock = $el.parent().find('[data-bt-error-msg]');
  if (field.isEmpty) { 
    popup_message = `${eval(`bt_translations_${el}`)} ${field.container.id !== 'cvv' ? `${ bt_translations_empty_field}` : ''}`;
  } else if (!field.isValid) {
    popup_message = `${eval(`bt_translations_${el}`)} ${ bt_translations_invalid}`;
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
    const vaultToken = $('[data-bt-vaulting-token="bt"]').val(); // use vaulted card
    getOrderInformation(vaultToken).then(
        response => {
            let bt3Dinformation = response["orderInformation"];
            let payload = response["payload"];
            braintree.threeDSecure.create({
                version: 2, //Using 3DS 2
                client: bt_client_instance,
            }, (ThreeDSecureerror, threeDSecure) => {
                if (ThreeDSecureerror) {
                    switch (ThreeDSecureerror.code) {
                        case 'THREEDS_HTTPS_REQUIRED':
                            popup_message =  bt_translations_https;
                            break;
                        default:
                            popup_message =  bt_translations_load_3d;
                    }
                    $('[data-bt-card-error-msg]').show().text(popup_message);
                    return false;
                }
                threeDSecure.verifyCard(
                    bt3Dinformation,
                    (err, three_d_secure_response) => {
                        let popup_message = '';
                        if (err) {
                            switch (err.code) {
                                case 'CLIENT_REQUEST_ERROR':
                                    popup_message =  bt_translations_request_problem;
                                    break;
                                default:
                                    popup_message =  bt_translations_failed_3d;
                            }
                            $('[data-bt-card-error-msg]').show().text(popup_message);
                            return false;
                        }

                        if (three_d_secure_response.liabilityShifted == false && three_d_secure_response.liabilityShiftPossible == true) {
                            popup_message = bt_translations_3ds_failed_1;
                            $('[data-bt-card-error-msg]').show().text(popup_message);
                            return false;
                        } else if (three_d_secure_response.liabilityShifted == false) {
                            popup_message = bt_translations_3ds_failed_2;
                            $('[data-bt-card-error-msg]').show().text(popup_message);
                            return false;
                        }

                        if (typeof(vaultToken) == 'undefined' || vaultToken == false) {
                            $('[data-payment-method-nonce="bt"]').val(three_d_secure_response.nonce);
                            $('[data-bt-card-type]').val(payload.details.cardType);
                        }

                        bt_form.submit();
                    });
            });
        },
        errroMessage => {
            $('[data-bt-card-error-msg]').show().text(errroMessage);
        }
    ).catch(error => {
        console.log(error);
    });

}

function getOrderInformation(vaultToken) {
    const promise = new Promise((resolve, reject) => {
        $.ajax({
            url: controllerValidation,
            type: 'POST',
            dataType: 'JSON',
            data: {
                ajax: true,
                action: 'getOrderInformation',
            },
            success(response) {
                if (("success" in response) && (response["success"] == true)) {
                    response["orderInformation"]["onLookupComplete"] = (data, next) => {
                        next();
                    };
                    if (vaultToken) {
                        response["orderInformation"]["nonce"] = $('[data-bt-vaulting-token="bt"] option:checked').data('nonce');
                        resolve(response);
                    } else {
                        bt_hosted_fileds.tokenize((tokenizeErr, payload) => {
                            if (tokenizeErr) {
                                Object.entries(bt_hosted_fileds._state.fields).forEach(entry => {
                                    setErrorMsg(entry[0], entry[1]);
                                });

                                let popup_message = '';
                                if (tokenizeErr.code !== 'HOSTED_FIELDS_FIELDS_EMPTY' && tokenizeErr.code !== 'HOSTED_FIELDS_FIELDS_INVALID') {
                                    switch (tokenizeErr.code) {
                                        case 'HOSTED_FIELDS_FAILED_TOKENIZATION':
                                            popup_message =  bt_translations_token;
                                            break;
                                        case 'HOSTED_FIELDS_TOKENIZATION_NETWORK_ERROR':
                                            popup_message =  bt_translations_network;
                                            break;
                                        default:
                                            popup_message =  bt_translations_tkn_failed;
                                    }
                                    reject(popup_message);
                                }
                            } else {
                                response["orderInformation"]["nonce"] = payload.nonce;
                                response["orderInformation"]["bin"] = payload.details.bin;
                                response["payload"] = payload;
                                resolve(response);
                            }
                        });
                    }
                }
            }
        });
    });

    return promise;
}

// Make function BraintreeSubmitPayment global for call in main module file
window.BraintreeSubmitPayment = BraintreeSubmitPayment;