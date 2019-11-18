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

import {selectOption} from './functions.js'; 

$(document).ready(() => {
  if ($('#checkout-payment-step').hasClass('js-current-step')) {
    initBraintreeCard();
    if (use3dVerification) {
        initBraintreeCvvField();
    }
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
let bt_hosted_cvv_field;
let bt_cvv_client_instance;

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
        $('[data-bt-card-error-msg]').text('').hide();

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

const initBraintreeCvvField = () => {
    braintree.client.create({
        authorization,
    }, (clientErr, clientInstance) => {
        if (clientErr) {
            $('[data-bt-card-error-msg]').show().text( bt_translations_client);
            return;
        }

        // Show card form while choosing 'Add a new card'
        let cardSelect = $('[data-bt-vaulting-token="bt"]');
        let cardForm = $('[data-form-cvv-field]');
        if (cardSelect.length) {
            toggleCvv(cardSelect, cardForm);
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
                cvv: {
                    selector: '#cvv-field',
                    placeholder: '123',
                }
            },
        }, (hostedFieldsErr, hostedFieldsInstance) => {
            if (hostedFieldsErr) {
                $('[data-bt-cvv-error-msg]').show().text( bt_translations_hosted);
                return;
            }

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
                $('[data-bt-card-error-msg]').text('').hide();

                if (field.isValid) {
                    removeErrorMsg($(`#${field.container.id}`));
                    if (event.emittedBy == 'cvv') {
                        $(`#${field.container.id}`).removeClass('braintree-hosted-fields-focused');
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

            bt_hosted_cvv_field = hostedFieldsInstance;
            bt_cvv_client_instance = clientInstance;
        });
    });
};

const toggleCvv = (select, el) => {
    if (select) {
        select.on('change', (e) => {
            const index = e.target.selectedIndex;

            if (index === 0) {
                el.hide();
            } else {
                el.show();
            }
        });
    }
};



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
    const btnConfirmation = $('#payment-confirmation button');

    getOrderInformation(vaultToken).then(
        response => {
            let bt3Dinformation = response["orderInformation"];
            let payload = response["payload"];
            let use3dVerification = response["use3dVerification"];
            let btClientInstance = response["btClientInstance"];

            if (use3dVerification) {
                braintree.threeDSecure.create({
                    version: 2, //Using 3DS 2
                    client: bt_client_instance,
                }, (ThreeDSecureerror, threeDSecure) => {
                    if (ThreeDSecureerror) {
                        btnConfirmation.prop('disabled', false);
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
                                btnConfirmation.prop('disabled', false);
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
                                btnConfirmation.prop('disabled', false);
                                popup_message = bt_translations_3ds_failed_1;
                                $('[data-bt-card-error-msg]').show().text(popup_message);
                                return false;
                            } else if (three_d_secure_response.liabilityShifted == false) {
                                btnConfirmation.prop('disabled', false);
                                popup_message = bt_translations_3ds_failed_2;
                                $('[data-bt-card-error-msg]').show().text(popup_message);
                                return false;
                            }

                            $('[data-payment-method-nonce="bt"]').val(three_d_secure_response.nonce);
                            $('[data-bt-card-type]').val(three_d_secure_response.details.cardType);

                            bt_form.submit();
                        });
                });
            } else {
                if (typeof(vaultToken) == 'undefined' || vaultToken == false) {
                    $('[data-payment-method-nonce="bt"]').val(payload.nonce);
                    $('[data-bt-card-type]').val(payload.details.cardType);
                }

                bt_form.submit();
            }

        },
        errroMessage => {
            btnConfirmation.prop('disabled', false);
            $('[data-bt-card-error-msg]').show().text(errroMessage);
        }
    ).catch(error => {
        btnConfirmation.prop('disabled', false);
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
                let btCientInstance;
                let btHostedFields;

                if (("success" in response) && (response["success"] == true)) {
                    if (vaultToken && response["use3dVerification"]) {
                        btCientInstance = bt_cvv_client_instance;
                        btHostedFields = bt_hosted_cvv_field;
                    } else {
                        btCientInstance = bt_client_instance;
                        btHostedFields = bt_hosted_fileds;
                    }

                    response["orderInformation"]["onLookupComplete"] = (data, next) => {
                        next();
                    };
                    if (vaultToken && (response["use3dVerification"] == false)) {
                        response["orderInformation"]["nonce"] = $('[data-bt-vaulting-token="bt"] option:checked').data('nonce');
                        response["clientInstance"] = btCientInstance;
                        resolve(response);
                    } else {
                        btHostedFields.tokenize((tokenizeErr, payload) => {
                            if (tokenizeErr) {
                                Object.entries(btHostedFields._state.fields).forEach(entry => {
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
                                response["clientInstance"] = btCientInstance;
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