<?php
/**
 * since 2007 PayPal
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
 *  @author since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
require_once _PS_MODULE_DIR_ . 'braintreeofficial/controllers/admin/AdminBraintreeofficialProcessLogger.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminBraintreeOfficialLogsController extends AdminBraintreeofficialProcessLoggerController
{
    public function init()
    {
        if (Tools::getValue('action') === 'set_sandbox_mode') {
            Configuration::updateValue('BRAINTREEOFFICIAL_SANDBOX', (int) Tools::getValue('sandbox_mode'));
        }

        $this->page_header_toolbar_title = $this->l('Logs');
        $this->filter = true;

        parent::init();
    }

    public function processFilter()
    {
        if (Tools::isSubmit('submitFilter' . $this->list_id)) {
            return parent::processFilter();
        }

        $isWriteCookie = false;

        foreach ($this->getDefaultFilters() as $key => $value) {
            if (Tools::isSubmit('submitFilter' . $this->list_id) === false) {
                $this->context->cookie->__set($key, $value);
                $isWriteCookie = true;
            }
        }

        if ($isWriteCookie) {
            $this->context->cookie->write();
        }

        $this->_filter = sprintf(' AND a.`sandbox` = %d ', (int) Configuration::get('BRAINTREEOFFICIAL_SANDBOX'));
    }

    public function initContent()
    {
        $this->content = $this->context->smarty
            ->assign('isNotShowSCAMessage', $this->isNotShowSCAMessage())
            ->fetch($this->getTemplatePath() . '_partials/headerLogo.tpl');
        $this->content .= parent::initContent();
        $this->content = $this->context->smarty
            ->assign('content', $this->content)
            ->assign('isModeSandbox', (int) Configuration::get('BRAINTREEOFFICIAL_SANDBOX'))
            ->fetch($this->getTemplatePath() . 'admin.tpl');
        $this->context->smarty->assign('content', $this->content);
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/bt_admin.css');
    }

    protected function getDefaultFilters()
    {
        return [
            $this->getCookieFilterPrefix() . $this->list_id . 'Filter_a!sandbox' => Configuration::get('BRAINTREEOFFICIAL_SANDBOX'),
        ];
    }

    public function initPageHeaderToolbar()
    {
        $query = [
            'token' => $this->token,
            'action' => 'set_sandbox_mode',
            'sandbox_mode' => Configuration::get('BRAINTREEOFFICIAL_SANDBOX') ? 0 : 1,
        ];
        $this->page_header_toolbar_btn['switch_sandbox'] = [
            'desc' => $this->l('Sandbox mode'),
            'icon' => 'process-icon-toggle-' . (Configuration::get('BRAINTREEOFFICIAL_SANDBOX') ? 'on' : 'off'),
            'help' => $this->l('Sandbox mode is the test environment where you\'ll be not able to collect any real payments.'),
            'href' => self::$currentIndex . '?' . http_build_query($query),
        ];

        parent::initPageHeaderToolbar();
        $this->context->smarty->clearAssign('help_link');
    }

    /**
     * @return bool
     */
    public function isNotShowSCAMessage()
    {
        return (bool) Configuration::get(BRAINTREEOFFICIAL_NOT_SHOW_SCA_MESSAGE);
    }

    public function displayAjaxDisableSCAmessage()
    {
        Configuration::updateValue(BRAINTREEOFFICIAL_NOT_SHOW_SCA_MESSAGE, 1);
    }
}
