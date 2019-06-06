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


/**
 * Class MethodBT
 * @see https://developers.braintreepayments.com/guides/overview BT developper documentation
 */
class MethodBraintree extends AbstractMethodBraintree
{
    /** @var string token*/
    public $token;

    /** @var string sandbox or live*/
    public $mode;

    /** @var  string A secure, one-time-use reference to payment information */
    private $payment_method_nonce;

    /** @var  string  BT_CARD_PAYMENT or BT_PAYPAL_PAYMENT*/
    private $payment_method_bt;

    /** @var  string Vaulted token for cards */
    private $bt_vaulting_token;

    /** @var  string Vaulted token for paypal */
    private $pbt_vaulting_token;

    /** @var  bool vaulting checkbox */
    private $save_card_in_vault;

    /** @var  bool vaulting checkbox */
    private $save_account_in_vault;

    protected $payment_method = 'Braintree';

    /**
     * @param $values array replace for tools::getValues()
     */
    public function setParameters($values)
    {
        foreach ($values as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @see AbstractMethodBraintree::setConfig()
     */
    public function setConfig($params)
    {

    }

    /**
     * @see AbstractMethodBraintree::setConfig()
     */
    public function getConfig(\Braintree $module)
    {

    }


    /**
     * Init class configurations
     */
    private function initConfig($order_mode = null)
    {
        if ($order_mode !== null) {
            $this->mode = $order_mode ? 'SANDBOX' : 'LIVE';
        } else {
            $this->mode = Configuration::get('BRAINTREE_SANDBOX') ? 'SANDBOX' : 'LIVE';
        }
        $this->gateway = new Braintree_Gateway(array('environment' => $this->mode == 'SANDBOX' ? 'sandbox' : 'production',
            'publicKey' => Configuration::get('BRAINTREE_PUBLIC_KEY_' . $this->mode),
            'privateKey' => Configuration::get('BRAINTREE_PRIVATE_KEY_' . $this->mode),
            'merchantId' => Configuration::get('BRAINTREE_MERCHANT_ID_' . $this->mode)));
        $this->error = '';
    }

    /**
     * @see AbstractMethodBraintree::init()
     */
    public function init()
    {
        try {
            $this->initConfig();
            $clientToken = $this->gateway->clientToken()->generate();
            return $clientToken;
        } catch (Exception $e) {
            \Symfony\Component\VarDumper\VarDumper::dump(array($e->getMessage(), $e->getFile(), $e->getLine())); die;
            return array('error_code' => $e->getCode(), 'error_msg' => $e->getMessage());
        }
    }

    /**
     * @see AbstractMethodBraintree::validation()
     */
    public function validation()
    {

    }

    public function setDetailsTransaction($transaction)
    {

    }

    /**
     * @see AbstractMethodBraintree::confirmCapture()
     */
    public function confirmCapture($paypal_order)
    {

    }

    /**
     * @see AbstractMethodBraintree::refund()
     */
    public function refund($paypal_order)
    {

    }

    /**
     * @see AbstractMethodBraintree::partialRefund()
     */
    public function partialRefund($params)
    {

    }

    /**
     * @see AbstractMethodBraintree::void()
     */
    public function void($orderPayPal)
    {

    }


    /**
     * @see AbstractMethodBraintree::getLinkToTransaction()
     */
    public function getLinkToTransaction($id_transaction, $sandbox)
    {

    }

}
