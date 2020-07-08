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

class AdminBraintreeOfficialCustomizeCheckoutController extends AdminBraintreeOfficialController
{
    public $parameters = array();

    public function __construct()
    {
        parent::__construct();

        $this->parameters = array(
            'braintreeofficial_activate_paypal',
            'braintreeofficial_vaulting',
            'braintreeofficial_3DSecure',
            'braintreeofficial_3DSecure_amount',
            'braintreeofficial_show_paypal_benefits',
            'braintreeofficial_express_checkout_in_context',
            'braintreeofficial_express_checkout_shortcut',
            'braintreeofficial_express_checkout_shortcut_cart',
        );

        $this->advancedFormParameters = array(
            'braintreeofficial_customize_order_status',
            'braintreeofficial_os_refunded',
            'braintreeofficial_os_canceled',
            'braintreeofficial_os_accepted',
            'braintreeofficial_os_capture_canceled',
            'braintreeofficial_os_accepted_two',
            'braintreeofficial_os_processing',
            'braintreeofficial_os_pending'
        );
    }

    public function initContent()
    {
        $this->initBehaviorForm();
        $this->context->smarty->assign('form', $this->renderForm());
        $this->clearFieldsForm();
        $this->initAdvancedForm();
        $this->context->smarty->assign('advancedForm', $this->renderForm());
        $this->content = $this->context->smarty->fetch($this->getTemplatePath() . 'customizeCheckout.tpl');
        $this->context->smarty->assign('content', $this->content);
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/customizeCheckoutAdmin.js');
    }

