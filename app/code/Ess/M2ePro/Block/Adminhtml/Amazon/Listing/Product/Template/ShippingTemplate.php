<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Listing\Product\Template;

class ShippingTemplate extends \Ess\M2ePro\Block\Adminhtml\Amazon\Listing\Product\Template
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('amazon/listing/product/template/shipping_template/main.phtml');
    }

    //########################################
}