<?php
namespace Magento\Config\Model\Config\Structure\Data;

/**
 * Interceptor class for @see \Magento\Config\Model\Config\Structure\Data
 */
class Interceptor extends \Magento\Config\Model\Config\Structure\Data implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Config\Model\Config\Structure\Reader $reader, \Magento\Framework\Config\ScopeInterface $configScope, \Magento\Framework\Config\CacheInterface $cache, $cacheId, \Magento\Framework\Serialize\SerializerInterface $serializer = null)
    {
        $this->___init();
        parent::__construct($reader, $configScope, $cache, $cacheId, $serializer);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(array $config)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'merge');
        if (!$pluginInfo) {
            return parent::merge($config);
        } else {
            return $this->___callPlugins('merge', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($path = null, $default = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'get');
        if (!$pluginInfo) {
            return parent::get($path, $default);
        } else {
            return $this->___callPlugins('get', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'reset');
        if (!$pluginInfo) {
            return parent::reset();
        } else {
            return $this->___callPlugins('reset', func_get_args(), $pluginInfo);
        }
    }
}
