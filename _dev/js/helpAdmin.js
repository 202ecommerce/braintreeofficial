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


var HelpAdmin = {
  init() {

    // Check credentials (TLS version, country, enabling SSL)
    $('#ckeck_requirements').click(() => {
      HelpAdmin.checkCredentials();
    });
  },

  checkCredentials() {
    $.ajax({
      url: controllerUrl,
      type: 'POST',
      dataType: 'JSON',
      data: {
        ajax: true,
        action: 'CheckCredentials',
      },
      success(response) {
        let alert; let
          typeAlert;

        // Remove error messages
        $('.action_response').html('');
        if (response.success == true) {
          typeAlert = 'success';
        } else {
          typeAlert = 'danger';
        }
        for (const key in response.message) {
          alert = HelpAdmin.getAlert(response.message[key], typeAlert);
          $(alert).appendTo('.action_response');
        }
      },
    });
  },

  // Show error message
  getAlert(message, typeAlert) {
      const alert = document.createElement('div');
      let messageNode = document.createElement('div');
      messageNode.innerHTML = message;
      alert.className = `alert alert-${typeAlert}`;
      alert.appendChild(messageNode);
      return alert;
  },
};

$(document).ready(() => HelpAdmin.init());
