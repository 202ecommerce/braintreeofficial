<?php
/**
 * 2007-2020 PayPal
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
 *  @author 2007-2020 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace BraintreeOfficialAddons\services;

use BraintreeOfficialAddons\classes\BraintreeOfficialVaulting;
use BraintreeofficialPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use BraintreeOfficialAddons\classes\AbstractMethodBraintreeOfficial;

class ServiceBraintreeOfficialVaulting
{
    /**
     * Checking if vault was created already for this card/pp account
     * @return boolean
     */
    public function vaultingExist($token, $customer)
    {
        $collection = new \PrestaShopCollection(BraintreeOfficialVaulting::class);
        $collection->where('token', '=', pSQL($token));
        $collection->where('id_braintreeofficial_customer', '=', (int)$customer);
        $braintreeVaulting = $collection->getFirst();
        return \Validate::isLoadedObject($braintreeVaulting) ? true : false;
    }
    /**
     * Get all vaulted methods (cards, accounts) for this customer
     * @param integer $customer PrestaShop Customer ID
     * @param string $method payment tool (card or paypal account)
     * @return array BraintreeOfficialVaulting
     */
    public function getCustomerMethods($customer, $method)
    {
        /** @var $methodBraintree \MethodBraintreeOfficial*/
        $methodBraintree = AbstractMethodBraintreeOfficial::load('BraintreeOfficial');

        $db = \Db::getInstance();
        $query = new \DbQuery();
        $query->select('*');
        $query->from('braintreeofficial_vaulting', 'bv');
        $query->leftJoin('braintreeofficial_customer', 'bc', 'bv.id_braintreeofficial_customer = bc.id_braintreeofficial_customer');
        $query->where('bc.id_customer = '.(int)$customer);
        $query->where('bv.payment_tool = "'.pSQL($method).'"');
        $query->where('bc.sandbox = ' . (int)\Configuration::get('BRAINTREEOFFICIAL_SANDBOX'));
        $query->where('bc.profile_key = "' . pSQL($methodBraintree->getProfileKey()) . '"');
        $result = $db->executeS($query);
        return $result;
    }

    /**
     * Get vaulted methods grouped by tools (card or paypal account)
     * @param integer $customer PrestaShop Customer ID
     * @return array BraintreeOfficialVaulting
     */
    public function getCustomerGroupedMethods($customer)
    {
        /** @var $method \MethodBraintreeOfficial*/
        $method = AbstractMethodBraintreeOfficial::load('BraintreeOfficial');

        $db = \Db::getInstance();
        $methods = array();
        $query = new \DbQuery();
        $query->select('*');
        $query->from('braintreeofficial_vaulting', 'bv');
        $query->leftJoin('braintreeofficial_customer', 'bc', 'bv.id_braintreeofficial_customer = bc.id_braintreeofficial_customer');
        $query->where('bc.id_customer = '.(int)$customer);
        $query->where('bc.sandbox = ' . (int)\Configuration::get('BRAINTREEOFFICIAL_SANDBOX'));
        $query->where('bc.profile_key = "' . pSQL($method->getProfileKey()) . '"');
        $results = $db->query($query);
        while ($result = $db->nextRow($results)) {
            $methods[$result['payment_tool']][] = $result;
        }
        return $methods;
    }

    /**
     *   Migration of the vaulting from the module "paypal" to the module "braintree"
     */
    public function doMigration()
    {
        if (\Module::isInstalled('paypal')) {
            require_once _PS_MODULE_DIR_ . 'paypal/classes/PaypalVaulting.php';
            $collection = new \PrestaShopCollection('PaypalVaulting');

            if ($collection->count() == 0) {
                return;
            }

            ProcessLoggerHandler::openLogger();
            /* @var $paypalVaulting \PaypalVaulting*/
            foreach ($collection->getResults() as $paypalVaulting) {
                $braintreeVaulting = new BraintreeOfficialVaulting();
                $braintreeVaulting->id = $paypalVaulting->id;
                $braintreeVaulting->token = $paypalVaulting->token;
                $braintreeVaulting->id_braintreeofficial_customer = $paypalVaulting->id_paypal_customer;
                $braintreeVaulting->payment_tool = $paypalVaulting->payment_tool;
                $braintreeVaulting->name = $paypalVaulting->name;
                $braintreeVaulting->info = $paypalVaulting->info;
                $braintreeVaulting->date_add = $paypalVaulting->date_add;
                $braintreeVaulting->date_upd = $paypalVaulting->date_upd;
                try {
                    $braintreeVaulting->add();
                } catch (\Exception $e) {
                    \Configuration::updateValue('BRAINTREEOFFICIAL_MIGRATION_FAILED', 1);
                    $message = 'Error while migration paypal vaulting. ';
                    $message .= 'File: ' . $e->getFile() . '. ';
                    $message .= 'Line: ' . $e->getLine() . '. ';
                    $message .= 'Message: ' . $e->getMessage() . '.';
                    ProcessLoggerHandler::logError($message);
                }
            }
            ProcessLoggerHandler::closeLogger();
        }
    }
}
