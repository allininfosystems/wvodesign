<?php
/**
 * Copyright © 2017 Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\CustomShippingPrice\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Get carrier settings
     *
     * @param $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->scopeConfig->getValue('carriers/custom_shipping/'.$key);
    }

    /**
     * Get module settings
     *
     * @param $key
     * @return mixed
     */
    public function getConfigModule($key)
    {
        return $this->scopeConfig
            ->getValue('mageside_customshippingprice/general/' . $key);
    }
}
