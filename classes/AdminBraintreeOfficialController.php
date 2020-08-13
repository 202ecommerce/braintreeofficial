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

namespace BraintreeOfficialAddons\classes;

use Symfony\Component\VarDumper\VarDumper;

class AdminBraintreeOfficialController extends \ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function init()
    {
        parent::init();

        if ((int)\Configuration::get('BRAINTREEOFFICIAL_MIGRATION_FAILED') == 1) {
            $message = $this->module->l('The migration of your settings from PayPal module has been completed with errors.', 'AdminBraintreeOfficialController');
            $message .= $this->l('Please contact our');
            $message .= " <a href='https://addons.prestashop.com/fr/contactez-nous?id_product=1748' target='_blank'>";
            $message .= $this->module->l('support team.', 'AdminBraintreeOfficialController') . "</a>";
            $this->warnings[] = $message;
        }

        $this->context->smarty->assign('moduleDir', _MODULE_DIR_);
    }

    public function renderForm($fields_form = null)
    {
        if ($fields_form === null) {
            $fields_form = $this->fields_form;
        }

        $helper = new \HelperForm();
        $helper->token = \Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = \AdminController::$currentIndex;
        $helper->submit_action = $this->controller_name . '_config';
        $default_lang = (int)\Configuration::get('PS_LANG_DEFAULT');
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->tpl_vars = array(
            'fields_value' => $this->tpl_form_vars,
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm($fields_form);
    }

    public function postProcess()
    {
        if (\Tools::isSubmit($this->controller_name . '_config')) {
            $result = $this->saveForm();
            if ($result) {
                $this->confirmations[] = $this->module->l('Successful update.', 'AdminBraintreeOfficialController');
            }
        }

        if ((int)\Configuration::get('BRAINTREEOFFICIAL_SANDBOX') == 1) {
            $message = $this->module->l('Your Braintree account is currently configured to accept payments on the Sandbox', 'AdminBraintreeOfficialController');
            $message .= ' (<b>' . $this->module->l('test environment', 'AdminBraintreeOfficialController') . '</b>). ';
            $message .= $this->module->l('Any transaction will be fictitious. Disable the option, to accept actual payments (production environment) and log in with your Braintree credentials', 'AdminBraintreeOfficialController');
            $this->warnings[] = $message;
        }

        parent::postProcess();
    }

    public function saveForm()
    {
        $result = true;

        foreach (\Tools::getAllValues() as $fieldName => $fieldValue) {
            if (strpos($fieldName, 'braintreeofficial_') === 0) {
                $result &= \Configuration::updateValue(\Tools::strtoupper($fieldName), pSQL($fieldValue));
            }
        }

        return $result;
    }

    public function clearFieldsForm()
    {
        $this->fields_form = array();
        $this->tpl_form_vars = array();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/bt_admin.css');
    }

    protected function _checkRequirements()
    {
        $response = array(
            'success' => true,
            'message' => array()
        );
        $hooksUnregistered = $this->module->getHooksUnregistered();

        if (empty($hooksUnregistered) == false) {
            $response['success'] = false;
            $response['message'][] = $this->getHooksUnregisteredMessage($hooksUnregistered);
        }

        if ((int)\Configuration::get('PS_COUNTRY_DEFAULT') == false) {
            $response['success'] = false;
            $response['message'][] = $this->module->l('To activate a payment solution, please select your default country.', 'AdminBraintreeOfficialController');
        }

        if ($this->module->isSslActive() == false) {
            $response['success'] = false;
            $response['message'][] = $this->module->l('SSL should be enabled on your web site.', 'AdminBraintreeOfficialController');
        }

        $tls_check = $this->_checkTLSVersion();
        if ($tls_check['status'] == false) {
            $response['success'] = false;
            $response['message'][] = $this->module->l('Tls verification failed.', 'AdminBraintreeOfficialController').' '.$tls_check['error_message'];
        }
        if ($response['success']) {
            $response['message'][] = $this->module->l('Your shop configuration is OK. You can start to configure the Braintree module.', 'AdminBraintreeOfficialController');
        }
        return $response;
    }

    /**
     * Check TLS version 1.2 compability : CURL request to server
     */
    protected function _checkTLSVersion()
    {
        $return = array(
            'status' => false,
            'error_message' => ''
        );
        if (defined('CURL_SSLVERSION_TLSv1_2')) {
            $tls_server = $this->context->link->getModuleLink($this->module->name, 'tlscurltestserver');
            $curl = curl_init($tls_server);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            $response = curl_exec($curl);
            if ($response != 'ok') {
                $return['status'] = false;
                $curl_info = curl_getinfo($curl);
                if ($curl_info['http_code'] == 401) {
                    $return['error_message'] = $this->module->l('401 Unauthorized. Please note that the TLS verification can not be done if you have an htaccess password protection enabled on your web site.', 'AdminBraintreeOfficialController');
                } else {
                    $return['error_message'] = curl_error($curl);
                }
            } else {
                $return['status'] = true;
            }
        } else {
            $return['status'] = false;
            if (version_compare(curl_version()['version'], '7.34.0', '<')) {
                $return['error_message'] = $this->module->l(' You are using an old version of cURL. Please update your cURL extension to version 7.34.0 or higher.', 'AdminBraintreeOfficialController');
            } else {
                $return['error_message'] = $this->module->l('TLS version is not compatible', 'AdminBraintreeOfficialController');
            }
        }
        return $return;
    }

    /**
     *  @param array $hooks array of the hooks name
     *  @return string
     */
    public function getHooksUnregisteredMessage($hooks)
    {
        if (is_array($hooks) == false || empty($hooks)) {
            return '';
        }

        $this->context->smarty->assign('hooks', $hooks);
        return $this->context->smarty->fetch($this->getTemplatePath() . '_partials/unregisteredHooksMessage.tpl');
    }
}
