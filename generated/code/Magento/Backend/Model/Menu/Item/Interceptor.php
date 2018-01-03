<?php
namespace Magento\Backend\Model\Menu\Item;

/**
 * Interceptor class for @see \Magento\Backend\Model\Menu\Item
 */
class Interceptor extends \Magento\Backend\Model\Menu\Item implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Model\Menu\Item\Validator $validator, \Magento\Framework\AuthorizationInterface $authorization, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Backend\Model\MenuFactory $menuFactory, \Magento\Backend\Model\UrlInterface $urlModel, \Magento\Framework\Module\ModuleListInterface $moduleList, \Magento\Framework\Module\Manager $moduleManager, array $data = array())
    {
        $this->___init();
        parent::__construct($validator, $authorization, $scopeConfig, $menuFactory, $urlModel, $moduleList, $moduleManager, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getId');
        if (!$pluginInfo) {
            return parent::getId();
        } else {
            return $this->___callPlugins('getId', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getTarget');
        if (!$pluginInfo) {
            return parent::getTarget();
        } else {
            return $this->___callPlugins('getTarget', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'hasChildren');
        if (!$pluginInfo) {
            return parent::hasChildren();
        } else {
            return $this->___callPlugins('hasChildren', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getChildren');
        if (!$pluginInfo) {
            return parent::getChildren();
        } else {
            return $this->___callPlugins('getChildren', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getUrl');
        if (!$pluginInfo) {
            return parent::getUrl();
        } else {
            return $this->___callPlugins('getUrl', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getAction');
        if (!$pluginInfo) {
            return parent::getAction();
        } else {
            return $this->___callPlugins('getAction', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setAction($action)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setAction');
        if (!$pluginInfo) {
            return parent::setAction($action);
        } else {
            return $this->___callPlugins('setAction', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasClickCallback()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'hasClickCallback');
        if (!$pluginInfo) {
            return parent::hasClickCallback();
        } else {
            return $this->___callPlugins('hasClickCallback', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getClickCallback()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getClickCallback');
        if (!$pluginInfo) {
            return parent::getClickCallback();
        } else {
            return $this->___callPlugins('getClickCallback', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getTitle');
        if (!$pluginInfo) {
            return parent::getTitle();
        } else {
            return $this->___callPlugins('getTitle', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setTitle');
        if (!$pluginInfo) {
            return parent::setTitle($title);
        } else {
            return $this->___callPlugins('setTitle', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasTooltip()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'hasTooltip');
        if (!$pluginInfo) {
            return parent::hasTooltip();
        } else {
            return $this->___callPlugins('hasTooltip', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getTooltip');
        if (!$pluginInfo) {
            return parent::getTooltip();
        } else {
            return $this->___callPlugins('getTooltip', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setTooltip($tooltip)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setTooltip');
        if (!$pluginInfo) {
            return parent::setTooltip($tooltip);
        } else {
            return $this->___callPlugins('setTooltip', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setModule($module)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setModule');
        if (!$pluginInfo) {
            return parent::setModule($module);
        } else {
            return $this->___callPlugins('setModule', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setModuleDependency($moduleName)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setModuleDependency');
        if (!$pluginInfo) {
            return parent::setModuleDependency($moduleName);
        } else {
            return $this->___callPlugins('setModuleDependency', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigDependency($configPath)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setConfigDependency');
        if (!$pluginInfo) {
            return parent::setConfigDependency($configPath);
        } else {
            return $this->___callPlugins('setConfigDependency', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'isDisabled');
        if (!$pluginInfo) {
            return parent::isDisabled();
        } else {
            return $this->___callPlugins('isDisabled', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'isAllowed');
        if (!$pluginInfo) {
            return parent::isAllowed();
        } else {
            return $this->___callPlugins('isAllowed', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'toArray');
        if (!$pluginInfo) {
            return parent::toArray();
        } else {
            return $this->___callPlugins('toArray', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function populateFromArray(array $data)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'populateFromArray');
        if (!$pluginInfo) {
            return parent::populateFromArray($data);
        } else {
            return $this->___callPlugins('populateFromArray', func_get_args(), $pluginInfo);
        }
    }
}
