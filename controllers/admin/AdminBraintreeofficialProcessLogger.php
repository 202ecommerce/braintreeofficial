<?php
/**
 * since 2007 PayPal
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

require_once(_PS_MODULE_DIR_ . 'braintreeofficial/vendor/autoload.php');

use BraintreeofficialPPBTlib\Extensions\ProcessLogger\Controllers\Admin\AdminProcessLoggerController;

class AdminBraintreeofficialProcessLoggerController extends AdminProcessLoggerController
{
    public function __construct()
    {
        parent::__construct();
        $this->className = 'BraintreeOfficialAddons\classes\BraintreeOfficialLog';

        if (isset($this->fields_list['id_transaction'])) {
            $this->fields_list['id_transaction'] = array(
                'title'    => $this->module->l('Braintree Transaction ID', 'AdminProcessLoggerController'),
                'callback' => 'getLinkToTransaction',
            );
        }

        if (isset($this->fields_list['log'])) {
            $this->fields_list['log'] = array(
                'title' => $this->module->l('Message (Braintree API response)', 'AdminProcessLoggerController'),
            );
        }
    }
}
