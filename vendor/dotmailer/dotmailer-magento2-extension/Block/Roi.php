<?php

namespace Dotdigitalgroup\Email\Block;

class Roi extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    public $helper;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $session;

    /**
     * Roi constructor.
     *
     * @param \Dotdigitalgroup\Email\Helper\Data               $helper
     * @param \Magento\Checkout\Model\Session                  $session
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Dotdigitalgroup\Email\Helper\Data $helper,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->session = $session;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isRoiTrackingEnabled()
    {
        return $this->helper->isRoiTrackingEnabled();
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    private function getOrder()
    {
        return $this->session->getLastRealOrder();
    }

    /**
     * Get order total
     * @return string
     */
    public function getTotal()
    {
        return number_format($this->getOrder()->getBaseGrandTotal(), 2, '.', ',');
    }

    /**
     * Get product names
     * @return string
     */
    public function getProductNames()
    {
        $items = $this->getOrder()->getAllItems();
        $productNames = [];
        foreach ($items as $item) {
            if ($item->getParentItemId() === null) {
                $productNames[] = str_replace('"', ' ', $item->getName());
            }
        }
        return json_encode($productNames);
    }
}
