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
            if (strpos($fieldName,'braintree') === 0 ) {
                \Configuration::updateValue(\Tools::strtoupper($fieldName), pSQL($fieldValue), false, null, $this->context->shop->id);
            }
        }
    }

    public function clearFieldsForm()
    {
        $this->fields_form = array();
        $this->tpl_form_vars = array();
    }
}