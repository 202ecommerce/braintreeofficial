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

// Import functions for scrolling effect to necessary block on click
import {hoverConfig, hoverTabConfig} from './functions.js';
import './utils';

const setupAdmin = {
  init() {

    // Connect to braintree account
    $('[data-confirm-credentials]').click(() => {
      $('#bt_config_account').submit();
    });

    // Disconnect from braintree account
    $('[data-bt-logout]').click((e) => {
      setupAdmin.logoutAccount(e.target);
    });

    // Check credentials (TLS version, country, enabling SSL)
    $(document).on('click', '#btn-check-requirements', () => {
      setupAdmin.checkRequirements();
    });

    // Scroll to necessary block
    $('[data-bt-link-settings]').on('click', (e) => {
      e.preventDefault();
      const el = $(e.target.attributes.href.value);

      if (el.length) {
        hoverConfig(el);
      } else {
        hoverTabConfig();
      }
    });

    // Remove effect after leaving cursor from the block
    $('.defaultForm').on('mouseleave', (e) => {
      $(e.currentTarget).removeClass('bt-settings-link-on');
    });
  },

  logoutAccount(element) {
    const form = $(element).closest('form');
    form.find('div.current-account input').val('');
    form.submit();
  },

  checkRequirements() {
    $.ajax({
      url: controllerUrl,
      type: 'POST',
      data: {
        ajax: true,
        action: 'CheckCredentials',
      },
      success(response) {
        $('#status-block').html(response);
      },
    });
  },
};

$(document).ready(() => setupAdmin.init());
