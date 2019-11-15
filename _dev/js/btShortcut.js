/**
 * 2007-2019 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author 202-ecommerce <tech@202-ecommerce.com>
 * @copyright 202-ecommerce
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 */

const BtCheckout = {

    button: null,

    data: {
        amount: null,
        currency: null,
        mode: null,
        authorizationToken: null,
        controller: null,
        idProduct: null,
        idProductAttribute: null,
        quantity: null,
        page: null,
    },

    init() {
        let btnSc = $('[data-braintree-button]');

        if (btnSc.length == 0) {
            return;
        }

        this.button = btnSc;
        this.initData();
        this.addListeners();
        this.initPaymentBtn('checkout');
        this.checkAvaibility();
    },

    addListeners() {
        prestashop.on('updateCart', () => {
            BtCheckout.updateCartAmount();
        });

        prestashop.on('updatedProduct', (data) => {
            BtCheckout.updateProductAmount(data);
        });
    },

    updateProductAmount(data) {
        console.log(data);
        let quantity = $('input[name="qty"]').val();
        let idProductAttribute = data['id_product_attribute'];

        $.ajax({
            url: BtCheckout.data.controller,
            type: "POST",
            dataType: "JSON",
            data: {
                ajax: true,
                action: 'getProductAmount',
                quantity: quantity,
                idProductAttribute: idProductAttribute,
                idProduct: BtCheckout.data.idProduct
            },
            success (response) {
                if (("success" in response) && (response["success"] == true)) {
                    BtCheckout.data.amount = response["amount"];
                }

                if (BtCheckout.data.amount == 0) {
                    BtCheckout.button.hide();
                } else {
                    BtCheckout.button.show();
                }

                if (response["available"] == false) {
                    BtCheckout.button.hide();
                } else {
                    BtCheckout.button.show();
                }
            }
        });

    },

    updateCartAmount() {
        $.ajax({
            url: BtCheckout.data.controller,
            type: "POST",
            dataType: "JSON",
            data: {
                ajax: true,
                action: 'getCartAmount',
            },
            success (response) {
                if (("success" in response) && (response["success"] == true)) {
                    BtCheckout.data.amount = response["amount"];
                }

                if (BtCheckout.data.amount == 0) {
                    BtCheckout.button.hide();
                } else {
                    BtCheckout.button.show();
                }
            }
        });
    },

    initData() {
        if (typeof(paypal_braintree_authorization) != 'undefined') {
            this.data.authorizationToken = paypal_braintree_authorization;
        }

        if (typeof(paypal_braintree_currency) != 'undefined') {
            this.data.currency = paypal_braintree_currency;
        }

        if (typeof(paypal_braintree_mode) != 'undefined') {
            this.data.mode = paypal_braintree_mode;
        }

        if (typeof(paypal_braintree_amount) != 'undefined') {
            this.data.amount = paypal_braintree_amount;
        }

        if (typeof(paypal_braintree_contoller) != 'undefined') {
            this.data.controller = paypal_braintree_contoller;
        }

        if (typeof(paypal_braintree_id_product) != 'undefined') {
            this.data.idProduct = paypal_braintree_id_product;
        }

        if (typeof(paypal_braintree_id_product_attribute) != 'undefined') {
            this.data.idProductAttribute = paypal_braintree_id_product_attribute;
        }

        if (typeof(paypal_braintree_quantity) != 'undefined') {
            this.data.quantity = paypal_braintree_quantity;
        }

        if (typeof(paypal_braintree_page) != 'undefined') {
            this.data.page = paypal_braintree_page;
        }
    },

    showError(msgError) {
        alert(msgError);
    },

    sendData(data) {
        let form = document.createElement('form');
        let input = document.createElement('input');
        let inputPage = document.createElement('input');
        let inputProductId = document.createElement('input');
        let inputProductIdAttr = document.createElement('input');
        let inputProductQuantity = document.createElement('input');

        input.name = "paymentData";
        input.value = JSON.stringify(data);

        inputPage.name = "page";
        inputPage.value = BtCheckout.data.page;

        inputProductId.name = "idProduct";
        inputProductId.value = BtCheckout.data.idProduct;

        inputProductIdAttr.name = "idProductAttribute";
        inputProductIdAttr.value = BtCheckout.data.idProductAttribute;

        inputProductQuantity.name = "quantity";
        inputProductQuantity.value = BtCheckout.data.quantity;

        form.method = "POST";
        form.action = BtCheckout.data.controller;

        form.appendChild(input);
        form.appendChild(inputPage);
        form.appendChild(inputProductId);
        form.appendChild(inputProductIdAttr);
        form.appendChild(inputProductQuantity);

        document.body.appendChild(form);
        form.submit();
    },

    initPaymentBtn(flow) {
        if (BtCheckout.button == null) {
            return;
        }

        braintree.client.create({
            authorization: this.data.authorizationToken,
        }, (clientErr, clientInstance) => {
            // Stop if there was a problem creating the client.
            // This could happen if there is a network error or if the authorization
            // is invalid.
            if (clientErr) {
                console.error('Error creating client:', clientErr);
                return;
            }

            // Create a PayPal Checkout component.
            braintree.paypalCheckout.create({
                client: clientInstance,
            }, (paypalCheckoutErr, paypalCheckoutInstance) => {
                // Stop if there was a problem creating PayPal Checkout.
                // This could happen if there was a network error or if it's incorrectly
                // configured.
                if (paypalCheckoutErr) {
                    this.showError(paypalCheckoutErr);
                    return;
                }

                $('[data-braintree-button]').html('');
                paypal.Button.render({
                    env: BtCheckout.data.mode, // 'production' or 'sandbox'
                    style: {
                        tagline: false
                    },

                    payment() {
                        return paypalCheckoutInstance.createPayment({
                            flow,
                            amount: BtCheckout.data.amount,
                            currency: BtCheckout.data.currency,
                            billingAgreementDescription: '',
                            enableShippingAddress: true,
                            shippingAddressEditable: false,
                        });
                    },

                    onAuthorize(data, actions) {
                        return paypalCheckoutInstance.tokenizePayment(data)
                            .then((payload) => {
                                // Submit `payload.nonce` to your server.
                                BtCheckout.sendData(payload);

                            });
                    },
                    onError(err) {
                        BtCheckout.showError(err);
                    },
                }, '[data-braintree-button]').then((e) => {

                });
            });
        });
    },

    checkAvaibility() {
        $.ajax({
            url: BtCheckout.data.controller,
            type: "POST",
            dataType: "JSON",
            data: {
                ajax: true,
                action: 'checkProductAvaibility',
                idProduct: BtCheckout.data.idProduct,
                idProductAttribute: BtCheckout.data.idProductAttribute,
                quantity: BtCheckout.data.quantity
            },
            success (response) {
                if (("success" in response) && (response["success"] == true)) {
                    if (response['available']) {
                        BtCheckout.button.show();
                    } else {
                        BtCheckout.button.hide();
                    }
                }
            }
        });
    }
};

$(document).ready(() => {
    BtCheckout.init();
});
