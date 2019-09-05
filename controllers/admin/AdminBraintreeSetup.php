<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommerce
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommerce is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommerce
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommerce est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 * @version   develop
 */

require_once(_PS_MODULE_DIR_ . 'braintree/vendor/autoload.php');

use BraintreeAddons\classes\AdminBraintreeController;
use BraintreeAddons\classes\AbstractMethodBraintree;
use Symfony\Component\HttpFoundation\JsonResponse;
use BraintreeAddons\services\ServiceBraintreeOrder;

class AdminBraintreeSetupController extends AdminBraintreeController
{
    protected $serviceBraintreeOrder;

    public function __construct()
    {
        parent::__construct();
        $this->serviceBraintreeOrder = new ServiceBraintreeOrder();
    }

    public function initContent()
    {
        if ($this->offreMigration() && Configuration::get('BRAINTREE_MIGRATION_SKIP') != '1') {
            return Tools::redirectAdmin($this->context->link->getAdminLink('AdminBraintreeMigration', true));
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
            'formStatus' => $formStatus

        );
        $this->context->smarty->assign($tpl_vars);
        $this->content = $this->context->smarty->fetch($this->getTemplatePath() . 'setup.tpl');
        $this->context->smarty->assign('content', $this->content);
        Media::addJsDef(array(
            'controllerUrl' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite($this->controller_name)
        ));
        $this->addJS('modules/' . $this->module->name . '/views/js/setupAdmin.js');
    }

    public function offreMigration()
    {
        $offerMigration = Configuration::get('BRAINTREE_MIGRATION_DONE') != '1';
        $offerMigration &= Module::isInstalled('paypal');
        $offerMigration &= Configuration::get('PAYPAL_BRAINTREE_ENABLED') == '1';
        $offerMigration &= Configuration::get('PAYPAL_METHOD') == 'BT';
        $offerMigration &= $this->serviceBraintreeOrder->getCountOrders() == 0;
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
        /* @var $methodBraintree MethodBraintree*/
        $methodBraintree = AbstractMethodBraintree::load('Braintree');

        $tpl_vars = array(
            'braintree_public_key_live' => Configuration::get('BRAINTREE_PUBLIC_KEY_LIVE'),
            'braintree_public_key_sandbox' => Configuration::get('BRAINTREE_PUBLIC_KEY_SANDBOX'),
            'braintree_private_key_live' => Configuration::get('BRAINTREE_PRIVATE_KEY_LIVE'),
            'braintree_private_key_sandbox' => Configuration::get('BRAINTREE_PRIVATE_KEY_SANDBOX'),
            'braintree_merchant_id_live' => Configuration::get('BRAINTREE_MERCHANT_ID_LIVE'),
            'braintree_merchant_id_sandbox' => Configuration::get('BRAINTREE_MERCHANT_ID_SANDBOX'),
            'accountConfigured' => $methodBraintree->isConfigured(),
            'sandboxEnvironment' => (int)Configuration::get('BRAINTREE_SANDBOX'),
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
                    'name' => 'braintree_api_intent',
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
            'braintree_api_intent' => Configuration::get('BRAINTREE_API_INTENT'),
            'braintree_sandbox' => (int)Configuration::get('BRAINTREE_SANDBOX')
        );
        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $values);
    }

    public function initEnvironmentSettings()
    {
        $this->context->smarty->assign('sandbox', (int)\Configuration::get('BRAINTREE_SANDBOX'));
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
                    'name' => 'braintree_sandbox',
                    'col' => 12,
                    'label' => '',
                )
            ),
            'id_form' => 'bt_config_environment'
        );
        $values = array(
            'braintree_sandbox' => !(int)Configuration::get('BRAINTREE_SANDBOX')
        );
        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $values);
    }

    public function initStatusBlock()
    {
        /* @var $methodBraintree MethodBraintree*/
        $countryDefault = new \Country((int)\Configuration::get('PS_COUNTRY_DEFAULT'), $this->context->language->id);
        $methodBraintree = AbstractMethodBraintree::load('Braintree');

        $tpl_vars = array(
            'merchantCountry' => $countryDefault->name,
            'tlsVersion' => $this->_checkTLSVersion(),
            'accountConfigured' => $methodBraintree->isConfigured(),
            'sslActivated' => $this->module->isSslActive(),
            'merchantAccountIdConfigured' => $this->showWarningCurrency() == false
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
        $methodBraintree = AbstractMethodBraintree::load('Braintree');

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

        $methodBraintree = AbstractMethodBraintree::load('Braintree');

        if ($methodBraintree->isConfigured() == false) {
            $this->errors[] = $this->l('An error occurred while creating your web experience. Check your credentials.');
        }

        return $result;
    }

    /**
     * @param bool $mode true if mode Sandbox and false if mode Live
     */
    public function importMerchantAccountForCurrency($mode = null)
    {
        /* @var $method MethodBraintree*/
        $method = AbstractMethodBraintree::load('Braintree');
        if ($mode === null) {
            $mode = (int)Configuration::get('BRAINTREE_SANDBOX');
        }
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
        return $this->module->getCurrentModePaymentCurrency() == BRAINTREE_PAYMENT_CUSTOMER_CURRENCY &&
            $this->module->merchantAccountForCurrencyConfigured() == false;
    }
}
