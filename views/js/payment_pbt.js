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
/******/ 	return __webpack_require__(__webpack_require__.s = "./_dev/js/payment_pbt.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./_dev/js/payment_pbt.js":
/*!********************************!*\
  !*** ./_dev/js/payment_pbt.js ***!
  \********************************/
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
    initPaypalBraintree('checkout');
  }

  $(document).on('change', 'input[name=save_account_in_vault]', function () {
    $('#braintree-button').html('');

    if ($(this).is(':checked')) {
      initPaypalBraintree('vault');
    } else {
      initPaypalBraintree('checkout');
    }
  });
});

function initPaypalBraintree(flow) {
  braintree.client.create({
    authorization: paypal_braintree.authorization
  }, function (clientErr, clientInstance) {
    // Stop if there was a problem creating the client.
    // This could happen if there is a network error or if the authorization
    // is invalid.
    if (clientErr) {
      console.error('Error creating client:', clientErr);
      return;
    } // Create a PayPal Checkout component.


    braintree.paypalCheckout.create({
      client: clientInstance
    }, function (paypalCheckoutErr, paypalCheckoutInstance) {
      // Stop if there was a problem creating PayPal Checkout.
      // This could happen if there was a network error or if it's incorrectly
      // configured.
      if (paypalCheckoutErr) {
        $('#bt-paypal-error-msg').show().text(paypalCheckoutErr);
        return;
      }

      paypal.Button.render({
        env: paypal_braintree.mode,
        // 'production' or 'sandbox'
        payment: function payment() {
          return paypalCheckoutInstance.createPayment({
            flow: flow,
            amount: paypal_braintree.amount,
            currency: paypal_braintree.currency,
            billingAgreementDescription: '',
            enableShippingAddress: false,
            shippingAddressEditable: false
          });
        },
        onAuthorize: function onAuthorize(data, actions) {
          return paypalCheckoutInstance.tokenizePayment(data).then(function (payload) {
            // Submit `payload.nonce` to your server.
            document.querySelector('input#braintree_payment_method_nonce').value = payload.nonce;
            $('#braintree-button').hide();
            $('#braintree-error-msg').hide();
            $('#braintree-vault-info').show().append(payload.details.firstName + ' ' + payload.details.lastName + ' ' + payload.details.email);
          });
        },
        onCancel: function onCancel(data) {// $('#bt-paypal-error-msg').show().text('checkout.js payment cancelled'+JSON.stringify(data, 0, 2)+'');
        },
        onError: function onError(err) {
          $('#braintree-error-msg').show().text(err);
        }
      }, '#braintree-button').then(function (e) {});
      $('#payment-confirmation button').click(function (event) {
        payment_selected = $('input[name=payment-option]:checked').attr('id');

        if (!$('#pay-with-' + payment_selected + '-form .payment_module').hasClass('paypal-braintree')) {
          return true;
        }

        if (!document.querySelector('input#braintree_payment_method_nonce').value && !$('select[name=braintree_vaulting_token]').val()) {
          event.preventDefault();
          event.stopPropagation();
          $('#braintree-error-msg').show().text(paypal_braintree.translations.empty_nonce);
        }
      });
    });
  });
}

/***/ })

/******/ });
//# sourceMappingURL=payment_pbt.js.map