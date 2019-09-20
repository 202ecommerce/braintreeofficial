<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommerce
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommerce is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommerce
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommerce est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 * @version   develop
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
