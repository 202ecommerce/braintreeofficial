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

use BraintreeOfficialAddons\classes\BraintreeOfficialCapture;
use BraintreeofficialPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

class ServiceBraintreeOfficialCapture
{
    /**
     * Load BraintreeOfficialCapture object by BraintreeOfficialOrder ID
     * @param integer $id_braintreeofficial_order BraintreeOfficialOrder ID
     * @return BraintreeOfficialCapture
     */
    public function loadByOrderBraintreeId($id_braintree_order)
    {
        $collection = new \PrestaShopCollection(BraintreeOfficialCapture::class);
        $collection->where('id_braintreeofficial_order', '=', (int)$id_braintree_order);
        return $collection->getFirst();
    }

    /**
     * Update BraintreeOfficialCapture
     * @param string $transaction_id New transaction ID that correspond to capture
     * @param float $amount Captured amount
     * @param string $status new payment status
     * @param integer $id_braintreeofficial_order BraintreeOfficialOrder ID
     */
    public function updateCapture($transaction_id, $amount, $status, $id_braintree_order)
    {
        /* @var $braintreeCapture BraintreeOfficialCapture */
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
     * Get all datas from BraintreeOfficialOrder and BraintreeOfficialCapture
     * @param integer $id_order PrestaShop order ID
     * @return array BraintreeOfficialCapture
     */
    public function getByOrderId($id_order)
    {
        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('braintreeofficial_order', 'bo');
        $sql->innerJoin('braintreeofficial_capture', 'bc', 'bo.`id_braintreeofficial_order` = bc.`id_braintreeofficial_order`');
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
            $paypalOrderBtIds = $this->getPayPalOrderBtId();

            if (empty($paypalOrderBtIds)) {
                return;
            }

            $collection = new \PrestaShopCollection('PaypalCapture');
            $collection->where('id_paypal_order', 'in', $paypalOrderBtIds);

            if ($collection->count() == 0) {
                return;
            }

            ProcessLoggerHandler::openLogger();
            /* @var $paypalCapture \PaypalCapture*/
            foreach ($collection->getResults() as $paypalCapture) {
                $braintreeCapture = new BraintreeOfficialCapture();
                $braintreeCapture->id = $paypalCapture->id;
                $braintreeCapture->id_capture = $paypalCapture->id_capture;
                $braintreeCapture->capture_amount = $paypalCapture->capture_amount;
                $braintreeCapture->id_braintreeofficial_order = $paypalCapture->id_paypal_order;
                $braintreeCapture->result = $paypalCapture->result;
                $braintreeCapture->date_add = $paypalCapture->date_add;
                $braintreeCapture->date_upd = $paypalCapture->date_upd;
                $braintreeCapture->save();
                try {
                    $braintreeCapture->add();
                } catch (\Exception $e) {
                    \Configuration::updateValue('BRAINTREEOFFICIAL_MIGRATION_FAILED', 1);
                    $message = 'Error while migration paypal capture. ';
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
     * @return array id PaypalOrder
     */
    public function getPayPalOrderBtId()
    {
        $paypalOrderBtIds = array();
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();

        if ($moduleManager->isInstalled('paypal') == false) {
            return $paypalOrderBtIds;
        }

        $query = new \DbQuery();
        $query->select('id_paypal_order');
        $query->from('paypal_order');
        $query->where('method="BT"');

        $result = \DB::getInstance()->executeS($query);

        if (empty($result)) {
            return $paypalOrderBtIds;
        }

        foreach ($result as $row) {
            $paypalOrderBtIds[] = $row['id_paypal_order'];
        }

        return $paypalOrderBtIds;
    }
}
