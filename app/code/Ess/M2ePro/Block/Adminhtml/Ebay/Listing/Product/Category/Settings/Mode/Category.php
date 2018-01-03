<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Product\Category\Settings\Mode;

class Category extends \Ess\M2ePro\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingCategory');
        $this->_controller = 'adminhtml_ebay_listing_product_category_settings_mode_category';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $this->_headerText = $this->__('Set eBay Categories (Based On Magento Categories)');
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/*/',array('step' => 1, '_current' => true));
        $this->addButton('back', array(
            'label'     => $this->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\''.$url.'\');'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->addButton('next', array(
            'id'        => 'ebay_listing_category_continue_btn',
            'label'     => $this->__('Continue'),
            'class'     => 'action-primary forward',
            'onclick'   => "EbayListingProductCategorySettingsModeCategoryGridObj.validate()"
        ));
        // ---------------------------------------
    }

    //########################################

    public function getGridHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::getGridHtml();
        }

        $listing = $this->getHelper('Data\GlobalData')->getValue('listing_for_products_category_settings');

        $viewHeaderBlock = $this->createBlock('Listing\View\Header','', [
            'data' => ['listing' => $listing]
        ]);

        return $viewHeaderBlock->toHtml() . parent::getGridHtml();
    }

    //########################################
}