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
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminBraintreeOfficialHelpController extends AdminBraintreeOfficialController
{
    public function init()
    {
        parent::init();

        if (Tools::isSubmit('registerHooks')) {
            if ($this->registerHooks()) {
                $this->confirmations[] = $this->l('Hooks successfully registered');
            }
        }
    }

    /**
     * @throws Exception
     * @throws SmartyException
     */
    public function initContent()
    {
        $need_rounding = (Configuration::get('PS_ROUND_TYPE') != Order::ROUND_ITEM) || (Configuration::get('PS_PRICE_ROUND_MODE') != PS_ROUND_HALF_DOWN);
        $tpl_vars = array(
            'need_rounding' => $need_rounding,
        );
        $this->context->smarty->assign($tpl_vars);
        $this->content = $this->context->smarty->fetch($this->getTemplatePath() . 'help.tpl');
        $this->context->smarty->assign('content', $this->content);
        Media::addJsDef(array(
            'controllerUrl' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite($this->controller_name)
        ));
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/helpAdmin.js');
    }

    public function displayAjaxCheckCredentials()
    {
        $response = new JsonResponse($this->_checkRequirements());
        return $response->send();
    }

    public function registerHooks()
    {
        $result = true;
        $hooksUnregistered = $this->module->getHooksUnregistered();

        if (empty($hooksUnregistered)) {
            return $result;
        }

        foreach ($hooksUnregistered as $hookName) {
            $result &= $this->module->registerHook($hookName);
        }

        return $result;
    }
}
