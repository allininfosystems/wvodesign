<?php
namespace Ess\M2ePro\Model\Magento\Framework\Http;

/**
 * Factory class for @see \Ess\M2ePro\Model\Magento\Framework\Http\NotCacheableResponse
 */
class NotCacheableResponseFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Ess\\M2ePro\\Model\\Magento\\Framework\\Http\\NotCacheableResponse')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Ess\M2ePro\Model\Magento\Framework\Http\NotCacheableResponse
     */
    public function create(array $data = array())
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
