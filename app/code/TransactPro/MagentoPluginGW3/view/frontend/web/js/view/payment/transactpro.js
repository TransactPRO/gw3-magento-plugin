define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'transactpro',
                component: 'TransactPro_MagentoPluginGW3/js/view/payment/method-renderer/transactpro'
            }
        );
        console.log('payment/transactpro.js loaded');
        /** Add view logic here if needed */
        return Component.extend({});
    }
);