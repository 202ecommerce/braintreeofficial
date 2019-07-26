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

require_once(_PS_MODULE_DIR_ . 'braintree/vendor/autoload.php');

if (!defined('_PS_VERSION_')) {
    exit;
}


use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use BraintreeAddons\services\ServiceBraintreeOrder;
use BraintreeAddons\services\ServiceBraintreeCapture;
use BraintreeAddons\services\ServiceBraintreeVaulting;
use BraintreePPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use BraintreePPBTlib\Extensions\ProcessLogger\ProcessLoggerExtension;
use BraintreeAddons\classes\BraintreeOrder;
use BraintreeAddons\classes\BraintreeCapture;
use BraintreeAddons\classes\BraintreeCustomer;
use BraintreeAddons\classes\BraintreeVaulting;
use BraintreeAddons\classes\AbstractMethodBraintree;
use BraintreeAddons\classes\BraintreeLog;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use BraintreePPBTlib\Install\ModuleInstaller;
use BraintreePPBTlib\Extensions\AbstractModuleExtension;

const BRAINTREE_CARD_PAYMENT = 'card-braintree';
const BRAINTREE_PAYPAL_PAYMENT = 'paypal-braintree';
const BRAINTREE_PAYMENT_CUSTOMER_CURRENCY = -1;

class Braintree extends \PaymentModule
{
    /**
     * List of hooks used in this Module
     */
    public $hooks = array(
        'paymentOptions',
        'displayOrderConfirmation',
        'displayAdminOrder',
        'actionOrderStatusPostUpdate',
        'actionOrderStatusUpdate',
        'header',
        'actionObjectCurrencyAddAfter',
        'displayBackOfficeHeader',
        'displayInvoiceLegalFreeText',
        'actionAdminControllerSetMedia',
        'displayMyAccountBlock',
        'displayCustomerAccount',
        'actionOrderSlipAdd',
        'displayAdminOrderTabOrder',
        'displayAdminOrderContentOrder',
        'displayAdminCartsView'
    );

    /**
     * List of ppbtlib extentions
     */
    public $extensions = array(
        BraintreePPBTlib\Extensions\ProcessLogger\ProcessLoggerExtension::class,
    );

    /**
     * List of objectModel used in this Module
     * @var array
     */
    public $objectModels = array(
        BraintreeCapture::class,
        BraintreeOrder::class,
        BraintreeVaulting::class,
        BraintreeCustomer::class
    );

    /**
     * List of admin tabs used in this Module
     */
    public $moduleAdminControllers = array(
        array(
            'name' => array(
                'en' => 'Braintree Official',
                'fr' => 'Braintree Officiel'
            ),
            'class_name' => 'AdminParentBraintreeConfiguration',
            'parent_class_name' => 'SELL',
            'visible' => true,
            'icon' => 'payment'
        ),
        array(
            'name' => array(
                'en' => 'Configuration',
                'fr' => 'Configuration'
            ),
            'class_name' => 'AdminBraintreeConfiguration',
            'parent_class_name' => 'AdminParentBraintreeConfiguration',
            'visible' => true,
        ),
        array(
            'name' => array(
                'en' => 'Setup',
                'fr' => 'Setup'
            ),
            'class_name' => 'AdminBraintreeSetup',
            'parent_class_name' => 'AdminBraintreeConfiguration',
            'visible' => true,
        ),
        array(
            'name' => array(
                'en' => 'Customize checkout experience',
                'fr' => 'Customize checkout experience'
            ),
            'class_name' => 'AdminBraintreeCustomizeCheckout',
            'parent_class_name' => 'AdminBraintreeConfiguration',
            'visible' => true,
        ),
        array(
            'name' => array(
                'en' => 'Help',
                'fr' => 'Help'
            ),
            'class_name' => 'AdminBraintreeHelp',
            'parent_class_name' => 'AdminBraintreeConfiguration',
            'visible' => true,
        ),
        array(
            'name' => array(
                'en' => 'Logs',
                'fr' => 'Logs'
            ),
            'class_name' => 'AdminBraintreeLogs',
            'parent_class_name' => 'AdminBraintreeConfiguration',
            'visible' => true,
        ),
        array(
            'name' => array(
                'en' => 'Report',
                'fr' => 'Rapport'
            ),
            'class_name' => 'AdminBraintreeStats',
            'parent_class_name' => 'AdminParentBraintreeConfiguration',
            'visible' => true,
        ),
        array(
            'name' => array(
                'en' => 'Migration',
                'fr' => 'Migration'
            ),
            'class_name' => 'AdminBraintreeMigration',
            'parent_class_name' => 'AdminParentBraintreeConfiguration',
            'visible' => false,
        )
    );

    /* @var ServiceBraintreeOrder*/
    protected $serviceBraintreeOrder;

    /* @var ServiceBraintreeCapture*/
    protected $serviceBraintreeCapture;

    /* @var ServiceBraintreeVaulting*/
    protected $serviceBraintreeVaulting;

    /* @var MethodBraintree*/
    protected $methodBraintree;

    public function __construct()
    {
        $this->name = 'braintree';
        $this->tab = 'payments_gateways';
        $this->version = '@version@';
        $this->author = 'PrestaShop';
        $this->display = 'view';
        $this->module_key = '336225a5988ad434b782f2d868d7bfcd';
        $this->is_eu_compatible = 1;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->controllers = array('payment', 'validation');
        $this->bootstrap = true;

        $this->currencies = true;
        $this->currencies_mode = 'radio';

        parent::__construct();
        require_once realpath(dirname(__FILE__) .'/smarty/plugins') . '/modifier.totlreplace.php';

        $this->displayName = $this->l('Braintree');
        $this->description = $this->l('Boost revenue with the mobile native Braintree module, driving more conversion in 45+ countries and accepting the latest payment methods.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
        $this->module_link = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;

        $this->errors = '';
        $this->serviceBraintreeOrder = new ServiceBraintreeOrder();
        $this->serviceBraintreeCapture = new ServiceBraintreeCapture();
        $this->serviceBraintreeVaulting = new ServiceBraintreeVaulting();
        $this->methodBraintree = AbstractMethodBraintree::load('Braintree');
    }

    public function install()
    {
        $installer = new ModuleInstaller($this);

        $isPhpVersionCompliant = false;
        try {
            $isPhpVersionCompliant = $installer->checkPhpVersion();
        } catch (\Exception $e) {
            $this->_errors[] = Tools::displayError($e->getMessage());
        }

        if (($isPhpVersionCompliant && parent::install() && $installer->install()) == false) {
            return false;
        }

        if ($this->installOrderState() == false) {
            return false;
        }

        Configuration::updateValue('BRAINTREE_API_INTENT', 'sale');

        $this->checkBraintreeStats();
        return true;
    }

    public function uninstall()
    {
        $installer = new ModuleInstaller($this);

        if ((parent::uninstall() && $installer->uninstall()) == false) {
            return false;
        }

        if ($this->uninstallOrderStates() == false) {
            return false;
        }

        return true;
    }

    /**
     * Delete order states
     * @return bool
     */
    public function uninstallOrderStates()
    {
        /* @var $orderState OrderState*/
        $result = true;
        $collection = new PrestaShopCollection('OrderState');
        $collection->where('module_name', '=', $this->name);
        $orderStates = $collection->getResults();

        if ($orderStates == false) {
            return $result;
        }

        foreach ($orderStates as $orderState) {
            $result &= $orderState->delete();
        }

        return $result;
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminBraintreeSetup', true));
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('controller') == "AdminOrders" && Tools::getValue('id_order')) {
            $braintreeOrder = $this->serviceBraintreeOrder->loadByOrderId(Tools::getValue('id_order'));
            if (Validate::isLoadedObject($braintreeOrder)) {
                Media::addJsDefL('chb_braintree_refund', $this->l('Refund Braintree'));
                $this->context->controller->addJS(_PS_MODULE_DIR_ . $this->name . '/views/js/bo_order.js');
            }
        }
    }

