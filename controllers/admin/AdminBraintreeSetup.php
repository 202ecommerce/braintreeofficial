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

use BraintreeAddons\classes\AdminBraintreeController;
use BraintreeAddons\classes\AbstractMethodBraintree;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminBraintreeSetupController extends AdminBraintreeController
{
    public function initContent()
    {
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

    public function initAccountSettingsBlock()
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
            'sandboxEnvironment' => (int)Configuration::get('BRAINTREE_SANDBOX')
        );
        $this->context->smarty->assign($tpl_vars);
        $html_content = $this->context->smarty->fetch($this->getTemplatePath() . '_partials/accountSettingsBlock.tpl');

        $this->fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('Account settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'html',
                    'html_content' => $html_content,
                    'name' => '',
                )
            )
        );
    }

    public function initPaymentSettingsBlock()
    {
        $this->fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('Payment settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Payment action'),
                    'name' => 'braintree_api_intent',
                    'desc' => $this->l('We recommend Authoreze process only for lean manufacturers and craft products sellers.'),
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
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right button',
            ),
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
        $this->fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('Environment Settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'html',
                    'html_content' => $html_content,
                    'name' => '',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'braintree_sandbox',
                )
            )
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
            'accountConfigured' => $methodBraintree->isConfigured()
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
        $inputs = array();
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
            'input' => $inputs,
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right button',
            ),
        );

        foreach ($inputs as $input) {
            $values[$input['name']] = Configuration::get(Tools::strtoupper($input['name']));
        }
        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $values);
    }

    public function saveForm()
    {
        parent::saveForm();
        $this->module->checkBraintreeStats();
    }
}
