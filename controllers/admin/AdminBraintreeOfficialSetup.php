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

require_once(_PS_MODULE_DIR_ . 'braintreeofficial/vendor/autoload.php');

use BraintreeOfficialAddons\classes\AdminBraintreeOfficialController;
use BraintreeOfficialAddons\classes\AbstractMethodBraintreeOfficial;
use Symfony\Component\HttpFoundation\JsonResponse;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialOrder;

class AdminBraintreeOfficialSetupController extends AdminBraintreeOfficialController
{
    protected $serviceBraintreeOfficialOrder;

    public function __construct()
    {
        parent::__construct();
        $this->serviceBraintreeOfficialOrder = new ServiceBraintreeOfficialOrder();
    }

    public function initContent()
    {
        if ($this->offreMigration() && Configuration::get('BRAINTREEOFFICIAL_MIGRATION_SKIP') != '1') {
            return Tools::redirectAdmin($this->context->link->getAdminLink('AdminBraintreeOfficialMigration', true));
        }

        if ($this->showWarningCurrency()) {
            $this->warnings[] = $this->context->smarty->fetch($this->getTemplatePath() . '_partials/warningCurrency.tpl');
        }

        $this->initAccountSettingsBlock();
        $formAccountSettings = $this->renderForm();
        $this->clearFieldsForm();

        $this->initPaymentSettingsBlock();
        $formPaymentSettings = $this->renderForm();
        $this->clearFieldsForm();

        $this->initMerchantAccountForm();
        $formMerchantAccounts = $this->renderForm();
        $this->clearFieldsForm();

        $this->initEnvironmentSettings();
        $formEnvironmentSettings = $this->renderForm();
        $this->clearFieldsForm();

        $this->initStatusBlock();
        $formStatus = $this->renderForm();
        $this->clearFieldsForm();

        $tpl_vars = array(
            'formAccountSettings' => $formAccountSettings,
            'formPaymentSettings' => $formPaymentSettings,
            'formMerchantAccounts' => $formMerchantAccounts,
            'formEnvironmentSettings' => $formEnvironmentSettings,
            'formStatus' => $formStatus,
            'showMessageAboutStateName' => (int)Configuration::get('BRAINTREEOFFICIAL_SHOW_MESSAGE_ABOUT_STATE_NAME')

        );
        $this->context->smarty->assign($tpl_vars);
        $this->content = $this->context->smarty->fetch($this->getTemplatePath() . 'setup.tpl');
        $this->context->smarty->assign('content', $this->content);
        Media::addJsDef(array(
            'controllerUrl' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite($this->controller_name)
        ));
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/setupAdmin.js');
    }

    public function offreMigration()
    {
        $offerMigration = Configuration::get('BRAINTREEOFFICIAL_MIGRATION_DONE') != '1';
        $offerMigration &= Module::isInstalled('paypal');
        $offerMigration &= Configuration::get('PAYPAL_BRAINTREE_ENABLED') == '1';
        $offerMigration &= Configuration::get('PAYPAL_METHOD') == 'BT';
        $offerMigration &= $this->serviceBraintreeOfficialOrder->getCountOrders() == 0;
        return $offerMigration;
    }

    public function initAccountSettingsBlock()
    {
        $tpl_vars = $this->getCredentialsTplVars();
        $this->context->smarty->assign($tpl_vars);
        $html_content = $this->context->smarty->fetch($this->getTemplatePath() . '_partials/accountSettingsBlock.tpl');

        $this->fields_form['form']['form'] = array(
            'legend' => array(
                'title' => $this->l('Account settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'SaveAccountSettingsBlock'
                ),
                array(
                    'type' => 'html',
                    'html_content' => $html_content,
                    'name' => '',
                    'col' => 12,
                    'label' => '',
                )
            ),
            'id_form' => 'bt_config_account'
        );

        $this->tpl_form_vars = array_merge($this->tpl_form_vars, array('SaveAccountSettingsBlock' => 1));
    }

