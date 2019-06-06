{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div>
    <p>
        {l s='Braintree Account' mod='braintree'}.
    </p>

    <p>
        {l s='In order to activate the module, you must connect your existing Braintree account or create a new one.' mod='braintree'}
    </p>

    <p>
        <span class="btn btn-default" data-toggle="modal" data-target="#credentialBlock">
            {l s='Connect or create Braintree account' mod='braintree'}
        </span>
    </p>
</div>



<div class="modal fade" id="credentialBlock" role="dialog" aria-labelledby="credentialBlock" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div>
                    <div>
                        <p class="h3">{l s='API Credentials' mod='braintree'}</p>
                        <p>
                            {l s='In order to accept Braintreee payments, please fill your API Tokenization Keys.' mod='braintree'}
                        </p>
                        <p>
                            {l s='If you do not have a Braintreee account, you can create by following this' mod='braintree'}
                            <a href="">{l s='link' mod='braintree'}</a>
                        </p>
                        <p>
                            {l s='To find your Tokenization Keys, please follow those steps:' mod='braintree'}
                        </p>
                        <ul>
                            <li>
                                <a href="https://www.braintreegateway.com/login" target="_blank">{l s='Log into your account' mod='braintree'}</a>
                            </li>
                            <li>
                                {l s='Click the working wheel (logo)' mod='braintree'}
                            </li>
                            <li>
                                {l s='Click on API' mod='braintree'}
                            </li>
                            <li>
                                {l s='Click the "Generate New Tokization Key"' mod='braintree'}
                            </li>
                        </ul>
                        <hr>

                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Public key' mod='braintree'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text"
                                       name="braintree_public_key_live"
                                       {if isset($braintree_public_key_live)}value='{$braintree_public_key_live nofilter}'{/if}>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Private key' mod='braintree'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text"
                                       name="braintree_private_key_live"
                                       {if isset($braintree_private_key_live)}value='{$braintree_private_key_live nofilter}'{/if}>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Merchant ID' mod='braintree'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text"
                                       name="braintree_merchant_id_live"
                                       {if isset($braintree_merchant_id_live)}value='{$braintree_merchant_id_live nofilter}'{/if}>
                            </div>
                        </div>


                        <hr>

                        <p>
                            {l s='To retrieve sandbox Tokenization Keys please repeat the steps by connecting to' mod='braintree'}
                            <a href="https://sandbox.braintreegateway.com/login" target="_blank">{l s='sandbox account' mod='braintree'}</a>
                        </p>


                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Public key' mod='braintree'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text"
                                       name="braintree_public_key_sandbox"
                                       {if isset($braintree_public_key_sandbox)}value='{$braintree_public_key_sandbox nofilter}'{/if}>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Private key' mod='braintree'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text"
                                       name="braintree_private_key_sandbox"
                                       {if isset($braintree_private_key_sandbox)}value='{$braintree_private_key_sandbox nofilter}'{/if}>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-lg-3">
                                {l s='Merchant ID' mod='braintree'}
                            </label>
                            <div class="col-lg-9">
                                <input type="text"
                                       name="braintree_merchant_id_sandbox"
                                       {if isset($braintree_merchant_id_sandbox)}value='{$braintree_merchant_id_sandbox nofilter}'{/if}>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{l s='Cancel' mod='braintree'}</button>
                <button type="button" id="confirmCredentials" class="btn btn-primary">{l s='Confirm API Credentials' mod='braintree'}</button>
            </div>
        </div>
    </div>
</div>

