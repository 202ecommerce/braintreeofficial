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
use BraintreeAddons\classes\BraintreeCapture;

class ServiceBraintreeCapture
{
    /**
     * Load BraintreeCapture object by BraintreeOrder ID
     * @param integer $id_braintree_order BraintreeOrder ID
     * @return object BraintreeCapture
     */
    public function loadByOrderBraintreeId($id_braintree_order)
    {
        $collection = new \PrestaShopCollection(BraintreeCapture::class);
        $collection->where('id_braintree_order', '=', (int)$id_braintree_order);
        return $collection->getFirst();
    }

    /**
     * Update PaypalCapture
     * @param string $transaction_id New transaction ID that correspond to capture
     * @param float $amount Captured amount
     * @param string $status new payment status
     * @param integer $id_braintree_order BraintreeOrder ID
     */
    public function updateCapture($transaction_id, $amount, $status, $id_braintree_order)
    {
        /* @var $braintreeCapture BraintreeCapture */
        $braintreeCapture = $this->loadByOrderBraintreeId($id_braintree_order);
        if (\Validate::isLoadedObject($braintreeCapture) == false) {
            return false;
        }
        $braintreeCapture->id_capture = pSQL($transaction_id);
        $braintreeCapture->capture_amount = (float)$amount;
        $braintreeCapture->result = pSQL($status);
        return $braintreeCapture->save();
    }
}
