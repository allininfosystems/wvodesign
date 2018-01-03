<?php
namespace Magento\Backend\Model\Menu\Config;

/**
 * Interceptor class for @see \Magento\Backend\Model\Menu\Config
 */
class Interceptor extends \Magento\Backend\Model\Menu\Config implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Model\Menu\Builder $menuBuilder, \Magento\Backend\Model\Menu\AbstractDirector $menuDirector, \Magento\Backend\Model\MenuFactory $menuFactory, \Magento\Backend\Model\Menu\Config\Reader $configReader, \Magento\Framework\App\Cache\Type\Config $configCacheType, \Magento\Framework\Event\ManagerInterface $eventManager, \Psr\Log\LoggerInterface $logger, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\App\State $appState)
    {
        $this->___init();
        parent::__construct($menuBuilder, $menuDirector, $menuFactory, $configReader, $configCacheType, $eventManager, $logger, $scopeConfig, $appState);
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getMenu');
        if (!$pluginInfo) {
            return parent::getMenu();
        } else {
            return $this->___callPlugins('getMenu', func_get_args(), $pluginInfo);
        }
    }
}
