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

use BraintreeAddons\classes\AbstractMethodBraintree;
require_once _PS_MODULE_DIR_ . 'braintree/controllers/front/abstract.php';

/**
 * Validate BT payment
 */
class BraintreeValidationModuleFrontController extends BraintreeAbstarctModuleFrontController
{
    public function init()
    {
        parent::init();
        $this->values['payment_method_nonce'] = Tools::getvalue('payment_method_nonce');
        $this->values['payment_method_bt'] = Tools::getvalue('payment_method_bt');
        $this->values['bt_vaulting_token'] = Tools::getvalue('bt_vaulting_token');
        $this->values['pbt_vaulting_token'] = Tools::getvalue('pbt_vaulting_token');
        $this->values['save_card_in_vault'] = Tools::getvalue('save_card_in_vault');
        $this->values['save_account_in_vault'] = Tools::getvalue('save_account_in_vault');
    }

    public function postProcess()
    {
        /* @var $method_bt MethodBraintree*/
        $method_bt = AbstractMethodBraintree::load('Braintree');
        try {
            $method_bt->setParameters($this->values);
            $method_bt->validation();
            $cart = Context::getContext()->cart;
            $customer = new Customer($cart->id_customer);
            $module = Module::getInstanceByName($this->name);
            $this->redirectUrl = 'index.php?controller=order-confirmation&id_cart=' . $cart->id .'&id_module=' . $module->id .'&key='.$customer->secure_key;
        } catch (BraintreeAddons\classes\BraintreeException $e) {
            $this->errors['error_code'] = $e->getCode();
            $this->errors['error_msg'] = $e->getMessage();
            $this->errors['msg_long'] = $e->getMessageLong();
        } catch (Exception $e) {
            $this->errors['error_code'] = $e->getCode();
            $this->errors['error_msg'] = $e->getMessage();
        } finally {
            $this->transaction_detail = $method_bt->getDetailsTransaction();
        }

        if (!empty($this->errors)) {
            $this->redirectUrl = Context::getContext()->link->getModuleLink($this->name, 'error', $this->errors);
        }
    }
}