    public function hookActionObjectCurrencyAddAfter($params)
    {
        $this->methodBraintree->createForCurrency($params['object']->iso_code);
        $allCurrency = $this->methodBraintree->getAllCurrency();
        if (empty($allCurrency)) {
            return;
        }

        foreach ($allCurrency as $currency => $merchantAccountForCurrency) {
            Configuration::updateValue($this->getNameMerchantAccountForCurrency($currency), $merchantAccountForCurrency);
        }
    }

    /**
     * @param string $currency iso of currency
     * @param bool $mode true if mode Sandbox and false if mode Live
     * @return string name of merchant account id for currency
     */
    public function getNameMerchantAccountForCurrency($currency, $mode = null)
    {
        if ($mode === null) {
            $mode = Configuration::get('BRAINTREE_SANDBOX');
        }
        return Tools::strtoupper('braintree_merchant_account_id_' . $currency . '_' . ((int)$mode ? 'sandbox' : 'live'));
    }

    public function hookActionOrderSlipAdd($params)
    {
        if (Tools::isSubmit('doPartialRefundBraintree')) {
            $braintreeOrder = $this->serviceBraintreeOrder->loadByOrderId($params['order']->id);

            if (Validate::isLoadedObject($braintreeOrder) == false) {
                return false;
            }

            $message = '';
            $ex_detailed_message = '';
            $capture = $this->serviceBraintreeCapture->loadByOrderBraintreeId($braintreeOrder->id);
            if (Validate::isLoadedObject($capture) && !$capture->id_capture) {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $this->l('You couldn\'t refund order, it\'s not payed yet.'),
                    null,
                    $braintreeOrder->id_order,
                    $braintreeOrder->id_cart,
                    $this->context->shop->id,
                    $braintreeOrder->payment_tool,
                    $braintreeOrder->sandbox
                );
                ProcessLoggerHandler::closeLogger();
                return true;
            }
            $status = $this->methodBraintree->getTransactionStatus($braintreeOrder->id_transaction);

            if ($status == "submitted_for_settlement") {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $this->l('You couldn\'t refund order, it\'s not payed yet.'),
                    null,
                    $braintreeOrder->id_order,
                    $braintreeOrder->id_cart,
                    $this->context->shop->id,
                    $braintreeOrder->payment_tool,
                    $braintreeOrder->sandbox
                );
                ProcessLoggerHandler::closeLogger();
                return true;
            } else {
                try {
                    $refund_response = $this->methodBraintree->partialRefund($params);
                } catch (Exception $e) {
                    $ex_detailed_message = $e->getMessage();
                }
            }

