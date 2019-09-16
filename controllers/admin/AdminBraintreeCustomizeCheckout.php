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

class AdminBraintreeCustomizeCheckoutController extends AdminBraintreeController
{
    public function initContent()
    {
        $this->initBehaviorForm();
        $this->context->smarty->assign('form', $this->renderForm());
        $this->content = $this->context->smarty->fetch($this->getTemplatePath() . 'customizeCheckout.tpl');
        $this->context->smarty->assign('content', $this->content);
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/customizeCheckoutAdmin.js');
    }

    public function initBehaviorForm()
    {
        $this->fields_form['form']['form'] = array(
            'legend' => array(
                'title' => $this->l('Behavior'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Accept PayPal Payments'),
                    'name' => 'braintree_activate_paypal',
                    'desc' => $this->l(''),
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'braintree_activate_paypal_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'braintree_activate_paypal_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show PayPal benefits to your customers'),
                    'name' => 'braintree_show_paypal_benefits',
                    'hint' => $this->l('You can increase your conversion rate by presenting PayPal benefits to your customers on payment methods selection page.'),
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'braintree_show_paypal_benefits_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'braintree_show_paypal_benefits_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable Vault'),
                    'name' => 'braintree_vaulting',
                    'is_bool' => true,
                    'hint' => $this->l('The Vault is used to process payments so your customers don\'t need to re-enter their information each time they make a purchase from you.'),
                    'values' => array(
                        array(
                            'id' => 'braintree_vaulting_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'braintree_vaulting_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Activate 3D Secure for Braintree'),
                    'name' => 'braintree_3DSecure',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'braintree_3DSecure_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'braintree_3DSecure_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Amount for 3DS in ') . Currency::getCurrency(Configuration::get('PS_CURRENCY_DEFAULT'))['iso_code'],
                    'name' => 'braintree_3DSecure_amount',
                    'hint' => $this->l('Activate 3D Secure only for orders which total is bigger that this amount in your context currency'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right button',
            ),
            'id_form' => 'bt_config_behavior'
        );

        $values = array(
            'braintree_activate_paypal' => (int)Configuration::get('BRAINTREE_ACTIVATE_PAYPAL'),
            'braintree_vaulting' => (int)Configuration::get('BRAINTREE_VAULTING'),
            'braintree_3DSecure' => (int)Configuration::get('BRAINTREE_3DSECURE'),
            'braintree_3DSecure_amount' => (float)Configuration::get('BRAINTREE_3DSECURE_AMOUNT'),
            'braintree_show_paypal_benefits' => (int)Configuration::get('BRAINTREE_SHOW_PAYPAL_BENEFITS')
        );
        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $values);
    }
}
