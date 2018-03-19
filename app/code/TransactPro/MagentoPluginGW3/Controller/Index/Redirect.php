<?php

namespace TransactPro\MagentoPluginGW3\Controller\Index;

class Redirect extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Order success action (before redirection to MPI url)
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$this->_objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $this->_eventManager->dispatch(
            'transactpro_checkout_onepage_controller_success_action',
            ['order_ids' => [$session->getLastOrderId()]]
        );
    }
}