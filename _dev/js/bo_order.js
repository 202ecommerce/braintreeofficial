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

// init in-context
$(document).ready(() => {
  // Make partial order refund in Order page in BO
  $(document).on('click', '#desc-order-partial_refund', () => {
    if ($('#doPartialRefundBraintree').length == 0) {
      let p,
          label,
          input;
      
      // Create checkbox for Braintree refund 
      p = document.createElement('p');
      p.className = 'checkbox';

      label = document.createElement('label');
      label.setAttribute('for', 'doPartialRefundBraintree');

      input = document.createElement('input');
      input.type = 'checkbox';
      input.id = 'doPartialRefundBraintree';
      input.name = 'doPartialRefundBraintree';

      // insert checkbox 
      label.appendChild(input);
      label.appendChild(document.createTextNode(chb_braintree_refund));
      p.appendChild(label);

      $('button[name=partialRefund]').parent('.partial_refund_fields').prepend(p);
    }
  });
});
