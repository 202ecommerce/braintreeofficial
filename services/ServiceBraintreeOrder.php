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
use BraintreeAddons\classes\BraintreeOrder;

class ServiceBraintreeOrder
{
    /**
     * Load BraintreeOrder object by PrestaShop order ID
     * @param integer $id_order Order ID
     * @return BraintreeOrder
     */
    public function loadByOrderId($id_order)
    {
        $collection = new \PrestaShopCollection(BraintreeOrder::class);
        $collection->where('id_order', '=', (int)$id_order);
        return $collection->getFirst();
    }

    /**
     * Get BT records
     * @return array all BT transaction IDs
     */
    public function getBraintreeOrdersForValidation()
    {
        $collection = new \PrestaShopCollection(BraintreeOrder::class);
        $collection->where('payment_method', '=', 'sale');
        $collection->where('payment_tool', '=', 'paypal_account');
        $collection->where('payment_status', 'in', array('settling', 'submitted_for_settlement'));
        return $collection->getResults();
    }

    /**
     * @param string $id_transaction Transaction ID
     * @return BraintreeOrder Order id
     */
    public function loadByTransactionId($id_transaction)
    {
        $collection = new \PrestaShopCollection(BraintreeOrder::class);
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

            /* @var $paypalOrder \PaypalOrder*/
            foreach ($collection->getResults() as $paypalOrder) {
                $braintreeOrder = new BraintreeOrder();
                $braintreeOrder->id = $paypalOrder->id;
                $braintreeOrder->id_order = $paypalOrder->id_order;
                $braintreeOrder->payment_tool = $paypalOrder->payment_tool;
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
                $braintreeOrder->save();
            }
        }
    }

}
