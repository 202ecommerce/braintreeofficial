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
/******/ 	return __webpack_require__(__webpack_require__.s = "./_dev/js/customizeCheckoutAdmin.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./_dev/js/customizeCheckoutAdmin.js":
/*!*******************************************!*\
  !*** ./_dev/js/customizeCheckoutAdmin.js ***!
  \*******************************************/
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
var CustomizeCheckout = {
  init: function init() {
    $('input').change(function () {
      CustomizeCheckout.checkConfigurations();
    });
    this.checkConfigurations();
  },
  checkConfigurations: function checkConfigurations() {
    var usePayPal = $('input[name="braintree_activate_paypal"]');
    var enable3DSecure = $('input[name="braintree_3DSecure"]');
    var enableCardVerification = $('input[name="braintree_card_verification"]');

    if (usePayPal.prop("checked") == false) {
      this.hideConfiguration('braintree_show_paypal_benefits');
    } else {
      this.showConfiguration('braintree_show_paypal_benefits');
    }

    if (enableCardVerification.prop("checked") == false) {
      this.hideConfiguration('braintree_3DSecure');
      this.hideConfiguration('braintree_3DSecure_amount');
    } else {
      this.showConfiguration('braintree_3DSecure');
    }

    if (enable3DSecure.prop("checked") == false) {
      this.hideConfiguration('braintree_3DSecure_amount');
    } else {
      this.showConfiguration('braintree_3DSecure_amount');
    }
  },
  hideConfiguration: function hideConfiguration(name) {
    var selector = 'input[name="' + name + '"]';
    var configuration = $(selector);
    var formGroup = configuration.closest('.form-group');

    if (configuration.attr('type') == 'radio') {
      this.disableConfiguration(name);
    }

    formGroup.hide();
  },
  showConfiguration: function showConfiguration(name) {
    var selector = 'input[name="' + name + '"]';
    var configuration = $(selector);
    var formGroup = configuration.closest('.form-group');
    formGroup.show();
  },
  disableConfiguration: function disableConfiguration(name) {
    $('input#' + name + '_on').prop("checked", false);
    $('input#' + name + '_off').prop("checked", true);
  }
};
document.addEventListener("DOMContentLoaded", function () {
  CustomizeCheckout.init();
});

/***/ })

/******/ });
//# sourceMappingURL=customizeCheckoutAdmin.js.map