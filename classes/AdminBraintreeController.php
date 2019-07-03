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

namespace BraintreeAddons\classes;

use Symfony\Component\VarDumper\VarDumper;

class AdminBraintreeController extends \ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
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
            $this->saveForm();
        }
        parent::postProcess();
    }

    public function saveForm()
    {
        foreach (\Tools::getAllValues() as $fieldName => $fieldValue) {
            if (strpos($fieldName, 'braintree') === 0) {
                \Configuration::updateValue(\Tools::strtoupper($fieldName), pSQL($fieldValue), false, null, $this->context->shop->id);
            }
        }
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
        if ((int)\Configuration::get('PS_COUNTRY_DEFAULT') == false) {
            $response['success'] = false;
            $response['message'][] = $this->l('To activate a payment solution, please select your default country.');
        }
        $tls_check = $this->_checkTLSVersion();
        if ($tls_check['status'] == false) {
            $response['success'] = false;
            $response['message'][] = $this->l('Tls verification failed.').' '.$tls_check['error_message'];
        }
        if ($response['success']) {
            $response['message'][] = $this->l('Your shop configuration is OK. You can start to configure the Braintree module.');
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
                    $return['error_message'] = $this->l('401 Unauthorized. Please note that the TLS verification can not be done if you have an htaccess password protection enabled on your web site.');
                } else {
                    $return['error_message'] = curl_error($curl);
                }
            } else {
                $return['status'] = true;
            }
        } else {
            $return['status'] = false;
            if (version_compare(curl_version()['version'], '7.34.0', '<')) {
                $return['error_message'] = $this->l(' You are using an old version of cURL. Please update your cURL extension to version 7.34.0 or higher.');
            } else {
                $return['error_message'] = $this->l('TLS version is not compatible');
            }
        }
        return $return;
    }
}
