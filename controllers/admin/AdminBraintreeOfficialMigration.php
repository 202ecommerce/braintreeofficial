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
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @version   develop
 */

require_once _PS_MODULE_DIR_ . 'braintreeofficial/controllers/admin/AdminBraintreeOfficialSetup.php';
use BraintreeOfficialAddons\classes\AbstractMethodBraintreeOfficial;
use Symfony\Component\HttpFoundation\JsonResponse;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialCustomer;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialVaulting;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialOrder;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialCapture;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialLog;
use BraintreeofficialPPBTlib\Extensions\ProcessLogger\ProcessLoggerHandler;

class AdminBraintreeOfficialMigrationController extends AdminBraintreeOfficialSetupController
{
    public function initContent()
    {
        $this->content = $this->getStepOne();
        $this->context->smarty->assign('content', $this->content);
        Media::addJsDef(array(
            'controllerUrl' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite($this->controller_name)
        ));
        $this->addCSS('https://fonts.googleapis.com/icon?family=Material+Icons');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/migrationAdmin.js');
    }

    public function getStepOne()
    {
        return $this->context->smarty->fetch($this->getTemplatePath() . '_partials/migrationStepOne.tpl');
    }

    public function getStepTwo()
    {
        $tpl_vars = $this->getCredentialsTplVars();
        $this->context->smarty->assign($tpl_vars);
        return $this->context->smarty->fetch($this->getTemplatePath() . '_partials/migrationStepTwo.tpl');
    }

    public function getStepThree()
    {
        return $this->context->smarty->fetch($this->getTemplatePath() . '_partials/migrationStepThree.tpl');
    }

    protected function doMigration()
    {
        Configuration::updateValue('BRAINTREEOFFICIAL_MIGRATION_DONE', 1);
        $serviceBraintreeCustomer = new ServiceBraintreeOfficialCustomer();
        $serviceBraintreeVaulting = new ServiceBraintreeOfficialVaulting();
        $serviceBraintreeOrder = new ServiceBraintreeOfficialOrder();
        $serviceBraintreeCapture = new ServiceBraintreeOfficialCapture();
        $serviceBraintreeLog = new ServiceBraintreeOfficialLog();
        $tablesForBackup = array(
            'paypal_order',
            'paypal_customer',
            'paypal_capture',
            'paypal_processlogger',
            'paypal_vaulting'
        );
        $this->doBackupTables($tablesForBackup);

        $serviceBraintreeCustomer->doMigration();
        $serviceBraintreeVaulting->doMigration();
        $serviceBraintreeOrder->doMigration();
        $serviceBraintreeCapture->doMigration();
        $serviceBraintreeLog->doMigration();
        $serviceBraintreeOrder->deleteBtOrderFromPayPal();

        Configuration::updateValue('BRAINTREEOFFICIAL_MERCHANT_ID_SANDBOX', Configuration::get('PAYPAL_SANDBOX_BRAINTREE_MERCHANT_ID'));
        Configuration::updateValue('BRAINTREEOFFICIAL_MERCHANT_ID_LIVE', Configuration::get('PAYPAL_LIVE_BRAINTREE_MERCHANT_ID'));
        Configuration::updateValue('BRAINTREEOFFICIAL_API_INTENT', Configuration::get('PAYPAL_API_INTENT'));
        Configuration::updateValue('BRAINTREEOFFICIAL_SANDBOX', Configuration::get('PAYPAL_SANDBOX'));
        Configuration::updateValue('BRAINTREEOFFICIAL_ACTIVATE_PAYPAL', Configuration::get('PAYPAL_BY_BRAINTREE'));
        Configuration::updateValue('BRAINTREEOFFICIAL_SHOW_PAYPAL_BENEFITS', Configuration::get('PAYPAL_API_ADVANTAGES'));
        Configuration::updateValue('BRAINTREEOFFICIAL_VAULTING', Configuration::get('PAYPAL_VAULTING'));
        Configuration::updateValue('BRAINTREEOFFICIAL_3DSECURE', Configuration::get('PAYPAL_USE_3D_SECURE'));
        Configuration::updateValue('BRAINTREEOFFICIAL_3DSECURE_AMOUNT', Configuration::get('PAYPAL_3D_SECURE_AMOUNT'));

        $merchant_account_id_currency_sandbox = Tools::jsonDecode(Configuration::get('PAYPAL_SANDBOX_BRAINTREE_ACCOUNT_ID'));
        $merchant_account_id_currency_live = Tools::jsonDecode(Configuration::get('PAYPAL_LIVE_BRAINTREE_ACCOUNT_ID'));

        if ($merchant_account_id_currency_sandbox) {
            $this->doMigrateMerchantAccountIdCurrency((array)$merchant_account_id_currency_sandbox, 1);
        }

        if ($merchant_account_id_currency_live) {
            $this->doMigrateMerchantAccountIdCurrency((array)$merchant_account_id_currency_live, 0);
        }
    }

