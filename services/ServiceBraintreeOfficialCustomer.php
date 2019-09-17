<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2019 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace BraintreeOfficialAddons\services;

use Braintree\Exception;
use BraintreeOfficialAddons\classes\BraintreeOfficialCustomer;
use BraintreeofficialPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;

class ServiceBraintreeOfficialCustomer
{
    /**
     * Load customer object by ID
     * @param integer $id_customer PrestaShop Customer ID
     * @param bool $sandbox mode of customer
     * @return object BraintreeOfficialCustomer
     */
    public function loadCustomerByMethod($id_customer, $sandbox)
    {
        $collection = new \PrestaShopCollection(BraintreeOfficialCustomer::class);
        $collection->where('id_customer', '=', (int)$id_customer);
        $collection->where('sandbox', '=', (int)$sandbox);
        return $collection->getFirst();
    }

    /**
    *   Migration of the customers from the module "paypal" to the module "braintree"
    */
    public function doMigration()
    {
        if (\Module::isInstalled('paypal')) {
            require_once _PS_MODULE_DIR_ . 'paypal/classes/PaypalCustomer.php';
            $collection = new \PrestaShopCollection('PaypalCustomer');
            $collection->where('method', '=', 'BT');

            if ($collection->count() == 0) {
                return;
            }

            ProcessLoggerHandler::openLogger();
            /* @var $paypalCustomer \PaypalCustomer*/
            foreach ($collection->getResults() as $paypalCustomer) {
                $braintreeCustomer = new BraintreeOfficialCustomer();
                $braintreeCustomer->id = $paypalCustomer->id;
                $braintreeCustomer->reference = $paypalCustomer->reference;
                $braintreeCustomer->id_customer = $paypalCustomer->id_customer;
                $braintreeCustomer->sandbox = isset($paypalCustomer->sandbox) ? $paypalCustomer->sandbox : null;
                $braintreeCustomer->date_add = $paypalCustomer->date_add;
                $braintreeCustomer->date_upd = $paypalCustomer->date_upd;
                try {
                    $braintreeCustomer->add();
                } catch (\Exception $e) {
                    \Configuration::updateValue('BRAINTREEOFFICIAL_MIGRATION_FAILED', 1);
                    $message = 'Error while migration paypal log. ';
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