    public function getCredentialsTplVars()
    {
        /* @var $methodBraintree MethodBraintreeOfficial*/
        $methodBraintree = AbstractMethodBraintreeOfficial::load('BraintreeOfficial');

        $tpl_vars = array(
            'braintreeofficial_public_key_live' => Configuration::get('BRAINTREEOFFICIAL_PUBLIC_KEY_LIVE'),
            'braintreeofficial_public_key_sandbox' => Configuration::get('BRAINTREEOFFICIAL_PUBLIC_KEY_SANDBOX'),
            'braintreeofficial_private_key_live' => Configuration::get('BRAINTREEOFFICIAL_PRIVATE_KEY_LIVE'),
            'braintreeofficial_private_key_sandbox' => Configuration::get('BRAINTREEOFFICIAL_PRIVATE_KEY_SANDBOX'),
            'braintreeofficial_merchant_id_live' => Configuration::get('BRAINTREEOFFICIAL_MERCHANT_ID_LIVE'),
            'braintreeofficial_merchant_id_sandbox' => Configuration::get('BRAINTREEOFFICIAL_MERCHANT_ID_SANDBOX'),
            'accountConfigured' => $methodBraintree->isConfigured(),
            'sandboxEnvironment' => (int)Configuration::get('BRAINTREEOFFICIAL_SANDBOX'),
            'showMigrationBtn' => $this->offreMigration()
        );
        return $tpl_vars;
    }

    public function initPaymentSettingsBlock()
    {
        $this->fields_form['form']['form'] = array(
            'legend' => array(
                'title' => $this->l('Payment settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Payment action'),
                    'name' => 'braintreeofficial_api_intent',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'sale',
                                'name' => $this->l('Sale')
                            ),
                            array(
                                'id' => 'authorization',
                                'name' => $this->l('Authorize')
                            )
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'html',
                    'name' => '',
                    'html_content' => $this->module->displayInformation($this->l('We recommend Authorize process only for lean manufacturers and craft products sellers.'))
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right button',
            ),
            'id_form' => 'bt_config_payment'
        );

