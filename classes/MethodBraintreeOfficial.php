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

use BraintreeOfficialAddons\classes\BraintreeOfficialCustomer;
use BraintreeOfficialAddons\classes\BraintreeOfficialVaulting;
use BraintreeOfficialAddons\classes\BraintreeOfficialException;
use BraintreeOfficialAddons\classes\AbstractMethodBraintreeOfficial;
use BraintreeOfficialAddons\classes\BraintreeOfficialOrder;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialCapture;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialCustomer;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialVaulting;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialOrder;

/**
 * Class MethodBT
 * @see https://developers.braintreepayments.com/guides/overview BT developper documentation
 */
class MethodBraintreeOfficial extends AbstractMethodBraintreeOfficial
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

    protected $payment_method = 'Braintree Official';

    /* @var Braintree_Gateway*/
    public $gateway;

    /* @var ServiceBraintreeOfficialCapture */
    protected $serviceBraintreeOfficialCapture;

    /* @var ServiceBraintreeOfficialCustomer*/
    protected $serviceBraintreeOfficialCustomer;

    /* @var ServiceBraintreeOfficialVaulting*/
    protected $serviceBraintreeOfficialVaulting;

    /* @var ServiceBraintreeOfficialOrder*/
    protected $serviceBraintreeOfficialOrder;

    protected $cvv_field;

    public function __construct()
    {
        $this->serviceBraintreeOfficialCapture = new ServiceBraintreeOfficialCapture();
        $this->serviceBraintreeOfficialCustomer = new ServiceBraintreeOfficialCustomer();
        $this->serviceBraintreeOfficialVaulting = new ServiceBraintreeOfficialVaulting();
        $this->serviceBraintreeOfficialOrder = new ServiceBraintreeOfficialOrder();
    }

    protected function getPaymentMethod()
    {
        $transactionDetails = $this->getDetailsTransaction();
        if ((int)\Configuration::get('BRAINTREEOFFICIAL_SANDBOX')) {
            return $transactionDetails['payment_tool'] . ' - SANDBOX';
        } else {
            return $transactionDetails['payment_tool'];
        }
    }

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
     * @see AbstractMethodBraintreeOfficial::setConfig()
     */
    public function setConfig($params)
    {
    }

    /**
     * @see AbstractMethodBraintreeOfficial::setConfig()
     */
    public function getConfig(\BraintreeOfficial $module)
    {
    }

    /**
     * @param bool $mode true if mode Sandbox and false if mode Live
     */
    public function getAllCurrency($mode = null)
    {
        $allMerchantAccounts = $this->getAllMerchantAccounts($mode);
        $result = array();
        $accountDefault = null;

        if (empty($allMerchantAccounts)) {
            return $result;
        }

        foreach ($allMerchantAccounts as $account) {
            $result[$account->currencyIsoCode] = $account->id;
            if ($account->default == true) {
                $accountDefault = $account;
            }
        }
        // Use account by default
        $result[$accountDefault->currencyIsoCode] = $accountDefault->id;

        return $result;
    }

    /**
     * @param bool $mode true if mode Sandbox and false if mode Live
     * @return array array of the Braintree\MerchantAccount objects
     */
    public function getAllMerchantAccounts($mode = null)
    {
        $this->initConfig($mode);
        $merchantAccounts = array();

        try {
            $response = $this->gateway->merchantAccount()->all();
            foreach ($response as $merchantAccount) {
                $merchantAccounts[] = $merchantAccount;
            }
        } catch (Exception $e) {
            return $merchantAccounts;
        }

        return $merchantAccounts;
    }

    /**
     * Check if the merchant account ids are right
     * @param $data array the data for the validation
     * @return array returns wrong merchant accounts [iso => merchantAccount]
     */
    public function validateMerchantAccounts($merchantAccounts)
    {
        $wrongMerchantAccounts = array();
        $allMerchantAccounts = $this->getAllMerchantAccounts();

        foreach ($merchantAccounts as $iso => $merchantAccount) {
            $match = false;

            foreach ($allMerchantAccounts as $ma) {
                if ($ma->status == 'active' && $ma->id == $merchantAccount) {
                    $match = true;
                    continue;
                }
            }

            if ($match == false) {
                $wrongMerchantAccounts[$iso] = $merchantAccount;
            }
        }

        return $wrongMerchantAccounts;
    }


    /**
     * Init class configurations
     * @param $order_mode bool mode of sandbox / live (true / false)
     */
    protected function initConfig($order_mode = null)
    {
        if ($order_mode !== null) {
            $this->mode = $order_mode ? 'SANDBOX' : 'LIVE';
        } else {
            $this->mode = Configuration::get('BRAINTREEOFFICIAL_SANDBOX') ? 'SANDBOX' : 'LIVE';
        }

        $this->gateway = new Braintree_Gateway(array('environment' => $this->mode == 'SANDBOX' ? 'sandbox' : 'production',
            'publicKey' => Configuration::get('BRAINTREEOFFICIAL_PUBLIC_KEY_' . $this->mode),
            'privateKey' => Configuration::get('BRAINTREEOFFICIAL_PRIVATE_KEY_' . $this->mode),
            'merchantId' => Configuration::get('BRAINTREEOFFICIAL_MERCHANT_ID_' . $this->mode)));
        $this->error = '';
    }

    /**
     * @see AbstractMethodBraintreeOfficial::init()
     */
    public function init()
    {
        try {
            /** @var BraintreeOfficial $module */
            $module = Module::getInstanceByName($this->name);
            $currency = $module->getPaymentCurrencyIso();
            $merchantAccountId = Configuration::get($module->getNameMerchantAccountForCurrency($currency));
            $this->initConfig();
            $clientToken = $this->gateway->clientToken()->generate(['merchantAccountId' => $merchantAccountId]);
            return $clientToken;
        } catch (Exception $e) {
            return array('error_code' => $e->getCode(), 'error_msg' => $e->getMessage());
        }
    }

    /**
    * @return bool True if account is configured
    */
    public function isConfigured()
    {
        $initialModule = $this->init();
        if (isset($initialModule['error_code'])) {
            return false;
        }
        return true;
    }

    /**
     * @see AbstractMethodBraintreeOfficial::validation()
     */
    public function validation()
    {
        /* @var $module BraintreeOfficial*/
        $module = Module::getInstanceByName('braintreeofficial');
        $transaction = $this->sale(context::getContext()->cart, $this->payment_method_nonce);

        if (!$transaction) {
            throw new Exception('Error during transaction validation', '00000');
        }

        $this->setDetailsTransaction($transaction);
        $order_state = $this->getOrderState($transaction);

        $module->validateOrder(
            context::getContext()->cart->id,
            $order_state,
            $transaction->amount,
            $this->getPaymentMethod(),
            $module->l('Payment accepted.', get_class($this)),
            $this->getDetailsTransaction(),
            context::getContext()->cart->id_currency,
            false,
            context::getContext()->customer->secure_key
        );
    }

    public function getOrderState($transaction)
    {
        if ((int)Configuration::get('BRAINTREEOFFICIAL_CUSTOMIZE_ORDER_STATUS')) {
            if (Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == "sale" && $transaction->paymentInstrumentType == "paypal_account" && $transaction->status == "settling") { // or submitted for settlement?
                $orderState = (int)Configuration::get('BRAINTREEOFFICIAL_OS_PROCESSING');
            } elseif ((Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == "sale" && $transaction->paymentInstrumentType == "paypal_account" && $transaction->status == "settled")
                || (Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == "sale" && $transaction->paymentInstrumentType == "credit_card")) {
                $orderState = (int)Configuration::get('BRAINTREEOFFICIAL_OS_ACCEPTED_TWO');
            } else {
                $orderState = (int)Configuration::get('BRAINTREEOFFICIAL_OS_PENDING');
            }
        } else {
            if (Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == "sale" && $transaction->paymentInstrumentType == "paypal_account" && $transaction->status == "settling") { // or submitted for settlement?
                $orderState = (int)Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING_VALIDATION');
            } elseif ((Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == "sale" && $transaction->paymentInstrumentType == "paypal_account" && $transaction->status == "settled")
                || (Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == "sale" && $transaction->paymentInstrumentType == "credit_card")) {
                $orderState = (int)Configuration::get('PS_OS_PAYMENT');
            } else {
                $orderState = (int)Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING');
            }
        }

        return $orderState;
    }

    public function setDetailsTransaction($transaction)
    {
        if ($transaction->paymentInstrumentType == 'credit_card') {
            $paymentTool = $transaction->creditCardDetails->cardType;
        } else {
            $paymentTool = 'PayPal';
        }

        $this->transaction_detail = array(
            'currency' => pSQL($transaction->currencyIsoCode),
            'transaction_id' => pSQL($transaction->id),
            'payment_method' => $transaction->type,
            'payment_status' => $transaction->status,
            'id_payment' => $this->payment_method_nonce,
            'capture' => $transaction->status == "authorized" ? true : false,
            'payment_tool' => $paymentTool,
            'date_transaction' => $this->getDateTransaction($transaction)
        );
    }

    public function getDateTransaction($transaction)
    {
        return $transaction->updatedAt->format('Y-m-d H:i:s');
    }


    /**
     * Capture authorized transaction
     * @param $braintree_order BraintreeOfficialOrder object
     * @return array|Exception
     */
    public function confirmCapture($braintree_order)
    {
        try {
            $this->initConfig($braintree_order->sandbox);
            $result = $this->gateway->transaction()->submitForSettlement($braintree_order->id_transaction, number_format($braintree_order->total_paid, 2, ".", ''));
            if ($result instanceof Braintree_Result_Successful && $result->success) {
                $this->serviceBraintreeOfficialCapture->updateCapture($result->transaction->id, $result->transaction->amount, $result->transaction->status, $braintree_order->id);
                $response =  array(
                    'success' => true,
                    'authorization_id' => $result->transaction->id,
                    'status' => $result->transaction->status,
                    'amount' => $result->transaction->amount,
                    'currency' => $result->transaction->currencyIsoCode,
                    'payment_type' => isset($result->transaction->payment_type) ? $result->transaction->payment_type : '',
                    'merchantAccountId' => $result->transaction->merchantAccountId,
                    'date_transaction' => $this->getDateTransaction($result->transaction)
                );
            } elseif ($result->transaction->status == Braintree_Transaction::SETTLEMENT_DECLINED) {
                $order = new Order(Tools::getValue('id_order'));
                $order->setCurrentState(Configuration::get('PS_OS_ERROR'));
            } else {
                $errors = $result->errors->deepAll();

                foreach ($errors as $error) {
                    $response = array(
                        'transaction_capture_id' => $result->transaction->id,
                        'status' => $result->transaction->status,
                        'error_code' => $error->code,
                        'error_message' => $error->message,
                    );
                    if ($error->code == Braintree_Error_Codes::TRANSACTION_CANNOT_SUBMIT_FOR_SETTLEMENT) {
                        $response['already_captured'] = true;
                    }
                }
            }
            return $response;
        } catch (Exception $e) {
            $response =  array(
                'error_message' => $e->getCode().'=>'.$e->getMessage(),
            );
            return $response;
        }
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function searchTransactions($braintreeOrders)
    {
        $collection = array();
        foreach ($braintreeOrders as $braintreeOrder) {
            $transaction = $this->searchTransaction($braintreeOrder);
            if ($transaction === false) {
                continue;
            }
            $collection[] = $transaction;
        }
        return $collection;
    }

    /**
     * @param BraintreeOfficialOrder $braintreeOrder
     * @return mixed
     */
    public function searchTransaction($braintreeOrder)
    {
        $this->initConfig($braintreeOrder->sandbox);
        try {
            $transaction = $this->gateway->transaction()->find($braintreeOrder->id_transaction);
            return $transaction;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create new BT account for currency added on PS
     * @param string $currency iso code
     * @return array [curr_iso_code => account_id]
     */
    public function createForCurrency($currency = null)
    {
        $this->initConfig();
        $result = array();

        if ($currency) {
            try {
                $response = $this->gateway->merchantAccount()->createForCurrency(array(
                    'currency' => $currency,
                ));
                if ($response->success) {
                    $result[$response->merchantAccount->currencyIsoCode] = $response->merchantAccount->id;
                }
            } catch (Exception $e) {
            }
        } else {
            $currencies = Currency::getCurrencies();
            foreach ($currencies as $curr) {
                try {
                    $response = $this->gateway->merchantAccount()->createForCurrency(array(
                        'currency' => $curr['iso_code'],
                    ));
                    if ($response->success) {
                        $result[$response->merchantAccount->currencyIsoCode] = $response->merchantAccount->id;
                    }
                } catch (Exception $e) {
                }
            }
        }

        return $result;
    }

    /**
     * Deleted vaulted method from BT
     * @param $payment_method BraintreeOfficialVaulting
     */
    public function deleteVaultedMethod($payment_method)
    {
        $this->initConfig();
        $this->gateway->paymentMethod()->delete($payment_method->token);
    }

    /**
     * Refund settled transaction
     * @param $orderBraintree BraintreeOfficialOrder object
     * @return mixed
     */
    public function refund($orderBraintree)
    {
        try {
            $this->initConfig($orderBraintree->sandbox);
            $capture = $this->serviceBraintreeOfficialCapture->loadByOrderBraintreeId($orderBraintree->id);
            $id_transaction = Validate::isLoadedObject($capture) ? $capture->id_capture : $orderBraintree->id_transaction;

            $result = $this->gateway->transaction()->refund($id_transaction, number_format($orderBraintree->total_paid, 2, ".", ''));
            if ($result->success) {
                $response =  array(
                    'success' => true,
                    'refund_id' => $result->transaction->refundedTransactionId,
                    'transaction_id' => $result->transaction->id,
                    'status' => $result->transaction->status,
                    'amount' => $result->transaction->amount,
                    'currency' => $result->transaction->currencyIsoCode,
                    'payment_type' => $result->transaction->payment_type,
                    'merchantAccountId' => $result->transaction->merchantAccountId,
                    'date_transaction' => $this->getDateTransaction($result->transaction)
                );
            } elseif ($result->transaction->status == Braintree_Transaction::SETTLEMENT_DECLINED) {
                $order = new Order(Tools::getValue('id_order'));
                $order->setCurrentState(Configuration::get('PS_OS_ERROR'));
                $response =  array(
                    'transaction_id' => $result->params['id'],
                    'error_message' => $result->message,
                );
            } else {
                $errors = $result->errors->deepAll();
                foreach ($errors as $error) {
                    $response = array(
                        'transaction_id' => $result->transaction->refundedTransactionId,
                        'status' => 'Failure',
                        'error_code' => $error->code,
                        'error_message' => $error->message,
                    );
                    if ($error->code == Braintree_Error_Codes::TRANSACTION_HAS_ALREADY_BEEN_REFUNDED) {
                        $response['already_refunded'] = true;
                    }
                }
            }
            return $response;
        } catch (Exception $e) {
            $response =  array(
                'error_message' => $e->getCode().'=>'.$e->getMessage(),
            );
            return $response;
        }
    }

    /**
     * @see AbstractMethodBraintree::partialRefund()
     */
    public function partialRefund($params)
    {
        try {
            $braintreeOrder = $this->serviceBraintreeOfficialOrder->loadByOrderId(Tools::getValue('id_order'));
            $this->initConfig($braintreeOrder->sandbox);
            $capture = $this->serviceBraintreeOfficialCapture->loadByOrderBraintreeId($braintreeOrder->id);
            $id_transaction = Validate::isLoadedObject($capture) ? $capture->id_capture : $braintreeOrder->id_transaction;
            $amount = 0;
            foreach ($params['productList'] as $product) {
                $amount += $product['amount'];
            }
            if (Tools::getValue('partialRefundShippingCost')) {
                $amount += (int)Tools::getValue('partialRefundShippingCost');
            }
            $result = $this->gateway->transaction()->refund($id_transaction, number_format($amount, 2, ".", ''));

            if ($result->success) {
                $response =  array(
                    'success' => true,
                    'refundedTransactionId' => $result->transaction->refundedTransactionId,
                    'refund_id' => $result->transaction->id,
                    'status' => $result->transaction->status,
                    'amount' => $result->transaction->amount,
                    'currency' => $result->transaction->currencyIsoCode,
                    'payment_type' => $result->transaction->payment_type,
                    'merchantAccountId' => $result->transaction->merchantAccountId,
                );
                $braintreeOrder->total_paid -= $amount;
                if ($braintreeOrder->total_paid == 0) {
                    $braintreeOrder->payment_status = 'refunded';
                }
                $braintreeOrder->save();
                if (Validate::isLoadedObject($capture)) {
                    $capture->capture_amount = $braintreeOrder->total_paid;
                    $capture->result = $braintreeOrder->payment_status;
                    $capture->save();
                }
            } else {
                $errors = $result->errors->deepAll();
                foreach ($errors as $error) {
                    $response = array(
                        'refundedTransactionId' => $result->transaction->refundedTransactionId,
                        'status' => 'Failure',
                        'error_code' => $error->code,
                        'error_message' => $error->message,
                    );
                    if ($error->code == Braintree_Error_Codes::TRANSACTION_HAS_ALREADY_BEEN_REFUNDED) {
                        $response['already_refunded'] = true;
                    }
                }
            }
            return $response;
        } catch (Exception $e) {
            $response =  array(
                'error_message' => $e->getCode().'=>'.$e->getMessage(),
            );
            return $response;
        }
    }

    /**
     * Get current Transaction status from BT
     * @param BraintreeOfficialOrder $orderBraintree
     * @return string|boolean
     */
    public function getTransactionStatus($orderBraintree)
    {
        $this->initConfig($orderBraintree->sandbox);
        try {
            $result = $this->gateway->transaction()->find($orderBraintree->id_transaction);
            return $result->status;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @see AbstractMethodBraintreeOfficial::void()
     */
    public function void($orderBraintree)
    {
        $this->initConfig($orderBraintree->sandbox);
        try {
            $result = $this->gateway->transaction()->void($orderBraintree->id_transaction);
            if ($result instanceof Braintree_Result_Successful && $result->success) {
                $response =  array(
                    'success' => true,
                    'transaction_id' => $result->transaction->id,
                    'status' => $result->transaction->status,
                    'amount' => $result->transaction->amount,
                    'currency' => $result->transaction->currencyIsoCode,
                    'date_transaction' => $this->getDateTransaction($result->transaction)
                );
            } elseif ($result->transaction->status == Braintree_Transaction::SETTLEMENT_DECLINED) {
                $order = new Order(Tools::getValue('id_order'));
                $order->setCurrentState(Configuration::get('PS_OS_ERROR'));
                $response =  array(
                    'transaction_id' => $result->params['id'],
                    'error_message' => $result->message,
                );
            } else {
                $response =  array(
                    'transaction_id' => $result->params['id'],
                    'error_message' => $result->message,
                );
            }
            return $response;
        } catch (Exception $e) {
            $response =  array(
                'error_message' => $e->getCode().'=>'.$e->getMessage(),
            );
            return $response;
        }
    }


    /**
     * @see AbstractMethodBraintreeOfficial::getLinkToTransaction()
     */
    public function getLinkToTransaction($log)
    {
        if ($log->sandbox) {
            $url = 'https://sandbox.braintreegateway.com/merchants/' . Configuration::get('BRAINTREEOFFICIAL_MERCHANT_ID_SANDBOX', null, null, $log->id_shop) . '/transactions/';
        } else {
            $url = 'https://www.braintreegateway.com/merchants/' . Configuration::get('BRAINTREEOFFICIAL_MERCHANT_ID_LIVE', null, null, $log->id_shop) . '/transactions/';
        }
        return $url . $log->id_transaction;
    }

    /**
     * Create payment method nonce
     * @param $token
     * @return mixed
     */
    public function createMethodNonce($token)
    {
        $this->initConfig();
        $nonce = $this->gateway->paymentMethodNonce()->create($token);
        return $nonce->paymentMethodNonce->nonce;
    }

    /**
     * @param $cart Cart
     * @param $token_payment
     * @return bool|mixed
     * @throws Exception
     * @throws BraintreeOfficialException
     */
    public function sale($cart, $token_payment)
    {
        /* @var $module BraintreeOfficial*/
        $this->initConfig();
        $bt_method = $this->payment_method_bt;
        $vault_token = '';
        $use3dVerification = (int)Configuration::get('BRAINTREEOFFICIAL_3DSECURE');
        $use3dVerification &= (int)Configuration::get('BRAINTREEOFFICIAL_3DSECURE_AMOUNT') <= $cart->getOrderTotal();

        if ($bt_method == BRAINTREE_PAYPAL_PAYMENT) {
            $options = array(
                'submitForSettlement' => Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == "sale" ? true : false,
                'threeDSecure' => array(
                    'required' => $use3dVerification
                )
            );
        } else {
            $options = array(
                'submitForSettlement' => Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == "sale" ? true : false,
            );
        }

        $address_billing = new Address($cart->id_address_invoice);
        $country_billing = new Country($address_billing->id_country);
        $address_shipping = new Address($cart->id_address_delivery);
        $country_shipping = new Country($address_shipping->id_country);
        $amount = $this->formatPrice($cart->getOrderTotal());
        $module = Module::getInstanceByName($this->name);
        $currency = $module->getPaymentCurrencyIso();
        $iso_state = '';
        if ($address_shipping->id_state) {
            $state = new State((int) $address_shipping->id_state);
            $iso_state = $state->iso_code;
        }

        $data = array(
            'amount'                => $amount,
            'merchantAccountId'     => Configuration::get($module->getNameMerchantAccountForCurrency($currency)),
            'orderId'               => $this->getOrderId($cart),
            'channel'               => (getenv('PLATEFORM') == 'PSREAD')?'PrestaShop_Cart_Ready_Braintree':'PrestaShop_Cart_Braintree',
            'billing' => array(
                'firstName'         => $address_billing->firstname,
                'lastName'          => $address_billing->lastname,
                'company'           => $address_billing->company,
                'streetAddress'     => $address_billing->address1,
                'extendedAddress'   => $address_billing->address2,
                'locality'          => $address_billing->city,
                'postalCode'        => $address_billing->postcode,
                'countryCodeAlpha2' => $country_billing->iso_code,
                'region'            => $iso_state,
            ),
            'shipping' => array(
                'firstName'         => $address_shipping->firstname,
                'lastName'          => $address_shipping->lastname,
                'company'           => $address_shipping->company,
                'streetAddress'     => $address_shipping->address1,
                'extendedAddress'   => $address_shipping->address2,
                'locality'          => $address_shipping->city,
                'postalCode'        => $address_shipping->postcode,
                'countryCodeAlpha2' => $country_shipping->iso_code,
                'region'            => $iso_state,
            ),
            "deviceData"            => '',
        );

        $braintree_customer = $this->serviceBraintreeOfficialCustomer->loadCustomerByMethod(Context::getContext()->customer->id, (int)Configuration::get('BRAINTREEOFFICIAL_SANDBOX'));
        if (Validate::isLoadedObject($braintree_customer) == false) {
            $braintree_customer = $this->createCustomer();
        } else {
            $this->updateCustomer($braintree_customer);
        }

        if (Configuration::get('BRAINTREEOFFICIAL_VAULTING')) {
            if ($bt_method == BRAINTREE_CARD_PAYMENT) {
                $vault_token = $this->bt_vaulting_token;
            } elseif ($bt_method == BRAINTREE_PAYPAL_PAYMENT) {
                $vault_token = $this->pbt_vaulting_token;
            }

            if ($vault_token && $braintree_customer->id) {
                $vaultExists = $this->serviceBraintreeOfficialVaulting->vaultingExist($vault_token, $braintree_customer->id);
                if ($vaultExists && $this->payment_method_bt == 'paypal-braintree') {
                    $data['paymentMethodToken'] = $vault_token;
                } elseif ($vaultExists) {
                    if ($module->use3dVerification() == false) {
                        if (empty($this->cvv_field)) {
                            $error_msg = $module->l('Card verification failed.', get_class($this));

                            throw new Exception($error_msg, '00000');
                        }

                        $params = array(
                            'cvv' => $this->cvv_field,
                            'options' => [
                                'verifyCard' => true
                            ]
                        );
                        $payment_method = $this->gateway->paymentMethod()->update($vault_token, $params);

                        if ((isset($payment_method->verification) && $payment_method->verification == null) ||
                            (isset($payment_method->verification) && $payment_method->verification->status != 'verified')) {
                            $error_msg = $module->l('Card verification failed.', get_class($this));
                            $error_msg .= $module->l('The reason : ', get_class($this)).' '.$payment_method->message;

                            throw new Exception($error_msg, '00000');
                        }
                    }

                    $data['paymentMethodNonce'] = $token_payment;
                }
            } else {
                if ($this->save_card_in_vault || $this->save_account_in_vault) {
                    if ($this->save_card_in_vault && $use3dVerification == false) {
                        $payment_method = $this->gateway->paymentMethod()->create(array(
                            'customerId' => $braintree_customer->reference,
                            'paymentMethodNonce' => $token_payment,
                            'options' => array('verifyCard' => true),
                        ));

                        if (isset($payment_method->verification) && $payment_method->verification != null &&
                            isset($payment_method->verification->status) && $payment_method->verification->status != 'verified') {
                            $error_msg = $module->l('Card verification respond with status', get_class($this)).' '.$payment_method->verification->status.'. ';
                            $error_msg .= $module->l('The reason : ', get_class($this)).' '.$payment_method->verification->processorResponseText.'. ';

                            if ($payment_method->verification->gatewayRejectionReason) {
                                $error_msg .= $module->l('Rejection reason : ', get_class($this)).' '.$payment_method->verification->gatewayRejectionReason;
                            }
                            throw new BraintreeOfficialException('00000', $error_msg);
                        }

                        if ($payment_method instanceof Braintree\Result\Error) {
                            $error_msg = $module->l('Card verification is failed. ', get_class($this));
                            $error_msg .= $module->l('The reason : ', get_class($this)).' '.$payment_method->message.'. ';

                            throw new Exception($error_msg, '00000');
                        }

                        $paymentMethodToken = $payment_method->paymentMethod->token;
                    }
                    $options['storeInVaultOnSuccess'] = true;
                }
                if (isset($paymentMethodToken)) {
                    $data['paymentMethodToken'] = $paymentMethodToken;
                } else {
                    $data['paymentMethodNonce'] = $token_payment;
                }
            }
        } else {
            $data['paymentMethodNonce'] = $token_payment;
        }

        $data['options'] = $options;
        $data['customerId'] = $braintree_customer->reference;

        try {
            $result = $this->gateway->transaction()->sale($data);
        } catch (Braintree\Exception\Authorization $e) {
            throw new BraintreeOfficialException('00000', 'Authorization exception: please try to pay again or contact customer support');
        }

        if (($result instanceof Braintree_Result_Successful) && $result->success && $this->isValidStatus($result->transaction->status)) {
            if (Configuration::get('BRAINTREEOFFICIAL_VAULTING')
                && (($this->save_card_in_vault && $bt_method == BRAINTREE_CARD_PAYMENT)
                    || ($this->save_account_in_vault && $bt_method == BRAINTREE_PAYPAL_PAYMENT))
                && $this->serviceBraintreeOfficialVaulting->vaultingExist($result->transaction->creditCard['token'], $braintree_customer->id) == false) {
                $this->createVaulting($result, $braintree_customer);
            }

            Context::getContext()->cookie->__unset('payment_method_nonce');
            Context::getContext()->cookie->__unset('brainteeofficial_payer_email');
            Context::getContext()->cookie->__unset('payment_method_bt');

            return $result->transaction;
        } else {
            $errors = $result->errors->deepAll();
            if ($errors) {
                throw new BraintreeOfficialException($errors[0]->code, $errors[0]->message);
            } else {
                throw new BraintreeOfficialException($result->transaction->processorResponseCode, $result->message);
            }
        }

        return false;
    }

    public function formatPrice($price)
    {
        /* @var $module BraintreeOfficial*/
        if (is_float($price) == false) {
            $price = (float)$price;
        }

        $context = Context::getContext();
        $context_currency = $context->currency;
        $module = Module::getInstanceByName($this->name);
        if ($id_currency_to = $module->needConvert()) {
            $currency_to_convert = new Currency($id_currency_to);
            $price = Tools::ps_round(Tools::convertPriceFull($price, $context_currency, $currency_to_convert), _PS_PRICE_COMPUTE_PRECISION_);
        }
        return $price;
    }

    /**
     * Get order id for BT sale. Use secure key to avoid duplicate orderId error.
     * @param object $cart Cart
     * @return string
     */
    public function getOrderId($cart)
    {
        return $cart->secure_key.'_'.$cart->id;
    }

    /**
     * Create new customer on BT and PS
     * @return object BraintreeOfficialCustomer
     */
    public function createCustomer()
    {
        $context = Context::getContext();
        $data = array(
            'firstName' => $context->customer->firstname,
            'lastName' => $context->customer->lastname,
            'email' => $context->customer->email
        );

        $result = $this->gateway->customer()->create($data);

        $profileKey = $this->getProfileKey();

        $customer = new BrainTreeOfficialCustomer();
        $customer->id_customer = $context->customer->id;
        $customer->reference = $result->customer->id;
        $customer->sandbox = (int)Configuration::get('BRAINTREEOFFICIAL_SANDBOX');
        $customer->profile_key = pSQL($profileKey);
        $customer->save();
        return $customer;
    }

    /**
     * @param bool $sandbox
     * @return string
     */
    public function getProfileKey($sandbox = null)
    {
        if ($sandbox === null) {
            $sandbox = (int)Configuration::get('BRAINTREEOFFICIAL_SANDBOX');
        }

        if (($this->gateway instanceof Braintree\Gateway) == false) {
            try {
                $this->initConfig((int)$sandbox);
            } catch (Exception $e) {
                return '';
            }
        }

        $profileKey = md5($this->gateway->config->getMerchantId());

        return $profileKey;
    }

    /**
     * Update customer info on BT
     * @param BraintreeOfficialCustomer $braintree_customer
     * @throws Exception
     */
    public function updateCustomer($braintree_customer)
    {
        $context = Context::getContext();
        $data = array(
            'firstName' => $context->customer->firstname,
            'lastName' => $context->customer->lastname,
            'email' => $context->customer->email
        );
        try {
            $this->gateway->customer()->update($braintree_customer->reference, $data);
        } catch (Braintree\Exception\NotFound $e) {
            $braintree_customer->sandbox = !$braintree_customer->sandbox;
            $braintree_customer->save();
            $module = Module::getInstanceByName($this->name);
            $mode  = Configuration::get('BRAINTREEOFFICIAL_SANDBOX') ? 'Sandbox' : 'Live';
            $mode2  = !Configuration::get('BRAINTREEOFFICIAL_SANDBOX') ? 'Sandbox' : 'Live';
            $msg = sprintf($module->l('This client is not found in %s mode.', get_class($this)), $mode);
            $msg .= sprintf($module->l('Probably this customer has been already created in %s mode. Please create new prestashop client for this mode.', get_class($this)), $mode2);
            throw new Exception($msg);
        }
    }

    /**
     * Check if status is valid for vaulting
     * @param $status
     * @return bool
     */
    public function isValidStatus($status)
    {
        return in_array($status, array('submitted_for_settlement','authorized','settled', 'settling'));
    }
    /**
     * Add BraintreeOfficialVaulting
     * @param object $result Braintree\Result\Successful object
     * @param object $braintree_customer BraintreeOfficialCustomer
     */
    public function createVaulting($result, $braintree_customer)
    {
        $vaulting = new BraintreeOfficialVaulting();
        $vaulting->id_braintreeofficial_customer = $braintree_customer->id;
        $vaulting->payment_tool = $this->payment_method_bt;
        if ($vaulting->payment_tool == BRAINTREE_CARD_PAYMENT) {
            $vaulting->token = $result->transaction->creditCard['token'];
            $vaulting->info = $result->transaction->creditCard['cardType'].': *';
            $vaulting->info .= $result->transaction->creditCard['last4'].' ';
            $vaulting->info .= $result->transaction->creditCard['expirationMonth'].'/';
            $vaulting->info .= $result->transaction->creditCard['expirationYear'];
        } elseif ($vaulting->payment_tool == BRAINTREE_PAYPAL_PAYMENT) {
            $vaulting->token = $result->transaction->paypal['token'];
            $vaulting->info = $result->transaction->paypal['payerFirstName'].' ';
            $vaulting->info .= $result->transaction->paypal['payerLastName'].' ';
            $vaulting->info .= $result->transaction->paypal['payerEmail'];
        }
        $vaulting->save();
    }

    /**
     * @return array
     * */
    public function getShortcutJsVars($page)
    {
        switch ($page) {
            case BRAINTREE_CART_PAGE:
                $amount = Context::getContext()->cart->getOrderTotal();
                break;
            case BRAINTREE_PRODUCT_PAGE:
                $product = new Product((int)Tools::getValue('id_product'));
                $idProductAttribute = (int)Tools::getValue('id_product_attribute');

                if (Validate::isLoadedObject($product)) {
                    $amount = $product->getPrice(
                        true,
                        $idProductAttribute ? $idProductAttribute : 0,
                        6,
                        null,
                        false,
                        true
                    );
                } else {
                    $amount = 0;
                }

                break;
            default:
                $amount = 0;
        }

        $clientToken = $this->init();
        $tplVars = array(
            'paypal_braintree_authorization' => $clientToken,
            'paypal_braintree_amount' => Tools::ps_round($amount, _PS_PRICE_DISPLAY_PRECISION_),
            'paypal_braintree_mode' => $this->mode == 'SANDBOX' ? 'sandbox' : 'production',
            'paypal_braintree_currency' => Context::getContext()->currency->iso_code,
            'paypal_braintree_contoller' => Context::getContext()->link->getModuleLink($this->name, 'shortcut'),
            'paypal_braintree_id_product' => Tools::isSubmit('id_product') ? (int)Tools::getValue('id_product') : null,
            'paypal_braintree_id_product_attribute' => (int)Tools::getValue('id_product_attribute'),
            'paypal_braintree_quantity' => 1,
            'paypal_braintree_page' => $page,
            'paypal_braintree_locale' => str_replace("-", "_", Context::getContext()->language->locale)
        );

        return $tplVars;
    }
}
