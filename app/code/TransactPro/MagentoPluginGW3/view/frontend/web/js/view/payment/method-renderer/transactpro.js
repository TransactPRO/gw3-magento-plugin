define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Vault/js/view/payment/vault-enabler',
        'Magento_Checkout/js/model/quote',
        'mage/url'
    ],
    function ($,
              Component,
              placeOrderAction,
              fullScreenLoader,
              additionalValidators,
              validator,
              redirectOnSuccessAction,
              VaultEnabler,
              quote,
              urlBuilder) {
        'use strict';
        console.log('method renderer loaded');
        return Component.extend({
            defaults: {
                template: 'TransactPro_MagentoPluginGW3/payment/form',
                transactpro: null,
                transactproCardElement: null,
                transactproCard: null,
                token: null,
                creditCardHolder: ''
            },

            initialize: function () {
                this._super();
                this.transactpro = {};
                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());
                redirectOnSuccessAction.redirectUrl = urlBuilder.build('transactpro/index/redirect');
            },

            initTransactProElement: function () {
                //var self = this;
                /*
                 self.transactproCardElement = self.transactpro.elements();
                 self.transactproCard = self.transactproCardElement.create('card', {
                 hidePostalCode: true,
                 style: {
                 base: {
                 fontSize: '20px'
                 }
                 }
                 });
                 self.transactproCard.mount('#transactpro-card-element');
                 */
                console.log('initTransactProElement');
            },

            placeOrder: function (data, event) {
                console.log('placeOrder called');
                var self = this,
                    placeOrder;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    fullScreenLoader.startLoader();

                    $.when(this.createToken()).done(function () {
                        placeOrder = placeOrderAction(self.getData(), self.messageContainer);
                        $.when(placeOrder).done(function (backend_response) {
                            console.log('success order');
                            console.log(backend_response);
                            if (self.redirectAfterPlaceOrder) {
                                redirectOnSuccessAction.execute();
                            }
                        }).fail(function (backend_response) {
                            console.log('fail order');
                            console.log(backend_response);
                            fullScreenLoader.stopLoader();
                            self.isPlaceOrderActionAllowed(true);
                        });
                    }).fail(function (result) {
                        fullScreenLoader.stopLoader();
                        self.isPlaceOrderActionAllowed(true);

                        self.messageContainer.addErrorMessage({
                            'message': result
                        });
                    });

                    return true;
                }
                return false;
            },

            createToken: function () {
                console.log('createToken called');
                var self = this;

                var deffer = $.Deferred();
                self.token = {id: 1};
                deffer.resolve();

                /*
                 self.stripe.createToken(self.stripeCard, this.getAddressData()).then(function (response) {
                 if (response.error) {
                 deffer.reject(response.error.message);
                 } else {
                 self.token = response.token;
                 deffer.resolve();
                 }
                 });*/

                return deffer.promise();
            },

            getCode: function () {
                console.log('getCode called');
                return 'transactpro';
            },

            getTitle: function () {
                console.log('getTitle called');
                return 'TransactPro';
            },

            isActive: function () {
                console.log('isActive called');
                return true;
            },

            getData: function () {
                console.log('getData called');
                var data = this._super();

                data.additional_data.cc_holder = this.creditCardHolder;

                if (this.token) {
                    data.additional_data.cc_token = this.token.id;
                }

                this.vaultEnabler.visitAdditionalData(data);

                return data;
            },

            getPublishableKey: function () {
                console.log('getPublishableKey called');
                return window.checkoutConfig.payment[this.getCode()].publishableKey;
            },

            validate: function () {
                console.log('validate called');
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            isVaultEnabled: function () {
                console.log('isVaultEnabled called');
                return this.vaultEnabler.isVaultEnabled();
            },

            getVaultCode: function () {
                console.log('getVaultCode called');
                return window.checkoutConfig.payment[this.getCode()].vaultCode;
            },

            getPaymentMethod: function () {
                console.log('getPaymentMethod called');
                return window.checkoutConfig.payment[this.getCode()].payment_method;
            },

            showCcForm: function () {
                return window.checkoutConfig.payment[this.getCode()].show_cc_form;
            },

            isCardFormEnabled: function () {
                return this.showCcForm();
            },

            getAddressData: function () {
                console.log('getAddressData called');
                var billingAddress = quote.billingAddress();
                var transactproData = {
                    name: billingAddress.firstname + ' ' + billingAddress.lastname,
                    address_country: billingAddress.countryId,
                    address_line1: billingAddress.street[0]
                };

                /*
                 var stripeData = {
                 name: billingAddress.firstname + ' ' + billingAddress.lastname,
                 address_country: billingAddress.countryId,
                 address_line1: billingAddress.street[0]
                 };

                 if (billingAddress.street.length === 2) {
                 stripeData.address_line2 = billingAddress.street[1];
                 }

                 if (billingAddress.hasOwnProperty('postcode')) {
                 stripeData.address_zip = billingAddress.postcode;
                 }

                 if (billingAddress.hasOwnProperty('regionCode')) {
                 stripeData.address_state = billingAddress.regionCode;
                 }*/

                return transactproData;
            }
        });
    }
);