    public function initBehaviorForm()
    {
        $tplVars = array(
            'braintreeofficial_express_checkout_shortcut' => Configuration::get('BRAINTREEOFFICIAL_EXPRESS_CHECKOUT_SHORTCUT'),
            'braintreeofficial_express_checkout_shortcut_cart' => Configuration::get('BRAINTREEOFFICIAL_EXPRESS_CHECKOUT_SHORTCUT_CART'),
        );
        $this->context->smarty->assign($tplVars);
        $htmlContent = $this->context->smarty->fetch($this->getTemplatePath() . '_partials/blockPreviewButtonContext.tpl');

        $this->fields_form['form']['form'] = array(
            'legend' => array(
                'title' => $this->l('Behavior'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Accept PayPal Payments'),
                    'name' => 'braintreeofficial_activate_paypal',
                    'desc' => $this->l(''),
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'braintreeofficial_activate_paypal_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'braintreeofficial_activate_paypal_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),
                array(
                    'type' => 'html',
                    'label' => $this->l('PayPal Express Checkout Shortcut on'),
                    'hint' => $this->l('The PayPal Shortcut is displayed directly on your cart or on your product pages, allowing a faster checkout for your buyers. PayPal provides you with the client\'s shipping and billing information so that you don\'t have to collect it yourself.'),
                    'name' => '',
                    'html_content' => $htmlContent
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show PayPal benefits to your customers'),
                    'name' => 'braintreeofficial_show_paypal_benefits',
                    'hint' => $this->l('You can increase your conversion rate by presenting PayPal benefits to your customers on payment methods selection page.'),
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'braintreeofficial_show_paypal_benefits_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'braintreeofficial_show_paypal_benefits_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable Vault'),
                    'name' => 'braintreeofficial_vaulting',
                    'is_bool' => true,
                    'hint' => $this->l('The Vault is used to process payments so your customers don\'t need to re-enter their information each time they make a purchase from you.'),
                    'values' => array(
                        array(
                            'id' => 'braintreeofficial_vaulting_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'braintreeofficial_vaulting_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Activate 3D Secure for Braintree'),
                    'name' => 'braintreeofficial_3DSecure',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'braintreeofficial_3DSecure_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'braintreeofficial_3DSecure_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Amount for 3DS in ') . Currency::getCurrency(Configuration::get('PS_CURRENCY_DEFAULT'))['iso_code'],
                    'name' => 'braintreeofficial_3DSecure_amount',
                    'hint' => $this->l('Activate 3D Secure only for orders which total is bigger that this amount in your context currency'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right button',
                'name' => 'behaviorForm'
            ),
            'id_form' => 'bt_config_behavior'
        );

        $values =array();

        foreach ($this->parameters as $parameter) {
            $values[$parameter] = Configuration::get(Tools::strtoupper($parameter));
        }

        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $values);
    }

    public function initAdvancedForm()
    {
        $orderStatuses = $this->module->getOrderStatuses();
        $inputs = array();

        $inputs[] = array(
            'type' => 'html',
            'name' => '',
            'html_content' => $this->module->displayInformation($this->l('You can customize your orders\' status for each possible action in the Braintree module.'), false)
        );

        $inputs[] = array(
            'type' => 'switch',
            'label' => $this->l('Customize your order status'),
            'name' => 'braintreeofficial_customize_order_status',
            'hint' => $this->l('Please use this option only if you want to change the assigned default Braintree status on PrestaShop Order statuses.'),
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'braintreeofficial_customize_order_status_on',
                    'value' => 1,
                    'label' => $this->l('Enabled'),
                ),
                array(
                    'id' => 'braintreeofficial_customize_order_status_off',
                    'value' => 0,
                    'label' => $this->l('Disabled'),
                )
            ),
        );

        $inputs[] = array(
            'type' => 'html',
            'name' => '',
            'html_content' => $this->context->smarty->fetch($this->getTemplatePath() . '_partials/formAdvancedHelpOne.tpl')
        );

        $inputs[] = array(
            'type' => 'select',
            'label' => $this->l('Order Status for triggering the refund on Braintree'),
            'name' => 'braintreeofficial_os_refunded',
            'hint' => $this->l('You can refund the orders paid via Braintree directly via your PrestaShop BackOffice. Here you can choose the order status that triggers the refund on Braintree. Choose the option "no actions" if you would like to change the order status without triggering the automatic refund on Braintree.'),
            'desc' => $this->l('Default status : Refunded'),
            'options' => array(
                'query' => $orderStatuses,
                'id' => 'id',
                'name' => 'name'
            )
        );

        if (Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == 'sale') {
            $inputs[] = array(
                'type' => 'select',
                'label' => $this->l('Order Status for triggering the cancellation on Braintree'),
                'name' => 'braintreeofficial_os_canceled',
                'hint' => $this->l('You can cancel orders paid via Braintree directly via your PrestaShop BackOffice. Here you can choose the order status that triggers the Braintree voiding of an authorized transaction on Braintree. Choose the option "no actions" if you would like to change the order status without triggering the automatic cancellation on Braintree.'),
                'desc' => $this->l('Default status : Canceled'),
                'options' => array(
                    'query' => $orderStatuses,
                    'id' => 'id',
                    'name' => 'name'
                )
            );
        }

        if (Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == 'authorization') {
            $inputs[] = array(
                'type' => 'select',
                'label' => $this->l('Payment accepted via BO (call Braintree to get the payment)'),
                'name' => 'braintreeofficial_os_accepted',
                'hint' => $this->l('You are currently using the Authorize mode. It means that you separate the payment authorization from the capture of the authorized payment. For capturing the authorized payement you have to change the order status to "payment accepted" (or to a custom status with the same meaning). Here you can choose a custom order status for accepting the order and validating transaction in Authorize mode.'),
                'desc' => $this->l('Default status : Payment accepted'),
                'options' => array(
                    'query' => $orderStatuses,
                    'id' => 'id',
                    'name' => 'name'
                )
            );

            $inputs[] = array(
                'type' => 'select',
                'label' => $this->l('Payment canceled via BO (call Braintree to cancel the capture)'),
                'name' => 'braintreeofficial_os_capture_canceled',
                'hint' => $this->l('You are currently using the Authorize mode. It means that you separate the payment authorization from the capture of the authorized payment. For canceling the authorized payment you have to change the order status to "canceled" (or to a custom status with the same meaning). Here you can choose an order status for canceling the order and voiding the transaction in Authorize mode.'),
                'desc' => $this->l('Default status : Canceled'),
                'options' => array(
                    'query' => $orderStatuses,
                    'id' => 'id',
                    'name' => 'name'
                )
            );
        }

        $inputs[] = array(
            'type' => 'html',
            'name' => '',
            'html_content' => $this->context->smarty->fetch($this->getTemplatePath() . '_partials/formAdvancedHelpTwo.tpl')
        );

        if (Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == 'sale') {
            $inputs[] = array(
                'type' => 'select',
                'label' => $this->l('Payment accepted and transaction completed'),
                'name' => 'braintreeofficial_os_accepted_two',
                'hint' => $this->l('You are currently using the Authorize mode. It means that you separate the payment authorization from the capture of the authorized payment. For capturing the authorized payement you have to change the order status to "payment accepted" (or to a custom status with the same meaning). Here you can choose a custom order status for accepting the order and validating transaction in Authorize mode.'),
                'desc' => $this->l('Default status : Payment accepted'),
                'options' => array(
                    'query' => $orderStatuses,
                    'id' => 'id',
                    'name' => 'name'
                )
            );

            $inputs[] = array(
                'type' => 'select',
                'label' => $this->l('Payment processing (payments by PayPal)'),
                'name' => 'braintreeofficial_os_processing',
                'hint' => $this->l('The transaction paid by card can be in the pending status. If the payment is processing the order will be created in the temporary status.'),
                'desc' => $this->l('Default status : Payment processing (authorized)'),
                'options' => array(
                    'query' => $orderStatuses,
                    'id' => 'id',
                    'name' => 'name'
                )
            );
        }

        if (Configuration::get('BRAINTREEOFFICIAL_API_INTENT') == 'authorization') {
            $inputs[] = array(
                'type' => 'select',
                'label' => $this->l('Payment pending (authorized)'),
                'name' => 'braintreeofficial_os_pending',
                'hint' => $this->l('You are currently using the Authorize mode. It means that you separate the payment authorization from the capture of the authorized payment. By default the orders will be created in the "Payment pending (authorized)" status but you can customize it if needed.'),
                'desc' => $this->l('Default status : Payment pending (authorized)'),
                'options' => array(
                    'query' => $orderStatuses,
                    'id' => 'id',
                    'name' => 'name'
                )
            );
        }

        $this->fields_form['form']['form'] = array(
            'legend' => array(
                'title' => $this->l('Advanced mode'),
                'icon' => 'icon-cogs',
            ),
            'input' => $inputs,
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right button',
                'name' => 'saveAdvancedForm'
            ),
        );



        $values =array();

        foreach ($this->advancedFormParameters as $parameter) {
            $values[$parameter] = Configuration::get(Tools::strtoupper($parameter));
        }

        $this->tpl_form_vars = array_merge($this->tpl_form_vars, $values);
    }

    public function saveForm()
    {
        $return = parent::saveForm();

        if (Tools::isSubmit('behaviorForm')) {
            foreach ($this->parameters as $parameter) {
                $return &= Configuration::updateValue(Tools::strtoupper($parameter), Tools::getValue($parameter));
            }
        }

        if (Tools::isSubmit('saveAdvancedForm')) {
            foreach ($this->advancedFormParameters as $parameter) {
                if (Tools::isSubmit($parameter)) {
                    $return &= Configuration::updateValue(Tools::strtoupper($parameter), Tools::getValue($parameter));
                }
            }
        }

        return $return;
    }
}
