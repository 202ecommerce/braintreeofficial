<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommence
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommence is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommence
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommence est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 */

class AdminBraintreeStatsController extends ModuleAdminController
{

    /** @var Module Instance of your module automatically set by ModuleAdminController */
    public $module;

    /** @var string redirection link */
    public $report_link;

    public function __construct()
    {
        parent::__construct();
        Tools::redirect($this->getReportLink());
    }

    public function getReportLink()
    {
        if ((int)Configuration::get('BRAINTREE_SANDBOX')) {
            $this->report_link = "https://sandbox.braintreegateway.com/merchants/".Configuration::get('BRAINTREE_MERCHANT_ID_SANDBOX')."/transactions/advanced_search";
        } else {
            $this->report_link = "https://www.braintreegateway.com/merchants/".Configuration::get('BRAINTREE_MERCHANT_ID_LIVE')."/transactions/advanced_search";
        }
        return $this->report_link;
    }
}
