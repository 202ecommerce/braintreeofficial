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

use BraintreeOfficialAddons\classes\BraintreeOfficialVaulting;
use BraintreeOfficialAddons\classes\BraintreeOfficialCustomer;
use BraintreeOfficialAddons\classes\AbstractMethodBraintreeOfficial;
use BraintreeOfficialAddons\services\ServiceBraintreeOfficialVaulting;

class BraintreeOfficialAccountModuleFrontController extends ModuleFrontController
{
    /* @var ServiceBraintreeOfficialVaulting*/
    protected $serviceBraintreeOfficialVaulting;

    public function __construct()
    {
        $this->auth = true;
        parent::__construct();
        $this->context = Context::getContext();
        $this->serviceBraintreeOfficialVaulting = new ServiceBraintreeOfficialVaulting();
    }

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        /* @var $method MethodBraintreeOfficial*/
        if (Tools::getValue('process') == 'delete') {
            $id = (int)Tools::getValue('id_method');
            $payment_method = new BraintreeOfficialVaulting($id);
            $method = AbstractMethodBraintreeOfficial::load('BraintreeOfficial');
            $method->deleteVaultedMethod($payment_method);
            if ($payment_method->delete()) {
                $this->success[] = $this->l('Successfully deleted!');
            }
        }
        if (Tools::getValue('process') == 'save') {
            $all_values = Tools::getAllValues();
            foreach ($all_values as $key => $value) {
                $val_arr = explode('_', $key);
                if ($val_arr[0] == 'name') {
                    $payment_method = new BraintreeOfficialVaulting($val_arr[1]);
                    $payment_method->name = $value;
                    if ($payment_method->save()) {
                        $this->success[] = $this->l('Successfully updated!');
                    }
                }
            }
        }
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $methods = $this->serviceBraintreeOfficialVaulting->getCustomerGroupedMethods($this->context->customer->id);
        $this->context->smarty->assign(array(
            'payment_methods' => $methods,
        ));
        $this->setTemplate('module:braintreeofficial/views/templates/front/payment_methods.tpl');
    }


    /**
     * Set my account breadcrumb links.
     */
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();

        return $breadcrumb;
    }

    /**
     * Adds page-customer-account body class.
     */
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['body_classes']['page-customer-account'] = true;

        return $page;
    }
}
