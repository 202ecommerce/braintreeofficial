/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./_dev/js/payment_bt.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./_dev/js/payment_bt.js":
/*!*******************************!*\
  !*** ./_dev/js/payment_bt.js ***!
  \*******************************/
/*! no static exports found */
/***/ (function(module, exports) {

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
$(document).ready(function () {
  if ($('section#checkout-payment-step').hasClass('js-current-step')) {
    initBraintreeCard();
  }
});
var bt_hosted_fileds;
var bt_client_instance;

function initBraintreeCard() {
  braintree.client.create({
    authorization: authorization
  }, function (clientErr, clientInstance) {
    if (clientErr) {
      $('#bt-card-error-msg').show().text(bt_translations.client);
      return;
    }

    braintree.hostedFields.create({
      client: clientInstance,
      styles: {
        'input': {
          'color': '#999999',
          'font-size': '14px',
          'font-family': 'PayPal Forward, sans-serif'
        }
      },
      fields: {
        number: {
          selector: "#card-number",
          placeholder: bt_translations.card_nmb
        },
        cvv: {
          selector: "#cvv",
          placeholder: bt_translations.cvc
        },
        expirationDate: {
          selector: "#expiration-date",
          placeholder: bt_translations.date
        }
      }
    }, function (hostedFieldsErr, hostedFieldsInstance) {
      if (hostedFieldsErr) {
        $('#bt-card-error-msg').show().text(bt_translations.hosted);
        return;
      }

      hostedFieldsInstance.on('empty', function (event) {
        $('.braintree-card #card-image').removeClass();
      });
      hostedFieldsInstance.on('cardTypeChange', function (event) {
        // Change card bg depending on card type
        if (event.cards.length === 1) {
          $('.braintree-card #card-image').removeClass().addClass(event.cards[0].type);
        }
      });
      hostedFieldsInstance.on('blur', function (event) {
        var popup_message = '';
        var blur_field_info = event.fields[event.emittedBy];

        if (blur_field_info.isEmpty) {
          popup_message = bt_translations[event.emittedBy] + ' ' + bt_translations.empty_field;
        } else if (!blur_field_info.isValid) {
          popup_message = bt_translations.invalid + ' ' + bt_translations[event.emittedBy];
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
  var bt_form = document.querySelector('#braintree-card-form'); // use vaulted card

  if ($('select[name=bt_vaulting_token]').val()) {
    if (check3DS) {
      braintree.threeDSecure.create({
        client: bt_client_instance
      }, function (ThreeDSecureerror, threeDSecure) {
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
          addFrame: function addFrame(err, iframe) {
            $.fancybox.open([{
              type: 'inline',
              autoScale: true,
              minHeight: 30,
              content: '<p class="braintree-iframe">' + iframe.outerHTML + '</p>'
            }]);
          },
          removeFrame: function removeFrame() {}
        }, function (err, three_d_secure_response) {
          if (err) {
            var popup_message = '';

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
          return;
        });
      });
    } else {
      bt_form.submit();
      return;
    }
  } else {
    bt_hosted_fileds.tokenize(function (tokenizeErr, payload) {
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
          client: bt_client_instance
        }, function (ThreeDSecureerror, threeDSecure) {
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
            addFrame: function addFrame(err, iframe) {
              $.fancybox.open([{
                type: 'inline',
                autoScale: true,
                minHeight: 30,
                content: '<p class="braintree-iframe">' + iframe.outerHTML + '</p>'
              }]);
            },
            removeFrame: function removeFrame() {}
          }, function (err, three_d_secure_response) {
            if (err) {
              var popup_message = '';

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

/***/ })

/******/ });
//# sourceMappingURL=payment_bt.js.map