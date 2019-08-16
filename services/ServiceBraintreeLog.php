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

namespace BraintreeAddons\services;

use BraintreeAddons\classes\BraintreeLog;
use BraintreeAddons\classes\AbstractMethodBraintree;
use BraintreePPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class ServiceBraintreeLog
{
    /**
     * @param $log BraintreeLog
     * @return url
     */
    public function getLinkToTransaction($log)
    {
        /* @var $method \MethodBraintree*/
        if ($log->id_transaction == false || $log->id_order == false) {
            return '';
        }

        $method = AbstractMethodBraintree::load('Braintree');
        return $method->getLinkToTransaction($log->id_transaction, $log->sandbox);
    }

    /**
     *   Migration of the logs from the module "paypal" to the module "braintree"
     */
    public function doMigration()
    {
        if (\Module::isInstalled('paypal') && file_exists(_PS_MODULE_DIR_ . 'paypal/classes/PaypalLog.php')) {
            require_once _PS_MODULE_DIR_ . 'paypal/classes/PaypalLog.php';

            // Need to import only the logs related to brainree. So at first we get id of cart related to braintree for filtering below
            $cartBtIds = $this->getCartBtId();

            if (empty($cartBtIds)) {
                return;
            }

            $collection = new \PrestaShopCollection('PaypalLog');
            $collection->where('id_cart', 'in', $cartBtIds);

            if ($collection->count() == 0) {
                return;
            }

            ProcessLoggerHandler::openLogger();
            /* @var $paypalLog \PaypalLog*/
            foreach ($collection->getResults() as $paypalLog) {
                $braintreeLog = new BraintreeLog();
                $braintreeLog->id_cart = $paypalLog->id_cart;
                $braintreeLog->id_order = $paypalLog->id_order;
                $braintreeLog->id_transaction = $paypalLog->id_transaction;
                $braintreeLog->sandbox = $paypalLog->sandbox;
                $braintreeLog->date_transaction = $paypalLog->date_transaction == '0000-00-00 00:00:00' ? null : $paypalLog->date_transaction;
                $braintreeLog->date_add = $paypalLog->date_add;
                $braintreeLog->tools = $paypalLog->tools;
                $braintreeLog->status = $paypalLog->status;
                $braintreeLog->id_shop = $paypalLog->id_shop;
                $braintreeLog->log = $paypalLog->log;
                try {
                    $braintreeLog->save();
                } catch (\Exception $e) {
                    \Configuration::updateValue('BRAINTREE_MIGRATION_FAILED', 1);
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

    /**
     * @return array id cart
     */
    public function getCartBtId()
    {
        $cartBtIds = array();
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();

        if ($moduleManager->isInstalled('paypal') == false) {
            return $cartBtIds;
        }

        $query = new \DbQuery();
        $query->select('id_cart');
        $query->from('paypal_order');
        $query->where('method="BT"');

        $result = \DB::getInstance()->executeS($query);

        if (empty($result)) {
            return $cartBtIds;
        }

        foreach ($result as $row) {
            $cartBtIds[] = $row['id_cart'];
        }

        return $cartBtIds;
    }
}
