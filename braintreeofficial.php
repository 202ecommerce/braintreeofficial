<?php
/**
 * since 2007 PayPal
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
 *  @author since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
require_once _PS_MODULE_DIR_ . 'braintreeofficial/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

use BraintreeOfficialAddons\classes\AbstractMethodBraintreeOfficial;
use BraintreeOfficialAddons\classes\BraintreeOfficialCapture;
use BraintreeOfficialAddons\classes\BraintreeOfficialCustomer;
use BraintreeOfficialAddons\classes\BraintreeOfficialLog;
use BraintreeOfficialAddons\classes\BraintreeOfficialOrder;
use BraintreeOfficialAddons\classes\BraintreeOfficialVaulting;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialCapture;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialOrder;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialVaulting;
use BraintreeofficialPPBTlib\Extensions\AbstractModuleExtension;
use BraintreeofficialPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;
use BraintreeofficialPPBTlib\Install\ModuleInstaller;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('BRAINTREE_CARD_PAYMENT')) {
    define('BRAINTREE_CARD_PAYMENT', 'card-braintree');
}
if (!defined('BRAINTREE_PAYPAL_PAYMENT')) {
    define('BRAINTREE_PAYPAL_PAYMENT', 'paypal-braintree');
}
if (!defined('BRAINTREE_PAYMENT_CUSTOMER_CURRENCY')) {
    define('BRAINTREE_PAYMENT_CUSTOMER_CURRENCY', -1);
}
if (!defined('BRAINTREE_CART_PAGE')) {
    define('BRAINTREE_CART_PAGE', 1);
}
if (!defined('BRAINTREE_PRODUCT_PAGE')) {
    define('BRAINTREE_PRODUCT_PAGE', 2);
}
if (!defined('BRAINTREEOFFICIAL_NOT_SHOW_SCA_MESSAGE')) {
    define('BRAINTREEOFFICIAL_NOT_SHOW_SCA_MESSAGE', 'BRAINTREEOFFICIAL_NOT_SHOW_SCA_MESSAGE');
}

class BraintreeOfficial extends PaymentModule
{
    public static $state_iso_code_matrix = [
        'MX' => [
            'AGS' => 'AGS',
            'BCN' => 'BC',
            'BCS' => 'BCS',
            'CAM' => 'CAMP',
            'CHP' => 'CHIS',
            'CHH' => 'CHIH',
            'COA' => 'COAH',
            'COL' => 'COL',
            'DIF' => 'DF',
            'DUR' => 'DGO',
            'GUA' => 'GTO',
            'GRO' => 'GRO',
            'HID' => 'HGO',
            'JAL' => 'JAL',
            'MEX' => 'MEX',
            'MIC' => 'MICH',
            'MOR' => 'MOR',
            'NAY' => 'NAY',
            'NLE' => 'NL',
            'OAX' => 'OAX',
            'PUE' => 'PUE',
            'QUE' => 'QRO',
            'ROO' => 'Q ROO',
            'SLP' => 'SLP',
            'SIN' => 'SIN',
            'SON' => 'SON',
            'TAB' => 'TAB',
            'TAM' => 'TAMPS',
            'TLA' => 'TLAX',
            'VER' => 'VER',
            'YUC' => 'YUC',
            'ZAC' => 'ZAC',
        ],
        'JP' => [
            'Aichi' => 'Aichi-KEN',
            'Akita' => 'Akita-KEN',
            'Aomori' => 'Aomori-KEN',
            'Chiba' => 'Chiba-KEN',
            'Ehime' => 'Ehime-KEN',
            'Fukui' => 'Fukui-KEN',
            'Fukuoka' => 'Fukuoka-KEN',
            'Fukushima' => 'Fukushima-KEN',
            'Gifu' => 'Gifu-KEN',
            'Gunma' => 'Gunma-KEN',
            'Hiroshima' => 'Hiroshima-KEN',
            'Hokkaido' => 'Hokkaido-KEN',
            'Hyogo' => 'Hyogo-KEN',
            'Ibaraki' => 'Ibaraki-KEN',
            'Ishikawa' => 'Ishikawa-KEN',
            'Iwate' => 'Iwate-KEN',
            'Kagawa' => 'Kagawa-KEN',
            'Kagoshima' => 'Kagoshima-KEN',
            'Kanagawa' => 'Kanagawa-KEN',
            'Kochi' => 'Kochi-KEN',
            'Kumamoto' => 'Kumamoto-KEN',
            'Kyoto' => 'Kyoto-KEN',
            'Mie' => 'Mie-KEN',
            'Miyagi' => 'Miyagi-KEN',
            'Miyazaki' => 'Miyazaki-KEN',
            'Nagano' => 'Nagano-KEN',
            'Nagasaki' => 'Nagasaki-KEN',
            'Nara' => 'Nara-KEN',
            'Niigata' => 'Niigata-KEN',
            'Oita' => 'Oita-KEN',
            'Okayama' => 'Okayama-KEN',
            'Okinawa' => 'Okinawa-KEN',
            'Osaka' => 'Osaka-KEN',
            'Saga' => 'Saga-KEN',
            'Saitama' => 'Saitama-KEN',
            'Shiga' => 'Shiga-KEN',
            'Shimane' => 'Shimane-KEN',
            'Shizuoka' => 'Shizuoka-KEN',
            'Tochigi' => 'Tochigi-KEN',
            'Tokushima' => 'Tokushima-KEN',
            'Tokyo' => 'Tokyo-KEN',
            'Tottori' => 'Tottori-KEN',
            'Toyama' => 'Toyama-KEN',
            'Wakayama' => 'Wakayama-KEN',
            'Yamagata' => 'Yamagata-KEN',
            'Yamaguchi' => 'Yamaguchi-KEN',
            'Yamanashi' => 'Yamanashi-KEN',
        ],
    ];

    /**
     * List of hooks used in this Module
     */
    public $hooks = [
        'paymentOptions',
        'displayAdminOrder',
        'actionOrderStatusPostUpdate',
        'actionOrderStatusUpdate',
        'header',
        'actionObjectCurrencyAddAfter',
        'displayBackOfficeHeader',
        'displayMyAccountBlock',
        'displayCustomerAccount',
        'actionOrderSlipAdd',
        'displayAdminOrderTabOrder',
        'displayAdminOrderContentOrder',
        'displayAdminCartsView',
        'displayProductAdditionalInfo',
        'displayShoppingCartFooter',
        'actionBeforeCartUpdateQty',
        'displayAdminOrderTop',
        'displayAdminOrderTabLink',
        'displayAdminOrderTabContent',
    ];

    /**
     * List of ppbtlib extentions
     */
    public $extensions = [
        BraintreeofficialPPBTlib\Extensions\ProcessLogger\ProcessLoggerExtension::class,
    ];

    /**
     * List of objectModel used in this Module
     *
     * @var array
     */
    public $objectModels = [
        BraintreeOfficialCapture::class,
        BraintreeOfficialOrder::class,
        BraintreeOfficialVaulting::class,
        BraintreeOfficialCustomer::class,
    ];

    /**
     * @var array
     */
    public $moduleConfigs = [];

    /**
     * List of admin tabs used in this Module
     */
    public $moduleAdminControllers = [
        [
            'name' => [
                'en' => 'Braintree Official',
                'fr' => 'Braintree Officiel',
            ],
            'class_name' => 'AdminParentBraintreeOfficialConfiguration',
            'parent_class_name' => 'SELL',
            'visible' => false,
            'icon' => 'payment',
        ],
        [
            'name' => [
                'en' => 'Configuration',
                'fr' => 'Configuration',
            ],
            'class_name' => 'AdminBraintreeOfficialConfiguration',
            'parent_class_name' => 'AdminParentBraintreeOfficialConfiguration',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Setup',
                'fr' => 'Paramètres',
                'pt' => 'Definições',
                'pl' => 'Ustawienia',
                'nl' => 'Instellingen',
                'it' => 'Impostazioni',
                'es' => 'Configuración',
                'de' => 'Einstellungen',
            ],
            'class_name' => 'AdminBraintreeOfficialSetup',
            'parent_class_name' => 'AdminBraintreeOfficialConfiguration',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Experience',
                'fr' => 'Expérience',
                'de' => 'User Experience',
                'pt' => 'Experiência',
                'pl' => 'Doświadczenie',
                'nl' => 'Ervaring',
                'it' => 'Percorso cliente',
                'es' => 'Experiencia',
            ],
            'class_name' => 'AdminBraintreeOfficialCustomizeCheckout',
            'parent_class_name' => 'AdminBraintreeOfficialConfiguration',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Help',
                'fr' => 'Aide',
                'pt' => 'Ajuda',
                'pl' => 'Pomoc',
                'nl' => 'Hulp',
                'it' => 'Aiuto',
                'es' => 'Ayuda',
                'de' => 'Hilfe',
            ],
            'class_name' => 'AdminBraintreeOfficialHelp',
            'parent_class_name' => 'AdminBraintreeOfficialConfiguration',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Logs',
                'fr' => 'Logs',
                'de' => 'Logs',
                'pt' => 'Logs',
                'pl' => 'Dzienniki',
                'nl' => 'Logs',
                'it' => 'Logs',
                'es' => 'Logs',
            ],
            'class_name' => 'AdminBraintreeOfficialLogs',
            'parent_class_name' => 'AdminBraintreeOfficialConfiguration',
            'visible' => true,
        ],
        [
            'name' => [
                'en' => 'Migration',
                'fr' => 'Migration',
            ],
            'class_name' => 'AdminBraintreeOfficialMigration',
            'parent_class_name' => 'AdminParentBraintreeOfficialConfiguration',
            'visible' => false,
        ],
    ];

    /** @var ServiceBraintreeOfficialOrder */
    protected $serviceBraintreeOfficialOrder;

    /** @var ServiceBraintreeOfficialCapture */
    protected $serviceBraintreeOfficialCapture;

    /** @var ServiceBraintreeOfficialVaulting */
    protected $serviceBraintreeOfficialVaulting;

    /** @var MethodBraintreeOfficial */
    protected $methodBraintreeOfficial;

    public function __construct()
    {
        $this->name = 'braintreeofficial';
        $this->tab = 'payments_gateways';
        $this->version = '@version@';
        $this->author = '202 ecommerce';
        $this->display = 'view';
        $this->module_key = '155f56797c33f1d34fcba757d3269a35';
        $this->is_eu_compatible = 1;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->controllers = ['payment', 'validation'];
        $this->bootstrap = true;

        $this->currencies = true;
        $this->currencies_mode = 'radio';

        parent::__construct();
        require_once realpath(dirname(__FILE__) . '/smarty/plugins') . '/modifier.braintreereplace.php';

        $this->displayName = $this->l('Braintree');
        $this->description = $this->l('Boost revenue with the mobile native Braintree module, driving more conversion in 45+ countries and accepting the latest payment methods.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
        $this->module_link = $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        $this->errors = '';
        $this->serviceBraintreeOfficialOrder = new ServiceBraintreeOfficialOrder();
        $this->serviceBraintreeOfficialCapture = new ServiceBraintreeOfficialCapture();
        $this->serviceBraintreeOfficialVaulting = new ServiceBraintreeOfficialVaulting();
        $this->setMethodBraitree(AbstractMethodBraintreeOfficial::load('BraintreeOfficial'));
        $this->moduleConfigs = [
            'BRAINTREEOFFICIAL_API_INTENT' => 'sale',
            'BRAINTREEOFFICIAL_3DSECURE' => 1,
            'BRAINTREEOFFICIAL_3DSECURE_AMOUNT' => 0,
            'BRAINTREEOFFICIAL_CUSTOMIZE_ORDER_STATUS' => 0,
            'BRAINTREEOFFICIAL_OS_REFUNDED' => (int) Configuration::get('PS_OS_REFUND'),
            'BRAINTREEOFFICIAL_OS_CANCELED' => (int) Configuration::get('PS_OS_CANCELED'),
            'BRAINTREEOFFICIAL_OS_ACCEPTED' => (int) Configuration::get('PS_OS_PAYMENT'),
            'BRAINTREEOFFICIAL_OS_CAPTURE_CANCELED' => (int) Configuration::get('PS_OS_CANCELED'),
            'BRAINTREEOFFICIAL_OS_ACCEPTED_TWO' => (int) Configuration::get('PS_OS_PAYMENT'),
            'BRAINTREEOFFICIAL_OS_PENDING' => (int) Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING'),
            'BRAINTREEOFFICIAL_OS_PROCESSING' => (int) Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING_VALIDATION'),
        ];
    }

    public function install()
    {
        $installer = new ModuleInstaller($this);

        try {
            $isPhpVersionCompliant = $installer->checkPhpVersion();
        } catch (Exception $e) {
            $this->_errors[] = $e->getMessage();

            return false;
        }

        if (($isPhpVersionCompliant && parent::install()) === false) {
            return false;
        }

        if (!$installer->installObjectModels()) {
            $this->_errors[] = $this->l('Fail registering object models');

            return false;
        }
        if (!$installer->installAdminControllers()) {
            $this->_errors[] = $this->l('Fail registering admin tabs');

            return false;
        }
        if (!$installer->installExtensions()) {
            $this->_errors[] = $this->l('Fail installing extensions');

            return false;
        }

        $this->registerHooks();

        if ($this->installOrderState() == false) {
            $this->_errors[] = $this->l('Fail registering order states');

            return false;
        }

        $this->moduleConfigs['BRAINTREEOFFICIAL_OS_PENDING'] = (int) Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING');
        $this->moduleConfigs['BRAINTREEOFFICIAL_OS_PROCESSING'] = (int) Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING_VALIDATION');
        $shops = Shop::getShops();

        foreach ($this->moduleConfigs as $key => $value) {
            if (Shop::isFeatureActive()) {
                foreach ($shops as $shop) {
                    Configuration::updateValue($key, $value, false, null, (int) $shop['id_shop']);
                }
            } else {
                Configuration::updateValue($key, $value);
            }
        }

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
     *
     * @return bool
     */
    public function uninstallOrderStates()
    {
        /** @var $orderState OrderState */
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
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminBraintreeOfficialSetup', true));
    }

    public function hookActionObjectCurrencyAddAfter($params)
    {
        $this->methodBraintreeOfficial->createForCurrency($params['object']->iso_code);
        $allCurrency = $this->methodBraintreeOfficial->getAllCurrency();
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
     *
     * @return string name of merchant account id for currency
     */
    public function getNameMerchantAccountForCurrency($currency, $mode = null)
    {
        if ($mode === null) {
            $mode = Configuration::get('BRAINTREEOFFICIAL_SANDBOX');
        }

        return Tools::strtoupper('braintreeofficial_merchant_account_id_' . $currency . '_' . ((int) $mode ? 'sandbox' : 'live'));
    }

    public function deleteMerchantAccountIds($mode)
    {
        foreach (Currency::getCurrencies() as $currency) {
            $maName = Tools::strtoupper($this->getNameMerchantAccountForCurrency($currency['iso_code'], $mode));
            Configuration::deleteFromContext($maName);
        }
    }

    public function hookActionOrderSlipAdd($params)
    {
        if (Tools::isSubmit('doPartialRefundBraintree')) {
            $braintreeOrder = $this->serviceBraintreeOfficialOrder->loadByOrderId($params['order']->id);

            if (Validate::isLoadedObject($braintreeOrder) == false) {
                return false;
            }

            $message = '';
            $ex_detailed_message = '';
            $capture = $this->serviceBraintreeOfficialCapture->loadByOrderBraintreeId($braintreeOrder->id);

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
                Tools::redirect($_SERVER['HTTP_REFERER'] . '&not_payed_capture=1');
            }

            $status = $this->methodBraintreeOfficial->getTransactionStatus($braintreeOrder);

            if ($status == 'submitted_for_settlement') {
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
                Tools::redirect($_SERVER['HTTP_REFERER'] . '&not_payed_capture=1');
            } else {
                try {
                    $refund_response = $this->methodBraintreeOfficial->partialRefund($params);
                } catch (Exception $e) {
                    $ex_detailed_message = $e->getMessage();
                }
            }

            if (isset($refund_response) && isset($refund_response['success']) && $refund_response['success']) {
                foreach ($refund_response as $key => $msg) {
                    $message .= $key . ' : ' . $msg . ";\r";
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
                    $message .= $key . ' : ' . $msg . ";\r";
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
                Tools::redirect($_SERVER['HTTP_REFERER'] . '&error_refund=1');
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
                Tools::redirect($_SERVER['HTTP_REFERER'] . '&error_refund=1');
            }
        }
    }

    public function hookActionOrderStatusPostUpdate(&$params)
    {
        if ($params['newOrderStatus']->paid == 1) {
            $capture = $this->serviceBraintreeOfficialCapture->getByOrderId($params['id_order']);
            $ps_order = new Order($params['id_order']);
            if (isset($capture['id_capture']) && $capture['id_capture']) {
                $this->setTransactionId($ps_order, $capture['id_capture']);
            }
        }
    }

    /**
     * Get url for BT onboarding
     *
     * @param object $ps_order PS order object
     * @param string $transaction_id payment transaction ID
     */
    public function setTransactionId($ps_order, $transaction_id)
    {
        Db::getInstance()->update(
            'order_payment',
            ['transaction_id' => pSQL($transaction_id)],
            'order_reference = "' . pSQL($ps_order->reference) . '"'
        );
    }

    public function hookActionOrderStatusUpdate(&$params)
    {
        /**@var $orderBraintree BraintreeOfficialOrder
         * @var $braintreeCapture BraintreeOfficialCapture
         */
        $orderBraintree = $this->serviceBraintreeOfficialOrder->loadByOrderId($params['id_order']);

        if (!Validate::isLoadedObject($orderBraintree)) {
            return false;
        }

        if ($orderBraintree->payment_method == 'sale') {
            $statusCanceled = (int) Configuration::get('BRAINTREEOFFICIAL_CUSTOMIZE_ORDER_STATUS') ? (int) Configuration::get('BRAINTREEOFFICIAL_OS_CANCELED') : (int) Configuration::get('PS_OS_CANCELED');
        } else {
            $statusCanceled = (int) Configuration::get('BRAINTREEOFFICIAL_OS_CAPTURE_CANCELED') ? (int) Configuration::get('BRAINTREEOFFICIAL_OS_CANCELED') : (int) Configuration::get('PS_OS_CANCELED');
        }
        $statusRefunded = (int) Configuration::get('BRAINTREEOFFICIAL_CUSTOMIZE_ORDER_STATUS') ? (int) Configuration::get('BRAINTREEOFFICIAL_OS_REFUNDED') : (int) Configuration::get('PS_OS_REFUND');

        $message = '';
        $ex_detailed_message = '';
        if ($params['newOrderStatus']->id == $statusCanceled) {
            $braintreeCapture = $this->serviceBraintreeOfficialCapture->loadByOrderBraintreeId($orderBraintree->id);

            try {
                $response_void = $this->methodBraintreeOfficial->void($orderBraintree);
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
                    $message .= $key . ' : ' . $msg . ";\r";
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
                    $message .= $key . ' : ' . $msg . ";\r";
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
                Tools::redirect($_SERVER['HTTP_REFERER'] . '&cancel_failed=1');
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

        if ($params['newOrderStatus']->id == $statusRefunded) {
            $braintreeCapture = $this->serviceBraintreeOfficialCapture->loadByOrderBraintreeId($orderBraintree->id);

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
                Tools::redirect($_SERVER['HTTP_REFERER'] . '&not_payed_capture=1');
            }
            $status = $this->methodBraintreeOfficial->getTransactionStatus($orderBraintree);

            if ($status == 'submitted_for_settlement') {
                try {
                    $refund_response = $this->methodBraintreeOfficial->void($orderBraintree);
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
                        $message .= $key . ' : ' . $msg . ";\r";
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
                        $refund_response['date_transaction']
                    );
                    ProcessLoggerHandler::closeLogger();
                }
            } else {
                try {
                    $refund_response = $this->methodBraintreeOfficial->refund($orderBraintree);
                } catch (PayPal\Exception\PPConnectionException $e) {
                    $ex_detailed_message = $this->l('Error connecting to ') . $e->getUrl();
                } catch (PayPal\Exception\PPMissingCredentialException $e) {
                    $ex_detailed_message = $e->errorMessage();
                } catch (PayPal\Exception\PPConfigurationException $e) {
                    $ex_detailed_message = $this->l('Invalid configuration. Please check your configuration file');
                } catch (PayPal\Exception\PayPalConnectionException $e) {
                    $decoded_message = json_decode($e->getData());
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
                        $message .= $key . ' : ' . $msg . ";\r";
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
                    $message .= $key . ' : ' . $msg . ";\r";
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
                Tools::redirect($_SERVER['HTTP_REFERER'] . '&error_refund=1');
            }
        }

        if ($params['newOrderStatus']->paid == 1) {
            $braintreeCapture = $this->serviceBraintreeOfficialCapture->loadByOrderBraintreeId($orderBraintree->id);
            if (!Validate::isLoadedObject($braintreeCapture)) {
                return false;
            }

            try {
                $capture_response = $this->methodBraintreeOfficial->confirmCapture($orderBraintree);
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
                    $message .= $key . ' : ' . $msg . ";\r";
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
                    $message .= $key . ' : ' . $msg . ";\r";
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
                Tools::redirect($_SERVER['HTTP_REFERER'] . '&error_capture=1');
            }
        }
    }

    public function hookDisplayAdminCartsView($params)
    {
        $params['class_logger'] = BraintreeOfficialLog::class;
        if ($result = $this->handleExtensionsHook(__FUNCTION__, $params)) {
            if (!is_null($result)) {
                return $result;
            }
        }
    }

    public function hookDisplayAdminOrderTop($params)
    {
        $return = $this->getAdminOrderPageMessages($params);
        $return .= $this->getPartialRefund();

        return $return;
    }

    protected function getPartialRefund()
    {
        $this->context->smarty->assign('chb_braintree_refund', $this->l('Refund Braintree'));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'braintreeofficial/views/templates/hook/partialRefund.tpl');
    }

    public function hookDisplayAdminOrder($params)
    {
        // Since Ps 1.7.7 this hook is displayed at bottom of a page and we should use a hook DisplayAdminOrderTop
        if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
            return false;
        }

        $return = $this->getAdminOrderPageMessages($params);
        $return .= $this->getPartialRefund();

        return $return;
    }

    public function getAdminOrderPageMessages($params)
    {
        $id_order = $params['id_order'];
        $order = new Order((int) $id_order);
        $braintreeOrder = $this->serviceBraintreeOfficialOrder->loadByOrderId($id_order);
        $return = '';

        if (Validate::isLoadedObject($braintreeOrder) == false) {
            return $return;
        }

        $braintreeCapture = $this->serviceBraintreeOfficialCapture->loadByOrderBraintreeId($braintreeOrder->id);

        if (Validate::isLoadedObject($braintreeOrder) == false) {
            return $return;
        }
        if ($braintreeOrder->sandbox) {
            $return .= $this->displayWarning($this->l('[SANDBOX] Please pay attention that payment for this order was made via Braintree Sandbox mode.'));
        }
        if (Tools::getValue('not_payed_capture')) {
            $return .= $this->displayError($this->l('You couldn\'t refund order, it\'s not payed yet.'));
        }
        if (Tools::getValue('error_refund')) {
            $return .= $this->displayError($this->l('We have an unexpected problem during refund operation. For more details please see the Braintree tab in the order details or Braintree Logs.'));
        }
        if (Tools::getValue('cancel_failed')) {
            $return .= $this->displayError($this->l('We have an unexpected problem during cancel operation. For more details please see the Braintree tab in the order details or Braintree Logs.'));
        }
        if ($order->current_state == Configuration::get('PS_OS_REFUND') && $braintreeOrder->payment_status == 'refunded') {
            $return .= $this->displayInformation($this->l('Your order is fully refunded by Braintree.'));
        }

        if ($order->getCurrentOrderState()->paid == 1 && Validate::isLoadedObject($braintreeCapture) && $braintreeCapture->id_capture) {
            $return .= $this->displayInformation($this->l('Your order is fully captured by Braintree.'));
        }
        if (Tools::getValue('error_capture')) {
            $return .= $this->displayError($this->l('We have an unexpected problem during capture operation. For more details please see the Braintree tab in the order details or Braintree Logs.'));
        }

        if ($braintreeOrder->total_paid != $braintreeOrder->total_prestashop) {
            $preferences = $this->context->link->getAdminLink('AdminPreferences', true);
            $return .= $this->displayWarning($this->l('Product pricing has been modified as your rounding settings aren\'t compliant with Braintree.') . ' ' .
                $this->l('To avoid automatic rounding to customer for Braintree payments, please update your rounding settings.') . ' ' .
                '<a target="_blank" href="' . $preferences . '">' . $this->l('Reed more.') . '</a>');
        }

        return $return;
    }

    public function hookDisplayBackOfficeHeader()
    {
        $diff_cron_time = date_diff(date_create('now'), date_create(Configuration::get('BRAINTREEOFFICIAL_CRON_TIME')));
        if ($diff_cron_time->d > 0 || $diff_cron_time->h > 1 || Configuration::get('BRAINTREEOFFICIAL_CRON_TIME') == false) {
            Configuration::updateValue('BRAINTREEOFFICIAL_CRON_TIME', date('Y-m-d H:i:s'));
            $bt_orders = $this->serviceBraintreeOfficialOrder->getBraintreeOrdersForValidation();

            if ($bt_orders) {
                $transactions = $this->methodBraintreeOfficial->searchTransactions($bt_orders);

                if (empty($transactions)) {
                    return;
                }
                if ((int) Configuration::get('BRAINTREEOFFICIAL_CUSTOMIZE_ORDER_STATUS')) {
                    $expectedStates = [
                        (int) Configuration::get('BRAINTREEOFFICIAL_OS_PENDING'),
                        (int) Configuration::get('BRAINTREEOFFICIAL_OS_ACCEPTED_TWO'),
                        (int) Configuration::get('BRAINTREEOFFICIAL_OS_PROCESSING'),
                    ];
                } else {
                    $expectedStates = [
                        (int) Configuration::get('PS_OS_PAYMENT'),
                        (int) Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING'),
                        (int) Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING_VALIDATION'),
                    ];
                }

                foreach ($transactions as $transaction) {
                    $braintreeOrder = $this->serviceBraintreeOfficialOrder->loadByTransactionId($transaction->id);

                    if (Validate::isLoadedObject($braintreeOrder) == false) {
                        continue;
                    }

                    $ps_order = new Order($braintreeOrder->id_order);
                    $paid_state = (int) Configuration::get('BRAINTREEOFFICIAL_CUSTOMIZE_ORDER_STATUS') ? (int) Configuration::get('BRAINTREEOFFICIAL_OS_ACCEPTED') : (int) Configuration::get('PS_OS_PAYMENT');
                    $ps_order_details = OrderDetail::getList($braintreeOrder->id_order);

                    foreach ($ps_order_details as $order_detail) {
                        // Switch to back order if needed
                        if (Configuration::get('PS_STOCK_MANAGEMENT')
                            && (int) $order_detail['product_quantity'] > (int) $order_detail['product_quantity_in_stock']) {
                            $paid_state = Configuration::get('PS_OS_OUTOFSTOCK_PAID');
                        }
                    }

                    switch ($transaction->status) {
                        case 'declined':
                            if ($braintreeOrder->payment_status != 'declined') {
                                $braintreeOrder->payment_status = $transaction->status;
                                $braintreeOrder->update();

                                if (in_array($ps_order->getCurrentState(), $expectedStates)) {
                                    $ps_order->setCurrentState(Configuration::get('PS_OS_ERROR'));
                                }
                            }
                            break;
                        case 'settled':
                            if ($braintreeOrder->payment_status != 'settled') {
                                $braintreeOrder->payment_status = $transaction->status;
                                $braintreeOrder->update();

                                if (in_array($ps_order->getCurrentState(), $expectedStates)) {
                                    $ps_order->setCurrentState($paid_state);
                                    $this->setTransactionId($ps_order, $transaction->id);
                                }
                            }
                            break;
                        case 'settling': // waiting
                            // do nothing and check later one more time
                            break;
                        case 'submit_for_settlement': // waiting
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
        if (Configuration::get('BRAINTREEOFFICIAL_VAULTING')) {
            return $this->display(__FILE__, 'displayCustomerAccount.tpl');
        }
    }

    public function hookDisplayMyAccountBlock()
    {
        if (Configuration::get('BRAINTREEOFFICIAL_VAULTING')) {
            return $this->display(__FILE__, 'displayMyAccountBlock.tpl');
        }
    }

    public function hookHeader()
    {
        if (Tools::getValue('controller') == 'order') {
            if (!$this->checkActiveModule()) {
                return;
            }

            $this->context->controller->addJqueryPlugin('fancybox');
            $this->context->controller->registerJavascript($this->name . '-braintreegateway-client', 'https://js.braintreegateway.com/web/3.57.0/js/client.min.js', ['server' => 'remote']);
            $this->context->controller->registerJavascript($this->name . '-braintreegateway-hosted', 'https://js.braintreegateway.com/web/3.57.0/js/hosted-fields.min.js', ['server' => 'remote']);
            $this->context->controller->registerJavascript($this->name . '-braintreegateway-data', 'https://js.braintreegateway.com/web/3.57.0/js/data-collector.min.js', ['server' => 'remote']);
            $this->context->controller->registerJavascript($this->name . '-braintreegateway-3ds', 'https://js.braintreegateway.com/web/3.57.0/js/three-d-secure.min.js', ['server' => 'remote']);
            $this->context->controller->registerStylesheet($this->name . '-braintreecss', 'modules/' . $this->name . '/views/css/braintree.css');
            $this->addJsVarsLangBT();
            $this->addJsVarsBT();
            $this->context->controller->registerJavascript($this->name . '-braintreejs', 'modules/' . $this->name . '/views/js/payment_bt.js');

            if (Configuration::get('BRAINTREEOFFICIAL_ACTIVATE_PAYPAL')) {
                $this->context->controller->registerJavascript($this->name . '-pp-braintree-checkout-min', 'https://js.braintreegateway.com/web/3.57.0/js/paypal-checkout.min.js', ['server' => 'remote']);
                $this->context->controller->registerJavascript($this->name . '-pp-braintree-checkout', 'https://www.paypalobjects.com/api/checkout.js', ['server' => 'remote']);
                Media::addJsDefL('empty_nonce', $this->l('Please click on the PayPal Pay button first'));
                $this->addJsVarsPB();
                $this->context->controller->registerJavascript($this->name . '-pp-braintreejs', 'modules/' . $this->name . '/views/js/payment_pbt.js');
            }

            if (isset($this->context->cookie->payment_method_nonce) && isset($this->context->cookie->brainteeofficial_payer_email)) {
                $this->context->smarty->assign('payerEmail', $this->context->cookie->brainteeofficial_payer_email);
                $carrierFees = $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);

                if ($carrierFees == 0) {
                    $messageForCustomer = $this->context->smarty
                        ->assign('isSandbox', $this->methodBraintreeOfficial->isSandbox())
                        ->fetch('module:braintreeofficial/views/templates/front/_partials/messageForCustomerOne.tpl');
                } else {
                    $this->context->smarty->assign('carrierFees', Tools::displayPrice($carrierFees));
                    $this->context->smarty->assign('isSandbox', $this->methodBraintreeOfficial->isSandbox());
                    $messageForCustomer = $this->context->smarty->fetch('module:braintreeofficial/views/templates/front/_partials/messageForCustomerTwo.tpl');
                }

                Media::addJsDefL('scPaypalCheckedMsg', $messageForCustomer);
                $this->context->controller->registerJavascript($this->name . '-shortcut-payment', 'modules/' . $this->name . '/views/js/shortcutPayment.js');
            }
        } elseif (Tools::getValue('controller') == 'cart') {
            if (!$this->checkActiveModule()) {
                return;
            }

            $resources = [
                'https://js.braintreegateway.com/web/3.57.0/js/client.min.js',
                'https://js.braintreegateway.com/web/3.57.0/js/hosted-fields.min.js',
                'https://js.braintreegateway.com/web/3.57.0/js/data-collector.min.js',
                'https://js.braintreegateway.com/web/3.57.0/js/three-d-secure.min.js',
            ];
            $this->context->controller->registerStylesheet($this->name . '-braintreecss', 'modules/' . $this->name . '/views/css/braintree.css');

            if (Configuration::get('BRAINTREEOFFICIAL_ACTIVATE_PAYPAL')) {
                $resources_paypal = [
                    'https://js.braintreegateway.com/web/3.57.0/js/paypal-checkout.min.js',
                ];

                if (Configuration::get('BRAINTREEOFFICIAL_EXPRESS_CHECKOUT_SHORTCUT_CART')) {
                    Media::addJsDef($this->methodBraintreeOfficial->getShortcutJsVars(BRAINTREE_CART_PAGE));
                    $this->context->controller->registerJavascript($this->name . '-braintreeShortcut', 'modules/' . $this->name . '/views/js/btShortcut.js');
                    $this->context->controller->registerJavascript($this->name . '-pp-braintree-checkout-min', 'https://js.braintreegateway.com/web/3.57.0/js/paypal-checkout.min.js', ['server' => 'remote']);
                    $this->context->controller->registerJavascript($this->name . '-pp-braintree-checkout', 'https://www.paypalobjects.com/api/checkout.js', ['server' => 'remote']);
                    $this->context->controller->registerJavascript($this->name . '-pp-braintree-client', 'https://js.braintreegateway.com/web/3.57.0/js/client.min.js', ['server' => 'remote']);
                }

                $resources = array_merge($resources, $resources_paypal);
            }

            $this->context->smarty->assign('prefetchResources', $resources);

            return $this->context->smarty->fetch('module:braintreeofficial/views/templates/front/_partials/prefetch.tpl');
        } elseif ($this->context->controller instanceof ProductController) {
            $this->context->controller->registerStylesheet($this->name . '-braintreecss', 'modules/' . $this->name . '/views/css/braintree.css');
            if (Configuration::get('BRAINTREEOFFICIAL_ACTIVATE_PAYPAL') && Configuration::get('BRAINTREEOFFICIAL_EXPRESS_CHECKOUT_SHORTCUT')) {
                Media::addJsDef($this->methodBraintreeOfficial->getShortcutJsVars(BRAINTREE_PRODUCT_PAGE));
                $this->context->controller->registerJavascript($this->name . '-braintreeShortcut', 'modules/' . $this->name . '/views/js/btShortcut.js');
                $this->context->controller->registerJavascript($this->name . '-pp-braintree-checkout-min', 'https://js.braintreegateway.com/web/3.57.0/js/paypal-checkout.min.js', ['server' => 'remote']);
                $this->context->controller->registerJavascript($this->name . '-pp-braintree-checkout', 'https://www.paypalobjects.com/api/checkout.js', ['server' => 'remote']);
                $this->context->controller->registerJavascript($this->name . '-pp-braintree-client', 'https://js.braintreegateway.com/web/3.57.0/js/client.min.js', ['server' => 'remote']);
            }
        }
    }

    public function checkActiveModule()
    {
        $active = false;
        $modules = Hook::getHookModuleExecList('paymentOptions');
        if (empty($modules)) {
            return;
        }
        foreach ($modules as $module) {
            if ($module['module'] == $this->name) {
                $active = true;
            }
        }

        return $active;
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
        Media::addJsDefL('bt_translations_3ds_failed_1', $this->l('Authentication unsuccessful for this transaction. Please try another card or payment method.'));
        Media::addJsDefL('bt_translations_3ds_failed_2', $this->l('Authentication missing for this transaction.'));
    }

    public function hookDisplayAdminOrderTabOrder($params)
    {
        $params['class_logger'] = BraintreeOfficialLog::class;
        if ($result = $this->handleExtensionsHook(__FUNCTION__, $params)) {
            if (!is_null($result)) {
                return $result;
            }
        }
    }

    public function hookDisplayAdminOrderTabLink($params)
    {
        $order = new Order((int) $params['id_order']);
        $params['order'] = $order;
        $return = $this->hookDisplayAdminOrderTabOrder($params);

        return $return;
    }

    public function hookDisplayAdminOrderTabContent($params)
    {
        $order = new Order((int) $params['id_order']);
        $params['order'] = $order;

        return $this->hookDisplayAdminOrderContentOrder($params);
    }

    public function hookDisplayAdminOrderContentOrder($params)
    {
        $params['class_logger'] = BraintreeOfficialLog::class;
        if ($result = $this->handleExtensionsHook(__FUNCTION__, $params)) {
            if (!is_null($result)) {
                return $result;
            }
        }
    }

    public function hookPaymentOptions($params)
    {
        $payments_options = [];
        /* for avoiding the connection exception need to verify if module configured correct */
        if ($this->methodBraintreeOfficial->isConfigured() == false) {
            return $payments_options;
        }

        /* for avoiding the exception of authorization need to verify mode of payment currency and merchant account */
        if ($this->getCurrentModePaymentCurrency() == BRAINTREE_PAYMENT_CUSTOMER_CURRENCY
            && $this->merchantAccountForCurrencyConfigured() == false) {
            return $payments_options;
        }

        if (Configuration::get('BRAINTREEOFFICIAL_ACTIVATE_PAYPAL')) {
            $embeddedOption = new PaymentOption();
            $action_text = $this->l('Pay with PayPal');
            $embeddedOption->setCallToActionText($action_text)
                ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/paypal.png'))
                ->setModuleName($this->name)
                ->setAdditionalInformation($this->generateFormPB())
                ->setAction('javascript:BraintreePaypalSubmitPayment();');
            $payments_options[] = $embeddedOption;

            if (isset($this->context->cookie->payment_method_nonce) && isset($this->context->cookie->brainteeofficial_payer_email)) {
                $paymentOption = new PaymentOption();
                $action_text = $this->l('Pay with paypal express checkout');
                $paymentOption->setCallToActionText($action_text);
                $paymentOption->setModuleName('braintreeofficial-shortcut');
                $paymentOption->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true));
                $payments_options[] = $paymentOption;
            }
        }

        $embeddedOption = new PaymentOption();
        $embeddedOption->setCallToActionText($this->l('Pay with card'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/mini-cards.png'))
            ->setAdditionalInformation($this->generateFormBt())
            ->setAction('javascript:BraintreeSubmitPayment();');
        $payments_options[] = $embeddedOption;

        return $payments_options;
    }

    public function generateFormPB()
    {
        $clientToken = $this->methodBraintreeOfficial->init();
        if (isset($clientToken['error_code'])) {
            $this->context->smarty->assign([
                'init_error' => $this->l('Error Braintree initialization ') . $clientToken['error_code'] . ' : ' . $clientToken['error_msg'],
            ]);
        }
        $this->context->smarty->assign([
            'braintreeSubmitUrl' => $this->context->link->getModuleLink($this->name, 'validation', [], true),
            'baseDir' => $this->context->link->getBaseLink($this->context->shop->id, true),
            'path' => $this->_path,
            'bt_method' => BRAINTREE_PAYPAL_PAYMENT,
            'active_vaulting' => Configuration::get('BRAINTREEOFFICIAL_VAULTING'),
            'show_paypal_benefits' => Configuration::get('BRAINTREEOFFICIAL_SHOW_PAYPAL_BENEFITS'),
            'isSandbox' => $this->methodBraintreeOfficial->isSandbox(),
        ]);
        if (Configuration::get('BRAINTREEOFFICIAL_VAULTING')) {
            $payment_methods = $this->serviceBraintreeOfficialVaulting->getCustomerMethods($this->context->customer->id, BRAINTREE_PAYPAL_PAYMENT);
            $this->context->smarty->assign([
                'payment_methods' => $payment_methods,
            ]);
        }

        return $this->context->smarty->fetch('module:braintreeofficial/views/templates/front/payment_pbt.tpl');
    }

    public function addJsVarsPB()
    {
        $clientToken = $this->methodBraintreeOfficial->init();
        Media::addJsDef([
            'paypal_braintree_authorization' => $clientToken,
            'paypal_braintree_amount' => $this->context->cart->getOrderTotal(),
            'paypal_braintree_mode' => $this->methodBraintreeOfficial->mode == 'SANDBOX' ? Tools::strtolower($this->methodBraintreeOfficial->mode) : 'production',
            'paypal_braintree_currency' => $this->context->currency->iso_code,
            'envLocale' => str_replace('-', '_', $this->context->language->locale),
        ]);
    }

    public function generateFormBT()
    {
        $clientToken = $this->methodBraintreeOfficial->init();

        if (isset($clientToken['error_code'])) {
            $this->context->smarty->assign([
                'init_error' => $this->l('Error Braintree initialization ') . $clientToken['error_code'] . ' : ' . $clientToken['error_msg'],
            ]);
        }

        if (Configuration::get('BRAINTREEOFFICIAL_VAULTING')) {
            $payment_methods = $this->serviceBraintreeOfficialVaulting->getCustomerMethods($this->context->customer->id, BRAINTREE_CARD_PAYMENT);

            foreach ($payment_methods as $key => $method) {
                $nonce = $this->methodBraintreeOfficial->createMethodNonce($method['token']);
                $payment_methods[$key]['nonce'] = $nonce;
            }

            $this->context->smarty->assign([
                'active_vaulting' => true,
                'payment_methods' => $payment_methods,
            ]);
        }

        $this->context->smarty->assign([
            'error_msg' => Tools::getValue('bt_error_msg'),
            'braintreeToken' => $clientToken,
            'braintreeSubmitUrl' => $this->context->link->getModuleLink($this->name, 'validation', [], true),
            'baseDir' => $this->context->link->getBaseLink($this->context->shop->id, true),
            'method_bt' => BRAINTREE_CARD_PAYMENT,
            'isSandbox' => $this->methodBraintreeOfficial->isSandbox(),
        ]);

        return $this->context->smarty->fetch('module:braintreeofficial/views/templates/front/payment_bt.tpl');
    }

    public function addJsVarsBT()
    {
        $clientToken = $this->methodBraintreeOfficial->init();
        $use3dVerification = $this->use3dVerification();

        Media::addJsDef([
            'authorization' => $clientToken,
            'controllerValidation' => $this->context->link->getModuleLink($this->name, 'validation', [], true),
            'use3dVerification' => $use3dVerification,
        ]);
    }

    /**
     * @return bool
     * */
    public function use3dVerification()
    {
        $use3dVerification = (int) Configuration::get('BRAINTREEOFFICIAL_3DSECURE');
        $use3dVerification &= (int) Configuration::get('BRAINTREEOFFICIAL_3DSECURE_AMOUNT') <= $this->context->cart->getOrderTotal(true, Cart::BOTH);

        return $use3dVerification;
    }

    /**
     * Check if we need convert currency
     *
     * @return bool|int currency id
     */
    public function needConvert()
    {
        $currency_mode = Currency::getPaymentCurrenciesSpecial($this->id);
        $mode_id = $currency_mode['id_currency'];
        if ($mode_id == -2) {
            return (int) Configuration::get('PS_CURRENCY_DEFAULT');
        } elseif ($mode_id == -1) {
            return false;
        } elseif ($mode_id != $this->context->currency->id) {
            return (int) $mode_id;
        } else {
            return false;
        }
    }

    /**
     * Get payment currency iso code
     *
     * @return string currency iso code
     */
    public function getPaymentCurrencyIso()
    {
        if ($id_currency = $this->needConvert()) {
            $currency = new Currency((int) $id_currency);
        } else {
            $currency = Context::getContext()->currency;
        }

        return $currency->iso_code;
    }

    public function validateOrder($id_cart, $id_order_state, $amount_paid, $payment_method = 'Unknown', $message = null, $transaction = [], $currency_special = null, $dont_touch_amount = false, $secure_key = false, ?Shop $shop = null, $order_reference = null)
    {
        if ($this->needConvert()) {
            $amount_paid_curr = Tools::ps_round(Tools::convertPrice($amount_paid, new Currency($currency_special), true), 2);
        } else {
            $amount_paid_curr = Tools::ps_round($amount_paid, 2);
        }
        $amount_paid = Tools::ps_round($amount_paid, 2);

        $cart = new Cart((int) $id_cart);
        $total_ps = Tools::ps_round($cart->getOrderTotal(true, Cart::BOTH), 2);
        if ($amount_paid_curr > $total_ps + 0.10 || $amount_paid_curr < $total_ps - 0.10) {
            $total_ps = $amount_paid_curr;
        }

        try {
            parent::validateOrder(
                (int) $id_cart,
                (int) $id_order_state,
                (float) $total_ps,
                $payment_method,
                $message,
                ['transaction_id' => isset($transaction['transaction_id']) ? $transaction['transaction_id'] : ''],
                $currency_special,
                $dont_touch_amount,
                $secure_key,
                $shop
            );
        } catch (Exception $e) {
            $log = 'Order validation error : ' . $e->getMessage() . ';';
            $log .= ' File: ' . $e->getFile() . ';';
            $log .= ' Line: ' . $e->getLine() . ';';
            ProcessLoggerHandler::openLogger();
            ProcessLoggerHandler::logError(
                $log,
                isset($transaction['transaction_id']) ? $transaction['transaction_id'] : null,
                null,
                (int) $id_cart,
                $this->context->shop->id,
                isset($transaction['payment_tool']) && $transaction['payment_tool'] ? $transaction['payment_tool'] : 'Braintree',
                (int) Configuration::get('BRAINTREEOFFICIAL_SANDBOX'),
                isset($transaction['date_transaction']) ? $transaction['date_transaction'] : null
            );
            ProcessLoggerHandler::closeLogger();

            $this->currentOrder = (int) Order::getIdByCartId((int) $id_cart);

            if ($this->currentOrder == false) {
                $msg = $this->l('Order validation error : ') . $e->getMessage() . '. ';
                if (isset($transaction['transaction_id']) && $id_order_state != Configuration::get('PS_OS_ERROR')) {
                    $msg .= $this->l('Attention, your payment is made. Please, contact customer support. Your transaction ID is  : ') . $transaction['transaction_id'];
                }
                Tools::redirect(Context::getContext()->link->getModuleLink($this->name, 'error', ['error_msg' => $msg, 'no_retry' => true]));
            }
        }

        $message = $this->getPaymentMessage($transaction);

        ProcessLoggerHandler::openLogger();
        ProcessLoggerHandler::logInfo(
            $message,
            isset($transaction['transaction_id']) ? $transaction['transaction_id'] : null,
            $this->currentOrder,
            (int) $id_cart,
            $this->context->shop->id,
            isset($transaction['payment_tool']) && $transaction['payment_tool'] ? $transaction['payment_tool'] : 'PayPal',
            (int) Configuration::get('BRAINTREEOFFICIAL_SANDBOX'),
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

            $sql = 'UPDATE `' . _DB_PREFIX_ . 'order_payment`
		    SET `amount` = ' . (float) $amount_paid_curr . '
		    WHERE  `order_reference` = "' . pSQL($order->reference) . '"';
            Db::getInstance()->execute($sql);
        }

        $braintree_order = new BraintreeOfficialOrder();
        $braintree_order->id_order = $this->currentOrder;
        $braintree_order->id_cart = $id_cart;
        $braintree_order->id_transaction = $transaction['transaction_id'];
        $braintree_order->id_payment = $transaction['id_payment'];
        $braintree_order->payment_method = $transaction['payment_method'];
        $braintree_order->currency = $transaction['currency'];
        $braintree_order->total_paid = $amount_paid;
        $braintree_order->payment_status = $transaction['payment_status'];
        $braintree_order->total_prestashop = $total_ps;
        $braintree_order->payment_tool = isset($transaction['payment_tool']) ? $transaction['payment_tool'] : 'Braintree';
        $braintree_order->sandbox = (int) Configuration::get('BRAINTREEOFFICIAL_SANDBOX');
        $braintree_order->save();

        if ($transaction['capture']) {
            $braintree_capture = new BraintreeOfficialCapture();
            $braintree_capture->id_braintreeofficial_order = $braintree_order->id;
            $braintree_capture->save();
        }
    }

    /**
     * @param $transactionInfo array
     *
     * @return string
     * */
    protected function getPaymentMessage($transactionInfo)
    {
        switch ($transactionInfo['payment_status']) {
            case 'authorized':
                $message = $this->l('Payment authorized : waiting for payment validation by admin');
                break;
            case 'settling':
                $message = $this->l('Payment authorized : Settling');
                break;
            case 'submitted_for_settlement':
                $message = $this->l('Payment processing (authorized)');
                break;
            default:
                $message = $transactionInfo['payment_status'];
        }

        return $message;
    }

    public function isOneOrder($order_reference)
    {
        $query = new DBQuery();
        $query->select('COUNT(*)');
        $query->from('orders');
        $query->where('reference = "' . pSQL($order_reference) . '"');
        $countOrders = (int) DB::getInstance()->getValue($query);

        return $countOrders == 1;
    }

    /**
     * Create order state
     *
     * @return bool
     */
    public function installOrderState()
    {
        if (!Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING')
            || !Validate::isLoadedObject(new OrderState((int) Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING')))
        ) {
            if (Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING')
                || Validate::isLoadedObject(new OrderState((int) Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING')))
            ) {
                $order_state = new OrderState((int) Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING'));
            } else {
                $order_state = new OrderState();
            }

            $order_state->name = [];
            foreach (Language::getLanguages() as $language) {
                $isoCode = Tools::strtolower($language['iso_code']);
                switch ($isoCode) {
                    case 'fr':
                        $order_state->name[$language['id_lang']] = 'Paiement en attente (autorisé)';
                        break;
                    case 'es':
                        $order_state->name[$language['id_lang']] = 'Pagamento in corso (autorizzato)';
                        break;
                    case 'it':
                        $order_state->name[$language['id_lang']] = 'Pagamento in sospeso (autorizzato)';
                        break;
                    default:
                        $order_state->name[$language['id_lang']] = 'Payment pending (authorized)';
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
                $source = _PS_MODULE_DIR_ . $this->name . '/views/img/os_braintree.png';
                $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
                copy($source, $destination);
            }

            if (Shop::isFeatureActive()) {
                $shops = Shop::getShops();
                foreach ($shops as $shop) {
                    Configuration::updateValue('BRAINTREEOFFICIAL_OS_AWAITING', (int) $order_state->id, false, null, (int) $shop['id_shop']);
                }
            } else {
                Configuration::updateValue('BRAINTREEOFFICIAL_OS_AWAITING', (int) $order_state->id);
            }
        }

        if (!Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING_VALIDATION')
            || !Validate::isLoadedObject(new OrderState((int) Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING_VALIDATION')))
        ) {
            if (Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING_VALIDATION')
                || Validate::isLoadedObject(new OrderState((int) Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING_VALIDATION')))
            ) {
                $order_state = new OrderState((int) Configuration::get('PAYPAL_BRAINTREE_OS_AWAITING_VALIDATION'));
            } else {
                $order_state = new OrderState();
            }
            $order_state->name = [];

            foreach (Language::getLanguages() as $language) {
                $isoCode = Tools::strtolower($language['iso_code']);
                switch ($isoCode) {
                    case 'fr':
                        $order_state->name[$language['id_lang']] = 'Paiement en cours (autorisé)';
                        break;
                    case 'es':
                        $order_state->name[$language['id_lang']] = 'Pago en proceso (autorizado)';
                        break;
                    case 'it':
                        $order_state->name[$language['id_lang']] = 'Pago pendiente (autorizado)';
                        break;
                    default:
                        $order_state->name[$language['id_lang']] = 'Payment processing (authorized)';
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
                $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
                copy($source, $destination);
            }

            if (Shop::isFeatureActive()) {
                $shops = Shop::getShops();
                foreach ($shops as $shop) {
                    Configuration::updateValue('BRAINTREEOFFICIAL_OS_AWAITING_VALIDATION', (int) $order_state->id, false, null, (int) $shop['id_shop']);
                }
            } else {
                Configuration::updateValue('BRAINTREEOFFICIAL_OS_AWAITING_VALIDATION', (int) $order_state->id);
            }
        }

        return true;
    }

    public function displayInformation($message)
    {
        $this->context->smarty->assign('message', $message);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/_partials/alertInfo.tpl');
    }

    public function isSslActive()
    {
        return Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
    }

    /**
     * TODO
     * Reset Module only if merchant choose to keep data on modal
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
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
            if (is_callable([$extension, $hookName])) {
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
     *
     * @param $action
     * @param $method
     * @param $hookName
     * @param $configuration
     *
     * @return bool
     *
     * @throws ReflectionException
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
            if (is_callable([$extension, $method])) {
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
    public function addCheckboxCarrierRestrictionsForModule(array $shops = [])
    {
        if (!$shops) {
            $shops = Shop::getShops(true, null, true);
        }

        $carriers = Carrier::getCarriers($this->context->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
        $carrier_ids = [];
        foreach ($carriers as $carrier) {
            $carrier_ids[] = $carrier['id_reference'];
        }

        foreach ($shops as $s) {
            foreach ($carrier_ids as $id_carrier) {
                if (!Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'module_carrier` (`id_module`, `id_shop`, `id_reference`)
				VALUES (' . (int) $this->id . ', "' . (int) $s . '", ' . (int) $id_carrier . ')')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Add radio currency restrictions for a new module.
     *
     * @param array $shops
     *
     * @return bool
     */
    public function addRadioCurrencyRestrictionsForModule(array $shops = [])
    {
        if (!$shops) {
            $shops = Shop::getShops(true, null, true);
        }

        $query = 'INSERT INTO `' . _DB_PREFIX_ . 'module_currency` (`id_module`, `id_shop`, `id_currency`) VALUES (%d, %d, %d)';

        foreach ($shops as $s) {
            if (!Db::getInstance()->execute(sprintf($query, $this->id, $s, BRAINTREE_PAYMENT_CUSTOMER_CURRENCY))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return choosed mode of currency restriction
     *
     * @return int|null
     */
    public function getCurrentModePaymentCurrency()
    {
        $currency_mode = Currency::getPaymentCurrenciesSpecial($this->id);

        if (isset($currency_mode['id_currency'])) {
            return (int) $currency_mode['id_currency'];
        } else {
            return null;
        }
    }

    /**
     * Check if all merchant account ids for currency are configured
     *
     * @return bool
     */
    public function merchantAccountForCurrencyConfigured()
    {
        $allCurrency = Currency::getCurrencies();

        if (empty($allCurrency)) {
            return false;
        }

        $result = true;

        foreach ($allCurrency as $currency) {
            $result &= (bool) Configuration::get($this->getNameMerchantAccountForCurrency($currency['iso_code']));
        }

        return (bool) $result;
    }

    public function setMethodBraitree(AbstractMethodBraintreeOfficial $method)
    {
        $this->methodBraintreeOfficial = $method;
    }

    public function registerHooks()
    {
        $result = true;
        $hooksUnregistered = $this->getHooksUnregistered();

        if (empty($hooksUnregistered)) {
            return $result;
        }

        foreach ($hooksUnregistered as $hookName) {
            $result &= $this->registerHook($hookName);
        }

        return $result;
    }

    /**
     * @return array return the unregistered hooks
     */
    public function getHooksUnregistered()
    {
        $hooksUnregistered = [];

        foreach ($this->hooks as $hookName) {
            $alias = '';

            try {
                $alias = Hook::getNameById(Hook::getIdByName($hookName));
            } catch (Exception $e) {
            }

            $hookName = empty($alias) ? $hookName : $alias;

            if (Hook::isModuleRegisteredOnHook($this, $hookName, $this->context->shop->id)) {
                continue;
            }

            $hooksUnregistered[] = $hookName;
        }

        return $hooksUnregistered;
    }

    /**
     * @return bool
     */
    public function renameOrderState()
    {
        $result = true;
        $languages = Language::getLanguages();
        $braintreeAwaiting = new OrderState((int) Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING'));
        $braintreeAwaitingValidation = new OrderState((int) Configuration::get('BRAINTREEOFFICIAL_OS_AWAITING_VALIDATION'));

        if (Validate::isLoadedObject($braintreeAwaiting)) {
            foreach ($languages as $language) {
                $isoCode = Tools::strtolower($language['iso_code']);
                switch ($isoCode) {
                    case 'fr':
                        $braintreeAwaiting->name[$language['id_lang']] = 'Paiement en attente (autorisé)';
                        break;
                    case 'es':
                        $braintreeAwaiting->name[$language['id_lang']] = 'Pagamento in corso (autorizzato)';
                        break;
                    case 'it':
                        $braintreeAwaiting->name[$language['id_lang']] = 'Pagamento in sospeso (autorizzato)';
                        break;
                    default:
                        $braintreeAwaiting->name[$language['id_lang']] = 'Payment pending (authorized)';
                }
            }

            $result &= $braintreeAwaiting->save();
        } else {
            $result &= false;
        }

        if (Validate::isLoadedObject($braintreeAwaitingValidation)) {
            foreach ($languages as $language) {
                $isoCode = Tools::strtolower($language['iso_code']);
                switch ($isoCode) {
                    case 'fr':
                        $braintreeAwaitingValidation->name[$language['id_lang']] = 'Paiement en cours (autorisé)';
                        break;
                    case 'es':
                        $braintreeAwaitingValidation->name[$language['id_lang']] = 'Pago en proceso (autorizado)';
                        break;
                    case 'it':
                        $braintreeAwaitingValidation->name[$language['id_lang']] = 'Pago pendiente (autorizado)';
                        break;
                    default:
                        $braintreeAwaitingValidation->name[$language['id_lang']] = 'Payment processing (authorized)';
                }
            }

            $result &= $braintreeAwaitingValidation->save();
        } else {
            $result &= false;
        }

        return $result;
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        if ((int) Configuration::get('BRAINTREEOFFICIAL_ACTIVATE_PAYPAL') == false || (int) Configuration::get('BRAINTREEOFFICIAL_EXPRESS_CHECKOUT_SHORTCUT_CART') == false) {
            return false;
        }

        if ($this->context->cart->nbProducts() == 0) {
            return false;
        }

        return $this->context->smarty->fetch('module:braintreeofficial/views/templates/hook/shortCut.tpl');
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        if ((int) Configuration::get('BRAINTREEOFFICIAL_ACTIVATE_PAYPAL') == false || (int) Configuration::get('BRAINTREEOFFICIAL_EXPRESS_CHECKOUT_SHORTCUT') == false) {
            return false;
        }

        if (($this->context->controller instanceof ProductController) == false) {
            return false;
        }

        return $this->context->smarty->fetch('module:braintreeofficial/views/templates/hook/shortCut.tpl');
    }

    public function getIdStateByPaypalCode($isoState, $isoCountry)
    {
        $idState = 0;
        $idCountry = Country::getByIso($isoCountry);
        if (Country::containsStates($idCountry)) {
            if (isset(self::$state_iso_code_matrix[$isoCountry])) {
                $matrix = self::$state_iso_code_matrix[$isoCountry];
                $isoState = array_search(Tools::strtolower($isoState), array_map('strtolower', $matrix));
            }
            if ($idState = (int) State::getIdByIso(Tools::strtoupper($isoState), $idCountry)) {
                $idState = $idState;
            } elseif ($idState = State::getIdByName(pSQL(trim($isoState)))) {
                $state = new State((int) $idState);
                if ($state->id_country == $idCountry) {
                    $idState = $state->id;
                }
            }
        }

        return $idState;
    }

    public function hookActionBeforeCartUpdateQty($params)
    {
        Context::getContext()->cookie->__unset('payment_method_nonce');
        Context::getContext()->cookie->__unset('brainteeofficial_payer_email');
    }

    public function getOrderStatuses()
    {
        $orderStatuses = [
            [
                'id' => 0,
                'name' => $this->l('Choose status'),
            ],
        ];
        $prestashopOrderStatuses = OrderState::getOrderStates($this->context->language->id);

        foreach ($prestashopOrderStatuses as $prestashopOrderStatus) {
            $orderStatuses[] = [
                'id' => $prestashopOrderStatus['id_order_state'],
                'name' => $prestashopOrderStatus['name'],
            ];
        }

        return $orderStatuses;
    }
}
