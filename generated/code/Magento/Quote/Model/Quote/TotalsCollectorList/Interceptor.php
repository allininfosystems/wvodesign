<?php
namespace Magento\Quote\Model\Quote\TotalsCollectorList;

/**
 * Interceptor class for @see \Magento\Quote\Model\Quote\TotalsCollectorList
 */
class Interceptor extends \Magento\Quote\Model\Quote\TotalsCollectorList implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Quote\Model\Quote\Address\Total\Collector $totalCollector, \Magento\Quote\Model\Quote\Address\Total\CollectorFactory $totalCollectorFactory, \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Quote\Model\Quote\Address\TotalFactory $totalFactory)
    {
        $this->___init();
        parent::__construct($totalCollector, $totalCollectorFactory, $eventManager, $storeManager, $totalFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollectors($storeId)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCollectors');
        if (!$pluginInfo) {
            return parent::getCollectors($storeId);
        } else {
            return $this->___callPlugins('getCollectors', func_get_args(), $pluginInfo);
        }
    }
}
