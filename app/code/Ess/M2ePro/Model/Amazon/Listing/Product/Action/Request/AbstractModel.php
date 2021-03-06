<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Action\Request;

abstract class AbstractModel extends \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Request
{
    /**
     * @var array
     */
    protected $validatorsData = array();

    //########################################

    public function setValidatorsData(array $data)
    {
        $this->validatorsData = $data;
    }

    //########################################

    protected function searchNotFoundAttributes()
    {
        $this->getMagentoProduct()->clearNotFoundAttributes();
    }

    protected function processNotFoundAttributes($title)
    {
        $attributes = $this->getMagentoProduct()->getNotFoundAttributes();

        if (empty($attributes)) {
            return true;
        }

        $this->addNotFoundAttributesMessages($title, $attributes);

        return false;
    }

    // ---------------------------------------

    protected function addNotFoundAttributesMessages($title, array $attributes)
    {
        $attributesTitles = array();

        foreach ($attributes as $attribute) {
            $attributesTitles[] = $this->getHelper('Magento\Attribute')
                ->getAttributeLabel($attribute,
                    $this->getListing()->getStoreId());
        }
        // M2ePro\TRANSLATIONS
        // %attribute_title%: Attribute(s) %attributes% were not found in this Product and its value was not sent.
        $this->addWarningMessage(
            $this->getHelper('Module\Translation')->__(
                '%attribute_title%: Attribute(s) %attributes% were not found'.
                ' in this Product and its value was not sent.',
                $this->getHelper('Module\Translation')->__($title), implode(',',$attributesTitles)
            )
        );
    }

    //########################################
}