            if (isset($refund_response) && isset($refund_response['success']) && $refund_response['success']) {
                foreach ($refund_response as $key => $msg) {
                    $message .= $key." : ".$msg.";\r";
                }
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logInfo(
                    $message,
                    isset($refund_response['refund_id']) ? $refund_response['refund_id'] : null,
                    $braintreeOrder->id_order,
                    $braintreeOrder->id_cart,
                    $this->context->shop->id,
                    $braintreeOrder->payment_tool,
                    $braintreeOrder->sandbox
                );
                ProcessLoggerHandler::closeLogger();
            } elseif (isset($refund_response) && empty($refund_response) == false) {
                foreach ($refund_response as $key => $msg) {
                    $message .= $key." : ".$msg.";\r";
                }
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $message,
                    null,
                    $braintreeOrder->id_order,
                    $braintreeOrder->id_cart,
                    $this->context->shop->id,
                    $braintreeOrder->payment_tool,
                    $braintreeOrder->sandbox
                );
                ProcessLoggerHandler::closeLogger();
            }
            if ($ex_detailed_message) {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $ex_detailed_message,
                    null,
                    $braintreeOrder->id_order,
                    $braintreeOrder->id_cart,
                    $this->context->shop->id,
                    $braintreeOrder->payment_tool,
                    $braintreeOrder->sandbox
                );
                ProcessLoggerHandler::closeLogger();
            }
        }
    }

    public function hookActionOrderStatusPostUpdate(&$params)
    {
        if ($params['newOrderStatus']->paid == 1) {
            $capture = $this->serviceBraintreeCapture->getByOrderId($params['id_order']);
            $ps_order = new Order($params['id_order']);
            if ($capture['id_capture']) {
                $this->setTransactionId($ps_order, $capture['id_capture']);
            }
        }
    }

    /**
     * Get url for BT onboarding
     * @param object $ps_order PS order object
     * @param string $transaction_id payment transaction ID
     */
    public function setTransactionId($ps_order, $transaction_id)
    {
        Db::getInstance()->update(
            'order_payment',
            array('transaction_id' => pSQL($transaction_id)),
            'order_reference = "'.pSQL($ps_order->reference).'"'
        );
    }

    public function hookActionOrderStatusUpdate(&$params)
    {
        /**@var $orderBraintree BraintreeOrder
         * @var $braintreeCapture BraintreeCapture
         */
        $orderBraintree = $this->serviceBraintreeOrder->loadByOrderId($params['id_order']);

        if (!Validate::isLoadedObject($orderBraintree)) {
            return false;
        }

        $message = '';
        $ex_detailed_message = '';
        if ($params['newOrderStatus']->id == Configuration::get('PS_OS_CANCELED')) {
            $braintreeCapture = $this->serviceBraintreeCapture->loadByOrderBraintreeId($orderBraintree->id);
            if (Validate::isLoadedObject($braintreeCapture) && !$braintreeCapture->id_capture) {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $this->l('You couldn\'t refund order, it\'s not payed yet.'),
                    null,
                    $orderBraintree->id_order,
                    $orderBraintree->id_cart,
                    $this->context->shop->id,
                    $orderBraintree->payment_tool,
                    $orderBraintree->sandbox
                );
                ProcessLoggerHandler::closeLogger();
                Tools::redirect($_SERVER['HTTP_REFERER'].'&not_payed_capture=1');
            }

            try {
                $response_void = $this->methodBraintree->void($orderBraintree);
            } catch (PayPal\Exception\PPConnectionException $e) {
                $ex_detailed_message = $this->l('Error connecting to ') . $e->getUrl();
            } catch (PayPal\Exception\PPMissingCredentialException $e) {
                $ex_detailed_message = $e->errorMessage();
            } catch (PayPal\Exception\PPConfigurationException $e) {
                $ex_detailed_message = $this->l('Invalid configuration. Please check your configuration file');
            }
            if (isset($response_void) && isset($response_void['success']) && $response_void['success']) {
                if (Validate::isLoadedObject($braintreeCapture)) {
                    $braintreeCapture->result = 'voided';
                    $braintreeCapture->save();
                }

                $orderBraintree->payment_status = 'voided';
                $orderBraintree->save();
                foreach ($response_void as $key => $msg) {
                    $message .= $key." : ".$msg.";\r";
                }
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logInfo(
                    $message,
                    isset($response_void['transaction_id']) ? $response_void['transaction_id'] : null,
                    $orderBraintree->id_order,
                    $orderBraintree->id_cart,
                    $this->context->shop->id,
                    $orderBraintree->payment_tool,
                    $orderBraintree->sandbox,
                    $response_void['date_transaction']
                );
                ProcessLoggerHandler::closeLogger();
            } elseif (isset($response_void) && empty($response_void) == false) {
                foreach ($response_void as $key => $msg) {
                    $message .= $key." : ".$msg.";\r";
                }
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $message,
                    null,
                    $orderBraintree->id_order,
                    $orderBraintree->id_cart,
                    $this->context->shop->id,
                    null,
                    $orderBraintree->sandbox
                );
                ProcessLoggerHandler::closeLogger();
                Tools::redirect($_SERVER['HTTP_REFERER'].'&cancel_failed=1');
            }

            if ($ex_detailed_message) {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $ex_detailed_message,
                    null,
                    $orderBraintree->id_order,
                    $orderBraintree->id_cart,
                    $this->context->shop->id,
                    $orderBraintree->payment_tool,
                    $orderBraintree->sandbox
                );
                ProcessLoggerHandler::closeLogger();
            }
        }

        if ($params['newOrderStatus']->id == Configuration::get('PS_OS_REFUND')) {
            $braintreeCapture = $this->serviceBraintreeCapture->loadByOrderBraintreeId($orderBraintree->id);

            if (Validate::isLoadedObject($braintreeCapture) && !$braintreeCapture->id_capture) {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $this->l('You couldn\'t refund order, it\'s not payed yet.'),
                    null,
                    $orderBraintree->id_order,
                    $orderBraintree->id_cart,
                    $this->context->shop->id,
                    $orderBraintree->payment_tool,
                    $orderBraintree->sandbox
                );
                ProcessLoggerHandler::closeLogger();
                Tools::redirect($_SERVER['HTTP_REFERER'].'&not_payed_capture=1');
            }
            $status = $this->methodBraintree->getTransactionStatus($orderBraintree);

            if ($status == "submitted_for_settlement") {
                try {
                    $refund_response = $this->methodBraintree->void($orderBraintree);
                } catch (PayPal\Exception\PPConnectionException $e) {
                    $ex_detailed_message = $this->l('Error connecting to ') . $e->getUrl();
                } catch (PayPal\Exception\PPMissingCredentialException $e) {
                    $ex_detailed_message = $e->errorMessage();
                } catch (PayPal\Exception\PPConfigurationException $e) {
                    $ex_detailed_message = $this->l('Invalid configuration. Please check your configuration file');
                }
                if (isset($refund_response) && isset($refund_response['success']) && $refund_response['success']) {
                    if (Validate::isLoadedObject($braintreeCapture)) {
                        $braintreeCapture->result = 'voided';
                    }
                    $orderBraintree->payment_status = 'voided';
                    foreach ($refund_response as $key => $msg) {
                        $message .= $key." : ".$msg.";\r";
                    }
                    ProcessLoggerHandler::openLogger();
                    ProcessLoggerHandler::logInfo(
                        $message,
                        isset($refund_response['transaction_id']) ? $refund_response['transaction_id'] : null,
                        $orderBraintree->id_order,
                        $orderBraintree->id_cart,
                        $this->context->shop->id,
                        $orderBraintree->payment_tool,
                        $orderBraintree->sandbox,
                        $response_void['date_transaction']
                    );
                    ProcessLoggerHandler::closeLogger();
                }
            } else {
                try {
                    $refund_response = $this->methodBraintree->refund($orderBraintree);
                } catch (PayPal\Exception\PPConnectionException $e) {
                    $ex_detailed_message = $this->l('Error connecting to ') . $e->getUrl();
                } catch (PayPal\Exception\PPMissingCredentialException $e) {
                    $ex_detailed_message = $e->errorMessage();
                } catch (PayPal\Exception\PPConfigurationException $e) {
                    $ex_detailed_message = $this->l('Invalid configuration. Please check your configuration file');
                } catch (PayPal\Exception\PayPalConnectionException $e) {
                    $decoded_message = Tools::jsonDecode($e->getData());
                    $ex_detailed_message = $decoded_message->message;
                } catch (PayPal\Exception\PayPalInvalidCredentialException $e) {
                    $ex_detailed_message = $e->errorMessage();
                } catch (PayPal\Exception\PayPalMissingCredentialException $e) {
                    $ex_detailed_message = $this->l('Invalid configuration. Please check your configuration file');
                } catch (Exception $e) {
                    $ex_detailed_message = $e->errorMessage();
                }

                if (isset($refund_response) && isset($refund_response['success']) && $refund_response['success']) {
                    if (Validate::isLoadedObject($braintreeCapture)) {
                        $braintreeCapture->result = 'refunded';
                    }

                    $orderBraintree->payment_status = 'refunded';
                    foreach ($refund_response as $key => $msg) {
                        $message .= $key." : ".$msg.";\r";
                    }
                    ProcessLoggerHandler::openLogger();
                    ProcessLoggerHandler::logInfo(
                        $message,
                        isset($refund_response['refund_id']) ? $refund_response['refund_id'] : null,
                        $orderBraintree->id_order,
                        $orderBraintree->id_cart,
                        $this->context->shop->id,
                        $orderBraintree->payment_tool,
                        $orderBraintree->sandbox,
                        $refund_response['date_transaction']
                    );
                    ProcessLoggerHandler::closeLogger();
                }
            }

            if (isset($refund_response) && isset($refund_response['success']) && $refund_response['success']) {
                if (Validate::isLoadedObject($braintreeCapture)) {
                    $braintreeCapture->save();
                }

                $orderBraintree->save();
            }

            if ($ex_detailed_message) {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $ex_detailed_message,
                    null,
                    $orderBraintree->id_order,
                    $orderBraintree->id_cart,
                    $this->context->shop->id,
                    $orderBraintree->payment_tool,
                    $orderBraintree->sandbox
                );
                ProcessLoggerHandler::closeLogger();
            }

            if (isset($refund_response) && !isset($refund_response['already_refunded']) && !isset($refund_response['success'])) {
                foreach ($refund_response as $key => $msg) {
                    $message .= $key." : ".$msg.";\r";
                }
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $message,
                    null,
                    $orderBraintree->id_order,
                    $orderBraintree->id_cart,
                    $this->context->shop->id,
                    $orderBraintree->payment_tool,
                    $orderBraintree->sandbox
                );
                ProcessLoggerHandler::closeLogger();
                Tools::redirect($_SERVER['HTTP_REFERER'].'&error_refund=1');
            }
        }

        if ($params['newOrderStatus']->paid == 1) {
            $braintreeCapture = $this->serviceBraintreeCapture->loadByOrderBraintreeId($orderBraintree->id);
            if (!Validate::isLoadedObject($braintreeCapture)) {
                return false;
            }

            try {
                $capture_response = $this->methodBraintree->confirmCapture($orderBraintree);
            } catch (PayPal\Exception\PPConnectionException $e) {
                $ex_detailed_message = $this->l('Error connecting to ') . $e->getUrl();
            } catch (PayPal\Exception\PPMissingCredentialException $e) {
                $ex_detailed_message = $e->errorMessage();
            } catch (PayPal\Exception\PPConfigurationException $e) {
                $ex_detailed_message = $this->l('Invalid configuration. Please check your configuration file');
            }

            if (isset($capture_response['success'])) {
                $orderBraintree->payment_status = $capture_response['status'];
                $orderBraintree->save();
            }
            if ($ex_detailed_message) {
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $ex_detailed_message,
                    null,
                    $orderBraintree->id_order,
                    $orderBraintree->id_cart,
                    $this->context->shop->id,
                    $orderBraintree->payment_tool,
                    $orderBraintree->sandbox
                );
                ProcessLoggerHandler::closeLogger();
            } elseif (isset($capture_response) && isset($capture_response['success']) && $capture_response['success']) {
                foreach ($capture_response as $key => $msg) {
                    $message .= $key." : ".$msg.";\r";
                }
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logInfo(
                    $message,
                    isset($capture_response['authorization_id']) ? $capture_response['authorization_id'] : null,
                    $orderBraintree->id_order,
                    $orderBraintree->id_cart,
                    $this->context->shop->id,
                    $orderBraintree->payment_tool,
                    $orderBraintree->sandbox,
                    isset($capture_response['date_transaction']) ? $capture_response['date_transaction'] : null
                );
                ProcessLoggerHandler::closeLogger();
            }

            if (!isset($capture_response['already_captured']) && !isset($capture_response['success'])) {
                foreach ($capture_response as $key => $msg) {
                    $message .= $key." : ".$msg.";\r";
                }
                ProcessLoggerHandler::openLogger();
                ProcessLoggerHandler::logError(
                    $message,
                    null,
                    $orderBraintree->id_order,
                    $orderBraintree->id_cart,
                    $this->context->shop->id,
                    $orderBraintree->payment_tool,
                    $orderBraintree->sandbox
                );
                ProcessLoggerHandler::closeLogger();
                Tools::redirect($_SERVER['HTTP_REFERER'].'&error_capture=1');
            }
        }
    }

    public function hookDisplayAdminCartsView($params)
    {
        $params['class_logger'] = BraintreeLog::class;
        if ($result = $this->handleExtensionsHook(__FUNCTION__, $params)) {
            if (!is_null($result)) {
                return $result;
            }
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        $id_order = $params['id_order'];
        $order = new Order((int)$id_order);
        $braintreeOrder = $this->serviceBraintreeOrder->loadByOrderId($id_order);

        if (Validate::isLoadedObject($braintreeOrder) == false) {
            return;
        }

        $braintreeCapture = $this->serviceBraintreeCapture->loadByOrderBraintreeId($braintreeOrder->id);

        if (Validate::isLoadedObject($braintreeOrder) == false) {
            return false;
        }
        if ($braintreeOrder->sandbox) {
            $this->context->controller->warnings[] = $this->l('[SANDBOX] Please pay attention that payment for this order was made via Braintree Sandbox mode.');
        }
        if (Tools::getValue('not_payed_capture')) {
            $this->context->controller->errors[] = $this->l('You couldn\'t refund order, it\'s not payed yet.');
        }
        if (Tools::getValue('error_refund')) {
            $this->context->controller->errors[] = $this->l('We have an unexpected problem during refund operation. For more details please see the Braintree tab in the order details or Braintree Logs.');
        }
        if (Tools::getValue('cancel_failed')) {
            $this->context->controller->errors[] = $this->l('We have an unexpected problem during cancel operation. For more details please see the Braintree tab in the order details or Braintree Logs.');
        }
        if ($order->current_state == Configuration::get('PS_OS_REFUND') &&  $braintreeOrder->payment_status == 'refunded') {
            $this->adminDisplayInformation($this->l('Your order is fully refunded by Braintree.'));
        }

        if ($order->getCurrentOrderState()->paid == 1 && Validate::isLoadedObject($braintreeCapture) && $braintreeCapture->id_capture) {
            $this->adminDisplayInformation($this->l('Your order is fully captured by Braintree.'));
        }
        if (Tools::getValue('error_capture')) {
            $this->context->controller->errors[] = $this->l('We have an unexpected problem during capture operation. For more details please see the Braintree tab in the order details or Braintree Logs.');
        }

        if ($braintreeOrder->total_paid != $braintreeOrder->total_prestashop) {
            $preferences = $this->context->link->getAdminLink('AdminPreferences', true);
            $this->adminDisplayWarning($this->l('Product pricing has been modified as your rounding settings aren\'t compliant with Braintree.').' '.
                $this->l('To avoid automatic rounding to customer for Braintree payments, please update your rounding settings.').' '.
                '<a target="_blank" href="'.$preferences.'">'.$this->l('Reed more.').'</a>');
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        $diff_cron_time = date_diff(date_create('now'), date_create(Configuration::get('BRAINTREE_CRON_TIME')));
        if ($diff_cron_time->d > 0 || $diff_cron_time->h > 4 || Configuration::get('BRAINTREE_CRON_TIME') == false) {
            Configuration::updateValue('BRAINTREE_CRON_TIME', date('Y-m-d H:i:s'));
            $bt_orders = $this->serviceBraintreeOrder->getBraintreeOrdersForValidation();
            
            if ($bt_orders) {
                $transactions = $this->methodBraintree->searchTransactions($bt_orders);

                if (empty($transactions)) {
                    return;
                }

                foreach ($transactions as $transaction) {
                    $braintreeOrder = $this->serviceBraintreeOrder->loadByTransactionId($transaction->id);

                    if (Validate::isLoadedObject($braintreeOrder) == false) {
                        continue;
                    }

                    $ps_order = new Order($braintreeOrder->id_order);
                    $paid_state  = Configuration::get('PS_OS_PAYMENT');
                    $ps_order_details = OrderDetail::getList($braintreeOrder->id_order);

                    foreach ($ps_order_details as $order_detail) {
                        // Switch to back order if needed
                        $product_stock = StockAvailable::getQuantityAvailableByProduct($order_detail['product_id'], $order_detail['product_attribute_id']);
                        if (Configuration::get('PS_STOCK_MANAGEMENT') && $product_stock <= 0) {
                            $paid_state  = Configuration::get('PS_OS_OUTOFSTOCK_PAID');
                        }
                    }

                    switch ($transaction->status) {
                        case 'declined':
                            if ($braintreeOrder->payment_status != "declined") {
                                $braintreeOrder->payment_status = $transaction->status;
                                $braintreeOrder->update();
                                $ps_order->setCurrentState(Configuration::get('PS_OS_ERROR'));
                            }
                            break;
                        case 'settled':
                            if ($braintreeOrder->payment_status != "settled") {
                                $braintreeOrder->payment_status = $transaction->status;
                                $braintreeOrder->update();
                                $ps_order->setCurrentState($paid_state);
                                $this->setTransactionId($ps_order, $transaction->id);
                            }
                            break;
                        case 'settling': // waiting
                            // do nothing and check later one more time
                            break;
                        case 'submit_for_settlement': //waiting
                            // do nothing and check later one more time
                            break;
                        default:
                            // do nothing and check later one more time
                            break;
                    }
                }
            }
        }
    }

    public function hookDisplayCustomerAccount()
    {
        if (Configuration::get('BRAINTREE_VAULTING')) {
            return $this->display(__FILE__, 'displayCustomerAccount.tpl');
        }
    }

    public function hookDisplayMyAccountBlock()
    {
        if (Configuration::get('BRAINTREE_VAULTING')) {
            return $this->display(__FILE__, 'displayMyAccountBlock.tpl');
        }
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $braintreeOrder = $this->serviceBraintreeOrder->loadByOrderId($params['order']->id);
        if (Validate::isLoadedObject($braintreeOrder) == false) {
            return;
        }

        $this->context->smarty->assign(array(
            'transaction_id' => $braintreeOrder->id_transaction,
        ));

        $this->context->controller->registerJavascript($this->name.'-order_confirmation_js', 'modules/' . $this->name . '/views/js/order_confirmation.js');
        return $this->context->smarty->fetch('module:braintree/views/templates/hook/displayOrderConfirmation.tpl');
    }

    public function hookHeader()
    {
        if (Tools::getValue('controller') == "order") {
            $active = false;
            $modules = Hook::getHookModuleExecList('paymentOptions');
            if (empty($modules)) {
                return;
            }
            foreach ($modules as $module) {
                if ($module['module'] == 'braintree') {
                    $active = true;
                }
            }
            if (!$active) {
                return;
            }

            $this->context->controller->addJqueryPlugin('fancybox');
            $this->context->controller->registerJavascript($this->name . '-braintreegateway-client', 'https://js.braintreegateway.com/web/3.24.0/js/client.min.js', array('server' => 'remote'));
            $this->context->controller->registerJavascript($this->name . '-braintreegateway-hosted', 'https://js.braintreegateway.com/web/3.24.0/js/hosted-fields.min.js', array('server' => 'remote'));
            $this->context->controller->registerJavascript($this->name . '-braintreegateway-data', 'https://js.braintreegateway.com/web/3.24.0/js/data-collector.min.js', array('server' => 'remote'));
            $this->context->controller->registerJavascript($this->name . '-braintreegateway-3ds', 'https://js.braintreegateway.com/web/3.24.0/js/three-d-secure.min.js', array('server' => 'remote'));
            $this->context->controller->registerStylesheet($this->name . '-braintreecss', 'modules/' . $this->name . '/views/css/braintree.css');
            $this->addJsVarsLangBT();
            $this->addJsVarsBT();
            $this->context->controller->registerJavascript($this->name . '-braintreejs', 'modules/' . $this->name . '/views/js/payment_bt.js');
            if (Configuration::get('BRAINTREE_ACTIVATE_PAYPAL')) {
                $this->context->controller->registerJavascript($this->name . '-pp-braintree-checkout', 'https://www.paypalobjects.com/api/checkout.js', array('server' => 'remote'));
                $this->context->controller->registerJavascript($this->name . '-pp-braintree-checkout-min', 'https://js.braintreegateway.com/web/3.24.0/js/paypal-checkout.min.js', array('server' => 'remote'));
                Media::addJsDefL('empty_nonce', $this->l('Please click on the PayPal Pay button first'));
                $this->addJsVarsPB();
                $this->context->controller->registerJavascript($this->name . '-pp-braintreejs', 'modules/' . $this->name . '/views/js/payment_pbt.js');
            }
        }
    }

    public function addJsVarsLangBT()
    {
        Media::addJsDefL('bt_translations_client', $this->l('Error create Client'));
        Media::addJsDefL('bt_translations_card_nmb', $this->l('Card number'));
        Media::addJsDefL('bt_translations_date', $this->l('MM/YY'));
        Media::addJsDefL('bt_translations_hosted', $this->l('Error create Hosted fields'));
        Media::addJsDefL('bt_translations_invalid', $this->l('is invalid.'));
        Media::addJsDefL('bt_translations_token', $this->l('Tokenization failed server side. Is the card valid?'));
        Media::addJsDefL('bt_translations_network', $this->l('Network error occurred when tokenizing.'));
        Media::addJsDefL('bt_translations_tkn_failed', $this->l('Tokenize failed'));
        Media::addJsDefL('bt_translations_https', $this->l('3D Secure requires HTTPS.'));
        Media::addJsDefL('bt_translations_load_3d', $this->l('Load 3D Secure Failed'));
        Media::addJsDefL('bt_translations_request_problem', $this->l('There was a problem with your request.'));
        Media::addJsDefL('bt_translations_failed_3d', $this->l('3D Secure Failed'));
        Media::addJsDefL('bt_translations_empty_field', $this->l('is empty.'));
        Media::addJsDefL('bt_translations_expirationDate', $this->l('This expiration date '));
        Media::addJsDefL('bt_translations_number', $this->l('This card number '));
        Media::addJsDefL('bt_translations_cvv', $this->l('Please fill out a CVV.'));
    }

    public function hookDisplayAdminOrderTabOrder($params)
    {
        if ($result = $this->handleExtensionsHook(__FUNCTION__, $params)) {
            if (!is_null($result)) {
                return $result;
            }
        }
    }

    public function hookDisplayAdminOrderContentOrder($params)
    {
        $params['class_logger'] = BraintreeLog::class;
        if ($result = $this->handleExtensionsHook(__FUNCTION__, $params)) {
            if (!is_null($result)) {
                return $result;
            }
        }
    }

    public function hookPaymentOptions($params)
    {
        /* for avoiding the connection exception need to verify if module configured correct*/
        if ($this->methodBraintree->isConfigured() == false) {
            return;
        }

        /* for avoiding the exception of authorization need to verify mode of payment currency and merchant account*/
        if ($this->getCurrentModePaymentCurrency() == BRAINTREE_PAYMENT_CUSTOMER_CURRENCY &&
            $this->merchantAccountForCurrencyConfigured() == false
        ) {
            return;
        }
        $payments_options = array();
        if (Configuration::get('BRAINTREE_ACTIVATE_PAYPAL')) {
            $embeddedOption = new PaymentOption();
            $action_text = $this->l('Pay with PayPal');
            $embeddedOption->setCallToActionText($action_text)
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/paypal.png'))
                ->setModuleName($this->name)
                ->setAdditionalInformation($this->generateFormPB())
                ->setAction('javascript:BraintreePaypalSubmitPayment();');
            $payments_options[] = $embeddedOption;
        }

        $embeddedOption = new PaymentOption();
        $embeddedOption->setCallToActionText($this->l('Pay with card'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/mini-cards.png'))
            ->setAdditionalInformation($this->generateFormBt())
            ->setAction('javascript:BraintreeSubmitPayment();');
        $payments_options[] = $embeddedOption;

        return $payments_options;
    }

    public function generateFormPB()
    {
        $clientToken = $this->methodBraintree->init();
        if (isset($clientToken['error_code'])) {
            $this->context->smarty->assign(array(
                'init_error'=> $this->l('Error Braintree initialization ').$clientToken['error_code'].' : '.$clientToken['error_msg'],
            ));
        }
        $this->context->smarty->assign(array(
            'braintreeSubmitUrl'=> $this->context->link->getModuleLink($this->name, 'validation', array(), true),
            'baseDir' => $this->context->link->getBaseLink($this->context->shop->id, true),
            'path' => $this->_path,
            'bt_method' => BRAINTREE_PAYPAL_PAYMENT,
            'active_vaulting'=> Configuration::get('BRAINTREE_VAULTING'),
            'show_paypal_benefits' => Configuration::get('BRAINTREE_SHOW_PAYPAL_BENEFITS')
        ));
        if (Configuration::get('BRAINTREE_VAULTING')) {
            $payment_methods = $this->serviceBraintreeVaulting->getCustomerMethods($this->context->customer->id, BRAINTREE_PAYPAL_PAYMENT);
            $this->context->smarty->assign(array(
                'payment_methods' => $payment_methods,
            ));
        }

        return $this->context->smarty->fetch('module:braintree/views/templates/front/payment_pbt.tpl');
    }

    public function addJsVarsPB()
    {
        $clientToken = $this->methodBraintree->init();
        Media::addJsDef(array(
            'paypal_braintree_authorization' => $clientToken,
            'paypal_braintree_amount' => $this->context->cart->getOrderTotal(),
            'paypal_braintree_mode' => $this->methodBraintree->mode == 'SANDBOX' ? Tools::strtolower($this->methodBraintree->mode) : 'production',
            'paypal_braintree_currency' => $this->context->currency->iso_code,
        ));
    }

    public function generateFormBT()
    {
        $amount = $this->context->cart->getOrderTotal();
        $clientToken = $this->methodBraintree->init();
        $check3DS = 0;
        $required_3ds_amount = Tools::convertPrice(Configuration::get('BRAINTREE_3DSECURE_AMOUNT'), Currency::getCurrencyInstance((int)$this->context->currency->id));
        if (Configuration::get('BRAINTREE_3DSECURE') && $amount > $required_3ds_amount) {
            $check3DS = 1;
        }
        if (isset($clientToken['error_code'])) {
            $this->context->smarty->assign(array(
                'init_error'=> $this->l('Error Braintree initialization ').$clientToken['error_code'].' : '.$clientToken['error_msg'],
            ));
        }
        $required_3ds_amount = Tools::convertPrice(Configuration::get('BRAINTREE_3DSECURE_AMOUNT'), Currency::getCurrencyInstance((int)$this->context->currency->id));
        if (Configuration::get('BRAINTREE_VAULTING')) {
            $payment_methods = $this->serviceBraintreeVaulting->getCustomerMethods($this->context->customer->id, BRAINTREE_CARD_PAYMENT);
            if (Configuration::get('BRAINTREE_3DSECURE') && $amount > $required_3ds_amount) {
                foreach ($payment_methods as $key => $method) {
                    $nonce = $this->methodBraintree->createMethodNonce($method['token']);
                    $payment_methods[$key]['nonce'] = $nonce;
                }
            }
            $this->context->smarty->assign(array(
                'active_vaulting'=> true,
                'payment_methods' => $payment_methods,
            ));
        }
        $this->context->smarty->assign(array(
            'error_msg'=> Tools::getValue('bt_error_msg'),
            'braintreeToken'=> $clientToken,
            'braintreeSubmitUrl'=> $this->context->link->getModuleLink($this->name, 'validation', array(), true),
            'braintreeAmount'=> $amount,
            'check3Dsecure'=> $check3DS,
            'baseDir' => $this->context->link->getBaseLink($this->context->shop->id, true),
            'method_bt' => BRAINTREE_CARD_PAYMENT,
        ));

        return $this->context->smarty->fetch('module:braintree/views/templates/front/payment_bt.tpl');
    }

    public function addJsVarsBT()
    {
        $amount = $this->context->cart->getOrderTotal();
        $clientToken = $this->methodBraintree->init();
        $check3DS = 0;
        $required_3ds_amount = Tools::convertPrice(Configuration::get('BRAINTREE_3DSECURE_AMOUNT'), Currency::getCurrencyInstance((int)$this->context->currency->id));
        if (Configuration::get('BRAINTREE_3DSECURE') && $amount > $required_3ds_amount) {
            $check3DS = 1;
        }
        Media::addJsDef(array(
            'authorization' => $clientToken,
            'bt_amount' => $amount,
            'check3DS' => $check3DS,
        ));
    }

    /**
     * Check if we need convert currency
     * @return boolean|integer currency id
     */
    public function needConvert()
    {
        $currency_mode = Currency::getPaymentCurrenciesSpecial($this->id);
        $mode_id = $currency_mode['id_currency'];
        if ($mode_id == -2) {
            return (int)Configuration::get('PS_CURRENCY_DEFAULT');
        } elseif ($mode_id == -1) {
            return false;
        } elseif ($mode_id != $this->context->currency->id) {
            return (int)$mode_id;
        } else {
            return false;
        }
    }

    /**
     * Get payment currency iso code
     * @return string currency iso code
     */
    public function getPaymentCurrencyIso()
    {
        if ($id_currency = $this->needConvert()) {
            $currency = new Currency((int)$id_currency);
        } else {
            $currency = Context::getContext()->currency;
        }
        return $currency->iso_code;
    }

    public function validateOrder($id_cart, $id_order_state, $amount_paid, $payment_method = 'Unknown', $message = null, $transaction = array(), $currency_special = null, $dont_touch_amount = false, $secure_key = false, Shop $shop = null)
    {
        if ($this->needConvert()) {
            $amount_paid_curr = Tools::ps_round(Tools::convertPrice($amount_paid, new Currency($currency_special), true), 2);
        } else {
            $amount_paid_curr = Tools::ps_round($amount_paid, 2);
        }
        $amount_paid = Tools::ps_round($amount_paid, 2);

        $cart = new Cart((int) $id_cart);
        $total_ps = (float)$cart->getOrderTotal(true, Cart::BOTH);
        if ($amount_paid_curr > $total_ps+0.10 || $amount_paid_curr < $total_ps-0.10) {
            $total_ps = $amount_paid_curr;
        }

        try {
            parent::validateOrder(
                (int) $id_cart,
                (int) $id_order_state,
                (float) $total_ps,
                $payment_method,
                $message,
                array('transaction_id' => isset($transaction['transaction_id']) ? $transaction['transaction_id'] : ''),
                $currency_special,
                $dont_touch_amount,
                $secure_key,
                $shop
            );
        } catch (Exception $e) {
            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logError(
                'Order validation error : ' . $e->getMessage(),
                isset($transaction['transaction_id']) ? $transaction['transaction_id'] : null,
                null,
                (int)$id_cart,
                $this->context->shop->id,
                isset($transaction['payment_tool']) && $transaction['payment_tool'] ? $transaction['payment_tool'] : 'Braintree',
                (int)Configuration::get('BRAINTREE_SANDBOX'),
                isset($transaction['date_transaction']) ? $transaction['date_transaction'] : null
            );
            ProcessLoggerHandler::closeLogger();
            $msg = $this->l('Order validation error : ').$e->getMessage().'. ';
            if (isset($transaction['transaction_id']) && $id_order_state != Configuration::get('PS_OS_ERROR')) {
                $msg .= $this->l('Attention, your payment is made. Please, contact customer support. Your transaction ID is  : ').$transaction['transaction_id'];
            }
            Tools::redirect(Context::getContext()->link->getModuleLink('paypal', 'error', array('error_msg' => $msg, 'no_retry' => true)));
        }
        ProcessLoggerHandler::openLogger();
        ProcessLoggerHandler::logInfo(
            'Payment successful',
            isset($transaction['transaction_id']) ? $transaction['transaction_id'] : null,
            $this->currentOrder,
            (int)$id_cart,
            $this->context->shop->id,
            isset($transaction['payment_tool']) && $transaction['payment_tool'] ? $transaction['payment_tool'] : 'PayPal',
            (int)Configuration::get('BRAINTREE_SANDBOX'),
            isset($transaction['date_transaction']) ? $transaction['date_transaction'] : null
        );
        ProcessLoggerHandler::closeLogger();

        if (Tools::version_compare(_PS_VERSION_, '1.7.1.0', '>')) {
            $order = Order::getByCartId($id_cart);
        } else {
            $id_order = Order::getOrderByCartId($id_cart);
            $order = new Order($id_order);
        }

        if (isset($amount_paid_curr) && $amount_paid_curr != 0 && $order->total_paid != $amount_paid_curr && $this->isOneOrder($order->reference)) {
            $order->total_paid = $amount_paid_curr;
            $order->total_paid_real = $amount_paid_curr;
            $order->total_paid_tax_incl = $amount_paid_curr;
            $order->update();

            $sql = 'UPDATE `'._DB_PREFIX_.'order_payment`
		    SET `amount` = '.(float)$amount_paid_curr.'
		    WHERE  `order_reference` = "'.pSQL($order->reference).'"';
            Db::getInstance()->execute($sql);
        }

        $braintree_order = new BraintreeOrder();
        $braintree_order->id_order = $this->currentOrder;
        $braintree_order->id_cart = $id_cart;
        $braintree_order->id_transaction = $transaction['transaction_id'];
        $braintree_order->id_payment = $transaction['id_payment'];
        $braintree_order->payment_method = $transaction['payment_method'];
        $braintree_order->currency = $transaction['currency'];
        $braintree_order->total_paid = (float) $amount_paid;
        $braintree_order->payment_status = $transaction['payment_status'];
        $braintree_order->total_prestashop = (float) $total_ps;
        $braintree_order->payment_tool = isset($transaction['payment_tool']) ? $transaction['payment_tool'] : 'Braintree';
        $braintree_order->sandbox = (int) Configuration::get('BRAINTREE_SANDBOX');
        $braintree_order->save();

        if ($transaction['capture']) {
            $braintree_capture = new BraintreeCapture();
            $braintree_capture->id_braintree_order = $braintree_order->id;
            $braintree_capture->save();
        }
    }

    public function isOneOrder($order_reference)
    {
        $query = new DBQuery();
        $query->select('COUNT(*)');
        $query->from('orders');
        $query->where('reference = "' . pSQL($order_reference) . '"');
        $countOrders = (int)DB::getInstance()->getValue($query);
        return $countOrders == 1;
    }

    /**
     * Create order state
     * @return boolean
     */
    public function installOrderState()
    {
        if (!Configuration::get('BRAINTREE_OS_AWAITING')
            || !Validate::isLoadedObject(new OrderState((int)Configuration::get('BRAINTREE_OS_AWAITING')))
        ) {
            if (Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING')
                || Validate::isLoadedObject(new OrderState((int)Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING')))
            ) {
                $order_state = new OrderState((int)Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING'));
            } else {
                $order_state = new OrderState();
            }

            $order_state->name = array();
            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'En attente de paiement Braintree';
                } else {
                    $order_state->name[$language['id_lang']] = 'Awaiting for Braintree payment';
                }
            }
            $order_state->send_email = false;
            $order_state->color = '#4169E1';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;
            $order_state->module_name = $this->name;
            if ($order_state->id) {
                $order_state->update();
            } elseif ($order_state->add()) {
                $source = _PS_MODULE_DIR_. $this->name . '/views/img/os_braintree.png';
                $destination = _PS_ROOT_DIR_.'/img/os/'.(int) $order_state->id.'.gif';
                copy($source, $destination);
            }
            Configuration::updateValue('BRAINTREE_OS_AWAITING', (int) $order_state->id);
        }

        if (!Configuration::get('BRAINTREE_OS_AWAITING_VALIDATION')
            || !Validate::isLoadedObject(new OrderState((int)Configuration::get('BRAINTREE_OS_AWAITING_VALIDATION')))
        ) {
            if (Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING_VALIDATION')
                || Validate::isLoadedObject(new OrderState((int)Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING_VALIDATION')))
            ) {
                $order_state = new OrderState((int)Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING_VALIDATION'));
            } else {
                $order_state = new OrderState();
            }
            $order_state->name = array();
            foreach (Language::getLanguages() as $language) {
                if (Tools::strtolower($language['iso_code']) == 'fr') {
                    $order_state->name[$language['id_lang']] = 'En attente de validation Braintree';
                } else {
                    $order_state->name[$language['id_lang']] = 'Awaiting for Braintree validation';
                }
            }
            $order_state->send_email = false;
            $order_state->color = '#4169E1';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;
            $order_state->module_name = $this->name;
            if ($order_state->id && $order_state->update()) {
                $order_state->update();
            } elseif ($order_state->add()) {
                $source = _PS_MODULE_DIR_ . $this->name . '/views/img/os_braintree.png';
                $destination = _PS_ROOT_DIR_.'/img/os/'.(int) $order_state->id.'.gif';
                copy($source, $destination);
            }
            Configuration::updateValue('BRAINTREE_OS_AWAITING_VALIDATION', (int) $order_state->id);
        }
        return true;
    }

    public function checkBraintreeStats()
    {
        $tab = Tab::getInstanceFromClassName('AdminBraintreeStats');
        $accountConfigured = $this->methodBraintree->isConfigured();
        if (Validate::isLoadedObject($tab)) {
            if ($tab->active && $accountConfigured == false) {
                $tab->active = false;
                $tab->save();
            } elseif ($tab->active == false && $accountConfigured) {
                $tab->active = true;
                $tab->save();
            }
        }
    }

    public function displayInformation($message)
    {
        $this->context->smarty->assign('message', $message);
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/_partials/alertInfo.tpl');
    }

    public function isSslActive()
    {
        return \Configuration::get('PS_SSL_ENABLED') && \Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
    }

    /**
     * TODO
     * Reset Module only if merchant choose to keep data on modal
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function reset()
    {
        $installer = new ModuleInstaller($this);

        return $installer->reset($this);
    }

    public function handleExtensionsHook($hookName, $params)
    {
        if (!isset($this->extensions) || empty($this->extensions)) {
            return false;
        }
        $result = false;
        foreach ($this->extensions as $extension) {
            /** @var AbstractModuleExtension $extension */
            $extension = new $extension();
            $extension->setModule($this);
            if (is_callable(array($extension, $hookName))) {
                $hookResult = $extension->{$hookName}($params);
                if ($result === false) {
                    $result = $hookResult;
                } elseif (is_array($hookResult) && $result !== false) {
                    $result = array_merge($result, $hookResult);
                } else {
                    $result .= $hookResult;
                }
            }
        }

        return $result;
    }

    /**
     * Handle module widget call
     * @param $action
     * @param $method
     * @param $hookName
     * @param $configuration
     * @return bool
     * @throws \ReflectionException
     */
    public function handleWidget($action, $method, $hookName, $configuration)
    {
        if (!isset($this->extensions) || empty($this->extensions)) {
            return false;
        }

        foreach ($this->extensions as $extension) {
            /** @var AbstractModuleExtension $extension */
            $extension = new $extension();
            if (!($extension instanceof WidgetInterface)) {
                continue;
            }
            $extensionClass = (new ReflectionClass($extension))->getShortName();
            if ($extensionClass != $action) {
                continue;
            }
            $extension->setModule($this);
            if (is_callable(array($extension, $method))) {
                return $extension->{$method}($hookName, $configuration);
            }
        }

        return false;

    }

    /**
     * Add checkbox carrier restrictions for a module.
     *
     * @param array $shops
     *
     * @return bool
     */
    public function addCheckboxCarrierRestrictionsForModule(array $shops = array())
    {
        if (!$shops) {
            $shops = \Shop::getShops(true, null, true);
        }

        $carriers = \Carrier::getCarriers($this->context->language->id, false, false, false, null, \Carrier::ALL_CARRIERS);
        $carrier_ids = array();
        foreach ($carriers as $carrier) {
            $carrier_ids[] = $carrier['id_reference'];
        }

        foreach ($shops as $s) {
            foreach ($carrier_ids as $id_carrier) {
                if (!\Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'module_carrier` (`id_module`, `id_shop`, `id_reference`)
				VALUES (' . (int) $this->id . ', "' . (int) $s . '", ' . (int) $id_carrier . ')')) {
                    return false;
                }
            }
        }

        return true;
    }

    public function disable($force_all = false)
    {
        $result = true;
        $result &= parent::disable($force_all);
        $tabParent = \Tab::getInstanceFromClassName('AdminParentBraintreeConfiguration');

        if (\Validate::isLoadedObject($tabParent) == false) {
            return $result;
        }

        $tabParent->active = false;
        $result &=  $tabParent->save();
        return $result;
    }

    public function enable($force_all = false)
    {
        $result = true;
        $result &= parent::enable($force_all);
        $tabParent = \Tab::getInstanceFromClassName('AdminParentBraintreeConfiguration');

        if (\Validate::isLoadedObject($tabParent) == false) {
            return $result;
        }

        $tabParent->active = true;
        $result &=  $tabParent->save();
        return $result;
    }

    /**
     * Return choosed mode of currency restriction
     * @return int|null
     */
    public function getCurrentModePaymentCurrency()
    {
        $currency_mode = Currency::getPaymentCurrenciesSpecial($this->id);

        if (isset($currency_mode['id_currency']))  {
            return (int)$currency_mode['id_currency'];
        } else {
            return null;
        }
    }

    /**
     * Check if all merchant account ids for currency are configured
     * @return bool
     */
    public function merchantAccountForCurrencyConfigured()
    {
        $allCurrency = Currency::getCurrenciesByIdShop($this->context->shop->id);

        if (empty($allCurrency)) {
            return;
        }

        $result = true;

        foreach ($allCurrency as $currency) {
            $result &= (bool)Configuration::get($this->getNameMerchantAccountForCurrency($currency['iso_code']));
        }

        return $result;
    }
}
