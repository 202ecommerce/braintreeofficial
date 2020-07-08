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

use BraintreeOfficialAddons\classes\BraintreeOfficialOrder;
use BraintreeofficialPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;

class ServiceBraintreeOfficialOrder
{
    /**
     * Load BraintreeOfficialOrder object by PrestaShop order ID
     * @param integer $id_order Order ID
     * @return BraintreeOfficialOrder
     */
    public function loadByOrderId($id_order)
    {
        $collection = new \PrestaShopCollection(BraintreeOfficialOrder::class);
        $collection->where('id_order', '=', (int)$id_order);
        return $collection->getFirst();
    }

    /**
     * Get BT records
     * @return array all BT transaction IDs
     */
    public function getBraintreeOrdersForValidation()
    {
        $collection = new \PrestaShopCollection(BraintreeOfficialOrder::class);
        $collection->where('payment_method', '=', 'sale');
        $collection->where('payment_tool', 'in', array('paypal_account', 'PayPal'));
        $collection->where('payment_status', 'in', array('settling', 'submitted_for_settlement'));
        return $collection->getResults();
    }

    /**
     * @param string $id_transaction Transaction ID
     * @return BraintreeOfficialOrder Order id
     */
    public function loadByTransactionId($id_transaction)
    {
        $collection = new \PrestaShopCollection(BraintreeOfficialOrder::class);
        $collection->where('id_transaction', '=', pSQL($id_transaction));
        return $collection->getFirst();
    }

    /**
     *   Migration of the orders from the module "paypal" to the module "braintree"
     */
    public function doMigration()
    {
        if (\Module::isInstalled('paypal')) {
            require_once _PS_MODULE_DIR_ . 'paypal/classes/PaypalOrder.php';
            $collection = new \PrestaShopCollection('PaypalOrder');
            $collection->where('method', '=', 'BT');

            if ($collection->count() == 0) {
                return;
            }

            ProcessLoggerHandler::openLogger();
            /* @var $paypalOrder \PaypalOrder*/
            foreach ($collection->getResults() as $paypalOrder) {
                $braintreeOrder = new BraintreeOfficialOrder();
                $braintreeOrder->id = $paypalOrder->id;
                $braintreeOrder->id_order = $paypalOrder->id_order;
                $braintreeOrder->payment_tool = $paypalOrder->payment_tool;
                $braintreeOrder->payment_method = $paypalOrder->payment_method;
                $braintreeOrder->id_cart = $paypalOrder->id_cart;
                $braintreeOrder->id_payment = $paypalOrder->id_payment;
                $braintreeOrder->id_transaction = $paypalOrder->id_transaction;
                $braintreeOrder->sandbox = isset($paypalOrder->sandbox) ? $paypalOrder->sandbox : null;
                $braintreeOrder->currency = $paypalOrder->currency;
                $braintreeOrder->payment_status = $paypalOrder->payment_status;
                $braintreeOrder->total_paid = $paypalOrder->total_paid;
                $braintreeOrder->total_prestashop = $paypalOrder->total_prestashop;
                $braintreeOrder->date_add = $paypalOrder->date_add;
                $braintreeOrder->date_upd = $paypalOrder->date_upd;
                try {
                    $braintreeOrder->add();
                } catch (\Exception $e) {
                    \Configuration::updateValue('BRAINTREEOFFICIAL_MIGRATION_FAILED', 1);
                    $message = 'Error while migration paypal order. ';
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
     * @return int
     */
    public function getCountOrders()
    {
        $collection = new \PrestaShopCollection(BraintreeOfficialOrder::class);
        return $collection->count();
    }

    public function deleteBtOrderFromPayPal()
    {
        if (\Module::isInstalled('paypal')) {
            require_once _PS_MODULE_DIR_ . 'paypal/classes/PaypalOrder.php';
            require_once _PS_MODULE_DIR_ . 'paypal/classes/PaypalCapture.php';
            $collection = new \PrestaShopCollection(BraintreeOfficialOrder::class);

            if ($collection->count() == 0) {
                return;
            }

            ProcessLoggerHandler::openLogger();
            /* @var $braintreeOrder BraintreeOfficialOrder
             * @var $paypalOrder \PaypalOrder
             * @var $paypalCapture \PaypalCapture
             */
            foreach ($collection->getResults() as $braintreeOrder) {
                try {
                    $paypalOrder = \PaypalOrder::loadByOrderId($braintreeOrder->id_order); // method loadByOrderId exists
                    $paypalCapture = \PaypalCapture::loadByOrderPayPalId($paypalOrder->id); // method loadByOrderPayPalId exists
                    $paypalOrder->delete();
                    $paypalCapture->delete();
                } catch (\Exception $e) {
                    \Configuration::updateValue('BRAINTREEOFFICIAL_MIGRATION_FAILED', 1);
                    $message = 'Error while deleting paypal order. ';
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