    /**
     *  @param array $tables the names of tables
     * */
    protected function doBackupTables($tables)
    {
        if (is_array($tables) == false || empty($tables)) {
            return;
        }
        ProcessLoggerHandler::openLogger();
        foreach ($tables as $table) {
            $nameTableCurrent = _DB_PREFIX_ . $table;
            $nameTableBackup = $nameTableCurrent . '_old';
            $queryCreatingTableBackup = "CREATE TABLE IF NOT EXISTS %s LIKE %s;";
            $queryFillingTableBackup = "REPLACE %s SELECT * FROM %s;";
            try {
                DB::getInstance()->execute(sprintf($queryCreatingTableBackup, pSQL($nameTableBackup), pSQL($nameTableCurrent)));
                DB::getInstance()->execute(sprintf($queryFillingTableBackup, pSQL($nameTableBackup), pSQL($nameTableCurrent)));
            } catch (Exception $e) {
                \Configuration::updateValue('BRAINTREEOFFICIAL_MIGRATION_FAILED', 1);
                $message = 'Error while do backup of the tables. ';
                $message .= 'File: ' . $e->getFile() . '. ';
                $message .= 'Line: ' . $e->getLine() . '. ';
                $message .= 'Message: ' . $e->getMessage() . '.';
                ProcessLoggerHandler::logError($message);
            }
        }
        ProcessLoggerHandler::closeLogger();
    }

    protected function doMigrateMerchantAccountIdCurrency($merchantAccountIdCurrency, $sandbox)
    {
        if (is_array($merchantAccountIdCurrency) == false) {
            return;
        }

        foreach ($merchantAccountIdCurrency as $currency => $merchantAccountId) {
            Configuration::updateValue($this->module->getNameMerchantAccountForCurrency($currency, $sandbox), $merchantAccountId);
        }
    }

    public function displayAjaxStartMigration()
    {
        $this->doMigration();
        $content = Tools::jsonEncode(array(
            'status' => true,
            'content' => $this->getStepTwo(),
        ));
        $response = new JsonResponse();
        $response->setContent($content);
        return $response->send();
    }

    public function displayAjaxSkipMigration()
    {
        $content = Tools::jsonEncode(array(
            'status' => Configuration::updateValue('BRAINTREEOFFICIAL_MIGRATION_SKIP', 1),
            'urlRedirect' => $this->context->link->getAdminLink('AdminBraintreeOfficialSetup', true),
        ));
        $paypalModule = Module::getInstanceByName('paypal');
        $paypalModule->disable();
        $response = new JsonResponse();
        $response->setContent($content);
        return $response->send();
    }

    public function displayAjaxSaveAccount()
    {
        /* @var $method MethodBraintreeOfficial*/
        $this->saveForm();
        $method = AbstractMethodBraintreeOfficial::load('BraintreeOfficial');
        $isConfigured = $method->isConfigured();

        if ($isConfigured) {
            $paypalModule = Module::getInstanceByName('paypal');
            $paypalModule->disable();
        }

        $content = Tools::jsonEncode(array(
            'status' => $isConfigured,
            'content' => $isConfigured == false ? $this->l('An error occurred while creating your web experience. Check your credentials.') : $this->getStepThree(),
        ));

        $response = new JsonResponse();
        $response->setContent($content);
        return $response->send();
    }
}
