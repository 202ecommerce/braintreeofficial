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
use Symfony\Component\VarDumper\VarDumper;

class ServiceBraintreeCapture
{
    /**
     * Load BraintreeCapture object by BraintreeOrder ID
     * @param integer $id_braintree_order BraintreeOrder ID
     * @return BraintreeCapture
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

    /**
     * Get all datas from BraintreeOrder and BraintreeCapture
     * @param integer $id_order PrestaShop order ID
     * @return array BraintreeCapture
     */
    public function getByOrderId($id_order)
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('braintree_order', 'bo');
        $sql->innerJoin('braintree_capture', 'bc', 'bo.`id_braintree_order` = bc.`id_braintree_order`');
        $sql->where('bo.id_order = '.(int)$id_order);
        return \Db::getInstance()->getRow($sql);
    }

    /**
     *   Migration of the captures from the module "paypal" to the module "braintree"
     */
    public function doMigration()
    {
        if (\Module::isInstalled('paypal')) {
            require_once _PS_MODULE_DIR_ . 'paypal/classes/PaypalCapture.php';
            $collection = new \PrestaShopCollection('PaypalCapture');
            $collection->where('id_paypal_order', 'in', $this->getPayPalOrderBtId());

            if ($collection->count() == 0) {
                return;
            }

            /* @var $paypalCapture \PaypalCapture*/
            foreach ($collection->getResults() as $paypalCapture) {
                $braintreeCapture = new BraintreeCapture();
                $braintreeCapture->id = $paypalCapture->id;
                $braintreeCapture->id_capture = $paypalCapture->id_capture;
                $braintreeCapture->capture_amount = $paypalCapture->capture_amount;
                $braintreeCapture->id_braintree_order = $paypalCapture->id_paypal_order;
                $braintreeCapture->result = $paypalCapture->result;
                $braintreeCapture->date_add = $paypalCapture->date_add;
                $braintreeCapture->date_upd = $paypalCapture->date_upd;
                //$braintreeCapture->save();
            }
        }
    }

    /**
     * @return array id PaypalOrder
     */
    public function getPayPalOrderBtId()
    {
        $query = new \DbQuery();
        $query->select('id_paypal_order');
        $query->from('paypal_order');
        $query->where('method="BT"');

        $result = \DB::getInstance()->executeS($query);
        $paypalOrderBtIds = array();
        if (empty($result)) {
            return $paypalOrderBtIds;
        }

        foreach ($result as $row) {
            $paypalOrderBtIds[] = $row['id_paypal_order'];
        }

        return $paypalOrderBtIds;
    }

}