        $values = array(
            'braintreeofficial_api_intent' => Configuration::get('BRAINTREEOFFICIAL_API_INTENT'),
            'braintreeofficial_sandbox' => (int)Configuration::get('BRAINTREEOFFICIAL_SANDBOX')
        );
        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $values);
    }

    public function initEnvironmentSettings()
    {
        $this->context->smarty->assign('sandbox', (int)\Configuration::get('BRAINTREEOFFICIAL_SANDBOX'));
        $html_content = $this->context->smarty->fetch($this->getTemplatePath() . '_partials/switchSandboxBlock.tpl');
        $this->fields_form['form']['form'] = array(
            'legend' => array(
                'title' => $this->l('Environment Settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'html',
                    'html_content' => $html_content,
                    'name' => '',
                    'col' => 12,
                    'label' => '',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'braintreeofficial_sandbox',
                    'col' => 12,
                    'label' => '',
                )
            ),
            'id_form' => 'bt_config_environment'
        );
        $values = array(
            'braintreeofficial_sandbox' => !(int)Configuration::get('BRAINTREEOFFICIAL_SANDBOX')
        );
        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $values);
    }

    public function initStatusBlock()
    {
        /* @var $methodBraintree MethodBraintreeOfficial*/
        $countryDefault = new \Country((int)\Configuration::get('PS_COUNTRY_DEFAULT'), $this->context->language->id);
        $methodBraintree = AbstractMethodBraintreeOfficial::load('BraintreeOfficial');

        $tpl_vars = array(
            'merchantCountry' => $countryDefault->name,
            'tlsVersion' => $this->_checkTLSVersion(),
            'accountConfigured' => $methodBraintree->isConfigured(),
            'sslActivated' => $this->module->isSslActive(),
            'merchantAccountIdConfigured' => $this->showWarningCurrency() == false,
            'paymentCustomerCurrency' => $this->module->getCurrentModePaymentCurrency() == BRAINTREE_PAYMENT_CUSTOMER_CURRENCY
        );
        $this->context->smarty->assign($tpl_vars);
        $html_content = $this->context->smarty->fetch($this->getTemplatePath() . '_partials/statusBlock.tpl');
        $this->fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('Status'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'html',
                    'html_content' => $html_content,
                    'name' => '',
                    'col' => 12,
                    'label' => '',
                )
            )
        );
    }

    public function displayAjaxCheckCredentials()
    {
        $this->initStatusBlock();
        $response = new JsonResponse($this->renderForm());
        return $response->send();
    }

    public function initMerchantAccountForm()
    {
        $inputs = array(
            array(
                'type' => 'hidden',
                'name' => 'SaveMerchantAccountForm'
            )
        );

        foreach (Currency::getCurrencies() as $currency) {
            $inputs[] = array(
                'type' => 'text',
                'label' => $this->l('Merchant account Id for ') . $currency['iso_code'],
                'name' => Tools::strtolower($this->module->getNameMerchantAccountForCurrency($currency['iso_code']))
            );
        }

        $this->fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('Braintree Merchant Accounts'),
                'icon' => 'icon-cogs',
            ),
            'description' => $this->context->smarty->fetch($this->getTemplatePath() . '_partials/infoForMerchantAccount.tpl'),
            'input' => $inputs,
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right button',
            ),
        );

        $values =  array();
        foreach ($inputs as $input) {
            $values[$input['name']] = Configuration::get(Tools::strtoupper($input['name']));
        }
        $values['SaveMerchantAccountForm'] = 1;
        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $values);
    }

    public function saveForm()
    {
        $methodBraintree = AbstractMethodBraintreeOfficial::load('BraintreeOfficial');

        if (Tools::isSubmit("SaveMerchantAccountForm")) {
            $merchantAccounts = array();

            foreach (Currency::getCurrencies() as $currency) {
                $nameMerchantAccont = Tools::strtolower($this->module->getNameMerchantAccountForCurrency($currency['iso_code']));
                $merchantAccounts[$currency['iso_code']] = Tools::getValue($nameMerchantAccont);
            }

            $wrongMerchantAccounts = $methodBraintree->validateMerchantAccounts($merchantAccounts);

            if (empty($wrongMerchantAccounts) == false) {
                $this->errors[] = $this->l('Invalid Merchant account ID. Please verify your merchant account id for ') . implode(", ", array_keys($wrongMerchantAccounts));
                return false;
            }
        }

        $result = parent::saveForm();

        if (Tools::isSubmit('SaveAccountSettingsBlock')) {
            $this->importMerchantAccountForCurrency(true);
            $this->importMerchantAccountForCurrency(false);
        }

        if ($methodBraintree->isConfigured() == false) {
            if (Tools::isSubmit('braintreeofficial_sandbox') == false) {
                $this->errors[] = $this->l('An error occurred while creating your web experience. Check your credentials.');
            }

        } else {
            if (Module::isEnabled('paypal') && (int)Configuration::get('PAYPAL_BRAINTREE_ENABLED')) {
                $paypalModule = Module::getInstanceByName('paypal');
                $paypalModule->disable();
            }
        }

        return $result;
    }

    /**
     * @param bool $mode true if mode Sandbox and false if mode Live
     */
    public function importMerchantAccountForCurrency($mode = null)
    {
        /* @var $method MethodBraintreeOfficial*/
        $method = AbstractMethodBraintreeOfficial::load('BraintreeOfficial');

        if ($mode === null) {
            $mode = (int)Configuration::get('BRAINTREEOFFICIAL_SANDBOX');
        }

        // Delete merchant accounts if they exists
        $this->module->deleteMerchantAccountIds($mode);

        $allCurrency = $method->getAllCurrency($mode);

        if (empty($allCurrency)) {
            return;
        }

        foreach ($allCurrency as $currency => $merchantAccountForCurrency) {
            Configuration::updateValue($this->module->getNameMerchantAccountForCurrency($currency, $mode), $merchantAccountForCurrency);
        }
    }

    public function showWarningCurrency()
    {
        /* @var $methodBraintree MethodBraintreeOfficial*/
        $methodBraintree = AbstractMethodBraintreeOfficial::load('BraintreeOfficial');

        return $this->module->getCurrentModePaymentCurrency() == BRAINTREE_PAYMENT_CUSTOMER_CURRENCY &&
            $this->module->merchantAccountForCurrencyConfigured() == false &&
            $methodBraintree->isConfigured();
    }
}
