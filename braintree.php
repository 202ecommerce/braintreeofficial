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
require_once(_PS_MODULE_DIR_ . 'braintree/classes/BraintreeCapture.php');
require_once(_PS_MODULE_DIR_ . 'braintree/classes/BraintreeOrder.php');
require_once(_PS_MODULE_DIR_ . 'braintree/classes/BraintreeVaulting.php');
require_once(_PS_MODULE_DIR_ . 'braintree/classes/BraintreeCustomer.php');
require_once(_PS_MODULE_DIR_ . 'braintree/classes/AbstractMethodBraintree.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

use BraintreePPBTlib\Module\PaymentModule;
use BraintreePPBTlib\Extensions\ProcessLogger\ProcessLoggerExtension;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

const BT_CARD_PAYMENT = 'card-braintree';
const BT_PAYPAL_PAYMENT = 'paypal-braintree';

class Braintree extends PaymentModule
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
        'actionBeforeCartUpdateQty',
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
        'BraintreeCapture',
        'BraintreeOrder',
        'BraintreeVaulting',
        'BraintreeCustomer'
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
                'en' => 'Fees',
                'fr' => 'Fees'
            ),
            'class_name' => 'AdminBraintreeFees',
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
        )
    );

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

        $this->displayName = $this->l('Braintree');
        $this->description = $this->l('Benefit from Braintree’s complete payments platform and grow your business online, on mobile and internationally. Accept credit cards, debit cards and PayPal payments.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
        $this->module_link = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;

        $this->errors = '';
    }

    public function install()
    {
        // Install default
        if (!parent::install()) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        // Uninstall default
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminBraintreeSetup', true));
    }

    public function hookActionAdminControllerSetMedia()
    {

    }

    public function hookActionBeforeCartUpdateQty($params)
    {

    }

    public function hookActionObjectCurrencyAddAfter($params)
    {

    }

    public function hookActionOrderSlipAdd($params)
    {

    }
    public function hookActionOrderStatusPostUpdate(&$params)
    {

    }

    public function hookActionOrderStatusUpdate(&$params)
    {

    }

    public function hookDisplayAdminCartsView($params)
    {

    }

    public function hookDisplayAdminOrder($params)
    {

    }

    public function hookDisplayBackOfficeHeader()
    {

    }

    public function hookDisplayCustomerAccount()
    {

    }

    public function hookDisplayInvoiceLegalFreeText($params)
    {

    }

    public function hookDisplayMyAccountBlock()
    {

    }

    public function hookDisplayOrderConfirmation($params)
    {

    }

    public function hookHeader()
    {

    }

    public function hookPaymentOptions($params)
    {
        $payments_options = array();
        if (Configuration::get('BRAINTREE_ACTIVATE_PAYPAL')) {
            $embeddedOption = new PaymentOption();
            $action_text = $this->l('Pay with paypal');
            $embeddedOption->setCallToActionText($action_text);
            $embeddedOption->setModuleName('braintree');
            $embeddedOption->setForm($this->generateFormPaymentOption());
            $payments_options[] = $embeddedOption;
        }

        $embeddedOption = new PaymentOption();
        $embeddedOption->setCallToActionText($this->l('Pay with card'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/mini-cards.png'));
        $embeddedOption->setModuleName('braintree');
        $payments_options[] = $embeddedOption;

        return $payments_options;
    }

    public function generateFormPaymentOption()
    {
        /* @var $braintree MethodBraintree*/
        $braintree = AbstractMethodBraintree::load('Braintree');
        $clientToken = $braintree->init();

        if (isset($clientToken['error_code'])) {
            $this->context->smarty->assign(array(
                'init_error'=> $this->l('Error Braintree initialization ').$clientToken['error_code'].' : '.$clientToken['error_msg'],
            ));
        }

        $this->context->smarty->assign(array(
            'braintreeToken'=> $clientToken,
            'braintreeSubmitUrl'=> $this->context->link->getModuleLink($this->name, 'validation', array(), true),
            'braintreeAmount'=> $this->context->cart->getOrderTotal(),
            'baseDir' => $this->context->link->getBaseLink($this->context->shop->id, true),
            'path' => $this->_path,
            'mode' => $braintree->mode == 'SANDBOX' ? Tools::strtolower($braintree->mode) : 'production',
            'bt_method' => BT_PAYPAL_PAYMENT,
            'active_vaulting'=> Configuration::get('BRAINTREE_VAULTING'),
            'currency' => $this->context->currency->iso_code,
        ));

        if (Configuration::get('BRAINTREE_VAULTING')) {
            $payment_methods = BraintreeVaulting::getCustomerMethods($this->context->customer->id, BT_PAYPAL_PAYMENT);
            $this->context->smarty->assign(array(
                'payment_methods' => $payment_methods,
            ));
        }

        return $this->context->smarty->fetch('module:braintree/views/templates/front/payment.tpl');
    }

}
