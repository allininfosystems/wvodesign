<?php
namespace Magento\Quote\Model\Quote\Address\Total\Shipping;

/**
 * Interceptor class for @see \Magento\Quote\Model\Quote\Address\Total\Shipping
 */
class Interceptor extends \Magento\Quote\Model\Quote\Address\Total\Shipping implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency, \Magento\Quote\Model\Quote\Address\FreeShippingInterface $freeShipping)
    {
        $this->___init();
        parent::__construct($priceCurrency, $freeShipping);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'collect');
        if (!$pluginInfo) {
            return parent::collect($quote, $shippingAssignment, $total);
        } else {
            return $this->___callPlugins('collect', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'fetch');
        if (!$pluginInfo) {
            return parent::fetch($quote, $total);
        } else {
            return $this->___callPlugins('fetch', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getLabel');
        if (!$pluginInfo) {
            return parent::getLabel();
        } else {
            return $this->___callPlugins('getLabel', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setCode');
        if (!$pluginInfo) {
            return parent::setCode($code);
        } else {
            return $this->___callPlugins('setCode', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCode');
        if (!$pluginInfo) {
            return parent::getCode();
        } else {
            return $this->___callPlugins('getCode', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function _setTotal(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, '_setTotal');
        if (!$pluginInfo) {
            return parent::_setTotal($total);
        } else {
            return $this->___callPlugins('_setTotal', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getItemRowTotal(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getItemRowTotal');
        if (!$pluginInfo) {
            return parent::getItemRowTotal($item);
        } else {
            return $this->___callPlugins('getItemRowTotal', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getItemBaseRowTotal(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getItemBaseRowTotal');
        if (!$pluginInfo) {
            return parent::getItemBaseRowTotal($item);
        } else {
            return $this->___callPlugins('getItemBaseRowTotal', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIsItemRowTotalCompoundable(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getIsItemRowTotalCompoundable');
        if (!$pluginInfo) {
            return parent::getIsItemRowTotalCompoundable($item);
        } else {
            return $this->___callPlugins('getIsItemRowTotalCompoundable', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processConfigArray($config, $store)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'processConfigArray');
        if (!$pluginInfo) {
            return parent::processConfigArray($config, $store);
        } else {
            return $this->___callPlugins('processConfigArray', func_get_args(), $pluginInfo);
        }
    }
}
