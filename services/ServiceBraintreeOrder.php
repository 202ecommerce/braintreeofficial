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
     * @return object BraintreeOrder
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

}
