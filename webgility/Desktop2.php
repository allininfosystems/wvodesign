<?php
/*
 © Copyright 2007-2016 © Webgility INC. all rights reserved.
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility INC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 File last updated: 19/07/2011
*/
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
ini_set("display_errors","Off");
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//error_reporting(E_ALL);

class Webgility_Shop1_Model_Runmobile extends WgCommon
{
      //global $objectManager;
      public function __construct() {
          global $objectManager;                                                              
          $magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
          $objectManager = $magentoObjectManagerFactory->create($_SERVER);
          \Magento\Framework\App\ObjectManager::setInstance($objectManager);
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $objectManager->objectManager = $objectManager;
		  #$objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');
		 $objectManager->get('Magento\Framework\App\State')->setAreaCode('global');
		 # $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
      }
  
        protected   $types = array('AE'=>'American Express', 'VI'=>'Visa', 'MC'=>'MasterCard', 'DI'=>'Discover','OT'=>'Other',''=>'');
	protected   $carriers = array('dhl'=>'DHL',
                                  'fedex'=>'FedEx',
                                  'ups'=>'UPS',
                                  'usps'=>'USPS',
                                  'freeshipping'=>"Free Shipping" ,
                                  'flatrate'=>"Flat Rate",
                                  'tablerate'=>"Best Way");
    protected   $carriers_ =array('DHL'=>'dhl',
                                  'FEDEX'=>'fedex',
                                  'UPS'=>'ups',
                                  'USPS'=>'usps',
								  'FEDERAL EXPRESS'=>'fedex',
                                  'UNITED PARCEL SERVICE'=>'ups',
                                  'UNITED STATES POSTAL SERVICE'=>'usps',
                                  "FREE SHIPPING" =>'freeshipping',
                                  'FLAT RATE'=>"flatrate",
                                  "BEST WAY" =>'tablerate');
								  
        public  function CheckUser($username,$password)
        { 
            global $objectManager;

            $WgBaseResponse = new WgBaseResponse();		
            try
            {
                // return true;
                //$user = Mage::getSingleton('admin/user');
                $user = $objectManager->get('Magento\User\Model\User');

                //$userRole= Mage::getSingleton('admin/mysql4_acl_role');
                 $temp=$user->authenticate($username, $password);

                if($temp)
                {
                    //$loadRole=$userRole->load($user->getRoles($user));
                    return 0;
                }
                else
                {
                    $WgBaseResponse->setStatusCode('2');
                    $WgBaseResponse->setStatusMessage('Invalid login. Authorization failed');
                    return $this->response($WgBaseResponse->getBaseResponse());		   
                }
            }catch (Exception $e)
            {
                $WgBaseResponse->setStatusCode('1');
                $WgBaseResponse->setStatusMessage('Invalid login. Authorization failed');
                return $this->response($WgBaseResponse->getBaseResponse());		   
            }
        }

	function getVersion()
	{ 
            global $objectManager;
            $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
            if($productMetadata->getVersion()!="")
            {
                return $productMetadata->getVersion();
            }else{
                return "0";
            }
	}

	function checkAccessInfo($username,$password,$others)
	{
            $responseArray = array();		
            #Check for authorization
            $status = $this->CheckUser($username,$password);
            if($status!="0")
            {		
                return $status;
            }
            #Supported Versions
            $Cartsupportversion = 

            $version = $this->getVersion();

            $WgBaseResponse = new WgBaseResponse();				
            $WgBaseResponse->setStatusCode('0');
            $code = "0";
            $message = "Successfully connected to your online store.";
            $responseArray['StatusCode'] = $code;
            if($version!==0)
            {
                if((version_compare($version, '2.0.0',"<") > 0) || (version_compare($version, "2.2.0",">") > 0))
                {
                    $WgBaseResponse->setStatusMessage($message ." However, your store version is " . $version ." which hasn't been fully tested with eCC. If you'd still like to continue, click OK to continue or contact Webgility to confirm compatibility.");
                }else{
                    $WgBaseResponse->setStatusMessage($message);
                }
            }else{
                $WgBaseResponse->setStatusMessage($message." However, eCC is unable to detect your store version. If you'd still like to continue, click OK to continue or contact Webgility to confirm compatibility.");
            }		
            return $this->response($WgBaseResponse->getBaseResponse());		   
	}

	function getCompanyInfo($username,$password,$storeid=1,$others)
	{
            global $objectManager;
            $storeId=$this->getDefaultStore($storeid);
            $CompanyInfo = new CompanyInfo();		
            $status =  $this->CheckUser($username,$password);
            if($status!="0")
            {		
                return $status;
            }
            
            $config = $this->_getStoreDetails();

            $CompanyInfo->setStatusCode('0');
            $CompanyInfo->setStatusMessage('All Ok');

            $CompanyInfo->setStoreID('1');
            
            $CompanyInfo->setStoreName(@$config['storeName']);
            $CompanyInfo->setAddress(htmlspecialchars($config['storeAddress1'], ENT_NOQUOTES));
            $CompanyInfo->setcity($config['storeCity']);
            $CompanyInfo->setState($config['storeRegion']);
            $CompanyInfo->setCountry($config['storeCountry']);
            $CompanyInfo->setZipcode($config['storePostCode']);
            $CompanyInfo->setPhone($config['storePhone']);
            $CompanyInfo->setFax('');
            $CompanyInfo->setWebsite($config['websiteUrl']);
            return $this->response($CompanyInfo->getCompanyInfo());		
	}

	function getStores($username,$password,$others)
	{
            global $objectManager;
            $responseArray = array();
            $status =  $this->CheckUser($username,$password);
            if($status!="0")
            {		
                return $status;
            }
            $Stores = new Storesinfo();		

            $Stores->setStatusCode('0');
            $Stores->setStatusMessage('All Ok');

            $stores = $this->getStoresData();
            if(count($stores)>0)
            {
                $s=0;
                for($i=0;$i<count($stores['items']);$i++)
                {
                    if($stores['items'][$i]['group_id']>0)
                    {
                        $views = $objectManager->get('\Magento\Store\Model\Store')
                                                        ->getCollection()
                                                        ->addGroupFilter($stores['items'][$i]['group_id'])
                                                        ->load();
                        $views = $views->toArray();
                        foreach($views['items'] as $view)
                        {
                            $Store = new Store();
                            $Store->setStoreID($view['store_id']);
                            $Store->setStoreName($stores['items'][$i]['name']."->".$view['name']);
                            $Store->setStoreWebsiteId($stores['items'][$i]['website_id']);
                            $Store->setStoreWebsiteName($stores['items'][$i]['website_name']);
                            $Store->setStoreRootCategoryId($stores['items'][$i]['root_category_id']);
                            $Store->setStoreDefaultStoreId($stores['items'][$i]['default_store_id']);		

                            $Stores->setstores($Store->getStore());							
                        }
                    }
                }
            }

            return $this->response($Stores->getStoresInfo());				
	}


	function getStoresData()
	{
            global $objectManager;
            //$storeManagerObj = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            
            $websites = $objectManager->get('\Magento\Store\Model\Website')
				->getResourceCollection()
				->setLoadDefault(true)
				->load();
		$websites1 =  $websites->toArray();
            //print_r($websites1);
            unset($websites);
            
            $stores = $objectManager->get('\Magento\Store\Model\Group')
				->getResourceCollection()
				->setLoadDefault(true)
				->load();
		$stores = $stores->toArray();
            //print_r($stores);
                
            for($i=0;$i<count($websites1['items']);$i++)
            {

                if($websites1['items'][$i]['website_id']>0)
                {
                    $websites[$websites1['items'][$i]['website_id']] = $websites1['items'][$i]['name'];
                }
            }
            for($i=0;$i<count($stores['items']);$i++)
            {
                if($stores['items'][$i]['group_id']>0)
                {
                    $stores['items'][$i]['website_name'] = $websites[$stores['items'][$i]['website_id']];
                }
            }
            return $stores;
	}
	
	function getPaymentMethods($username,$password,$storeid=1,$others)
	{
            global $objectManager;
            $status =  $this->CheckUser($username,$password);
            if($status!="0")
            {		
                    return $status;
            }
            
            $PaymentMethods = new PaymentMethods();
            $PaymentMethods->setStatusCode('0');		 
            $PaymentMethods->setStatusMessage('All Ok');		 
            
            $paymentObj = $objectManager->get('Magento\Payment\Helper\Data');
            
            $i=1;
            $result = [];
            foreach ($paymentObj->getPaymentMethods() as $code => $method) {
                if(!isset($method['title']))
                    continue;
                $PaymentMethod = new PaymentMethod();
                $PaymentMethod->setMethodId($i);
                $PaymentMethod->setMethod($method['title']);
                $PaymentMethod->setDetail($code);

                $PaymentMethods->setPaymentMethods($PaymentMethod->getPaymentMethod());
                $i++;
            }
        
            return $this->response($PaymentMethods->getPaymentMethods());
	}	
	
	
	function getShippingMethods($username,$password,$storeid=1,$others)
	{
            $status =  $this->CheckUser($username,$password);
            if($status!="0")
            {		
                    return $status;
            }
            $ShippingMethods = new ShippingMethods();
            $ShippingMethods->setStatusCode('0');
            $ShippingMethods->setStatusMessage('All Ok');
	
            $carriers = $this->_getshippingMethods($storeid);
            if($carriers=="error_msg")
            {
                $ShippingMethods->setStatusCode('1');
                $ShippingMethods->setStatusMessage('Please enable at least one shipping method on online store.');
                $ShippingMethod = new ShippingMethod();
                $ShippingMethod->setCarrier("");
                $ShippingMethod->setMethods("");
                $ShippingMethods->setShippingMethods($ShippingMethod->getShippingMethod());
            }else{
		if(is_array($carriers))
                    {//print_r($carriers);
                        $j=0;
                        foreach($carriers as $k=>$v)
                        { //echo"--".$k."--";
                            if($k != "" && $carriers[$k]['value']!="")
                            {
                                $ShippingMethod = new ShippingMethod();
                                $ShippingMethod->setCarrier($carriers[$k]['label']);
                                for($i=0;$i < count($carriers[$k]['value']);$i++)
                                {
                                    $ShippingMethod->setMethods($carriers[$k]['value'][$i]['label']);
                                }
                                $j++;
                                $ShippingMethods->setShippingMethods($ShippingMethod->getShippingMethod());
                            }
                        }
                    }
		}
		
		return $this->response($ShippingMethods->getShippingMethods());
	}
	
	
	public function _getshippingMethods($storeid=1,$isActiveOnlyFlag=false)
	{
            global $get_Active_Carriers,$objectManager;

            $isActiveOnlyFlag = $get_Active_Carriers;
            
            $shippingObj = $objectManager->get('\Magento\Shipping\Model\Config');
            $scopeConfigObj = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
            
            $methods = [['value' => '', 'label' => '']];
            $carriers = $shippingObj->getAllCarriers();
		
            try{
                foreach ($carriers as $carrierCode => $carrierModel) {
                if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                    continue;
                }
                $carrierMethods = $carrierModel->getAllowedMethods();
                if (!$carrierMethods) {
                    continue;
                }
                $carrierTitle = $scopeConfigObj->getValue(
                    'carriers/' . $carrierCode . '/title',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $methods[$carrierCode] = ['label' => $carrierTitle, 'value' => []];//print_r($carrierMethods);
                foreach ($carrierMethods as $methodCode => $methodTitle) {
                    $methods[$carrierCode]['value'][] = [
                        'value' => $carrierCode . '_' . $methodCode,
                        //'label' => '[' . $carrierCode . '] ' . $methodTitle,
                        'label' => $methodTitle,
                    ];
                }
                //print_r($methods);
                return  $methods;
            }
        }catch (Exception $e) 
        {
         return "error_msg";
        }	
	
    }
	
	function getCategory($username,$password,$storeid=1,$others)
	{
		$storeid = $this->getDefaultStore($storeid);				
		$status = $this->CheckUser($username,$password);
		if($status!="0")
		{		
			return $status;
		}
		$Categories = new Categories();
		$Categories->setStatusCode('0');
		$Categories->setStatusMessage('All Ok');
		
		$categoriesData = $this->_getcategory($storeid);
		
		if($categoriesData)
			{

				for($i=0;$i <count ($categoriesData);$i++)
				{
				
					if($categoriesData[$i]['category_id']=='' || $categoriesData[$i]['name']=='')
					{
					}else{
						$Category =new Category();
						$Category->setCategoryID($categoriesData[$i]['category_id']);
						$Category->setCategoryName($categoriesData[$i]['name']);
						$Category->setParentID($categoriesData[$i]['parent_id']);
						
						$Categories->setCategories($Category->getCategory());	
					}
				}
			}		

		return $this->response($Categories->getCategories());
	}


	public function _getcategory($storeId=1)
	{
            global $objectManager;
            $categoryObj = $objectManager->get('Magento\Catalog\Model\Category');
            $categories = $categoryObj->getCollection()->getAllIds();
            $result = array();
            foreach($categories as $catVal){
                $category = $categoryObj->load($catVal);
                //echo $catObj->getName();
                if($category->getParentId()!='' )
                {

                    $rootId[] = $category->getId();
                    $result[] = array(
                        'category_id' => $category->getId(),
                        'parent_id'   => $category->getParentId(),
                        'name'        => $category->getName(),
                        'is_active'   => $category->getIsActive(),
                        'position'    => $category->getPosition(),
                        'level'       => $category->getLevel()
                    );
                }
            }
            
             return $result;
	}
	
	function getTaxes($username,$password,$storeid=1,$others)
	{
            //$storeId=$this->getDefaultStore($storeid);
            $status = $this->CheckUser($username,$password);
            if($status!="0")
            {		
                    return $status;
            }
            $Taxes = new Taxes();
            $Taxes->setStatusCode('0');
            $Taxes->setStatusMessage('All Ok');

            $taxesData = $this->_gettaxes($storeid);
            if($taxesData)
            {
                    foreach($taxesData as $taxKey => $taxValue)
                    {
                            $Tax =new Tax();
                            $Tax->setTaxID($taxKey);
                            $Tax->setTaxName($taxValue);
                            $Taxes->setTaxes($Tax->getTax());	
                    }
            }
            return $this->response($Taxes->getTaxes());
	}

	public function _gettaxes($storeId=1)
	{
            global $objectManager;
            $searchCriteria = $objectManager->get('\Magento\Framework\Api\SearchCriteriaInterface');
            $taxRepository = $objectManager->get('Magento\Tax\Model\TaxClass\Repository')->getList($searchCriteria);
            //echo $taxRepository->getTotalCount();
            //$taxClassData[0] = "None";
            $taxClassData = array();
            foreach($taxRepository->getItems() as $taxItem){
                //echo $taxItem->getClassName();echo $taxItem->getClassId();
                $taxClassData[$taxItem->getClassId()] = $taxItem->getClassName();
            }
            return $taxClassData;
    }
	
	function getManufacturers($username,$password,$storeid=1,$others)
	{
            //$storeId=$this->getDefaultStore($storeid);
            $status = $this->CheckUser($username,$password);
            if($status!="0")
            {		
                    return $status;
            }
            $Manufacturers = new Manufacturers();
            $Manufacturers->setStatusCode('0');
            $Manufacturers->setStatusMessage('All Ok');

            $manufacturersData = $this->_getmanufacturers($storeid);
            if($manufacturersData)
            {
                    foreach($manufacturersData as $manufKey=>$manufVal)
                    {
                            $Manufacturer =new Manufacturer();
                            $Manufacturer->setManufacturerID($manufKey);
                            $Manufacturer->setManufacturerName($manufVal);
                            $Manufacturers->setManufacturers($Manufacturer->getManufacturer());
                    }
            }
            return $this->response($Manufacturers->getManufacturers());
	}
	
	public function _getmanufacturers($storeid=1)
	{
            $optionCollection = array();
            global $objectManager;
            
            $eavConfig = $objectManager->get('\Magento\Eav\Model\Config');
            
            $attribute = $eavConfig->getAttribute('catalog_product', 'manufacturer');
            if (!$attribute || !$attribute->getAttributeId()) {
                $manufAttributeId = 80;
            }
            else
                 $manufAttributeId = $attribute->getAttributeId();
            $attrOptionCollectionFactory = $objectManager->get('\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection');
            $valuesCollection = $attrOptionCollectionFactory->setAttributeFilter($manufAttributeId)->setStoreFilter(null,true)->load();
            $manufacturer = array();
            foreach ($valuesCollection as $item) {
                $manufacturer[$item->getId()] = $item->getValue();
            }
            //print_r($values);
            return($manufacturer);
 	}
					  
	function getItems($username,$password,$DownloadType,$UpdatedDate,$start_item_no=0,$limit,$datefrom,$storeId=1,$others)
	{
            global $objectManager;
            global $set_Special_Price,$set_Short_Description;

            $storeId=$this->getDefaultStore($storeId);				
		
            $status =  $this->CheckUser($username,$password);
            if($status!="0")
            {		
                return $status;
            }
            
            $stockRegistryInterface = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
            
            $Items = new Items();
            if($UpdatedDate!="")
            {

                $UpdatedDate=explode(".0",$UpdatedDate);
                $ini_date=explode(" ",$UpdatedDate[0]);
                $mid_date=explode("/",$ini_date[0]);
                $final_date=$mid_date[2]."-".$mid_date[0]."-".$mid_date[1];
                $date_time_final=$final_date." ".$ini_date[1];

                $date_time=$date_time_final;

            }
		
            if($DownloadType=="byupdateddate" && $date_time!="")
            {
                $items_query_product = $this->getProduct($storeId,$start_item_no,$limit,$date_time,$others);		
                //$count_query_product = $items_query_product->getSize();
            }
            else
            {

                $items_query_product = $this->getProduct($storeId,$start_item_no,$limit,$datefrom,$others);		
                //$count_query_product = $items_query_product->getSize();
            }	
            
            $count_query_product = $items_query_product->getSize();
            //$count_query_product = $items_query_product->getTotalCount();
            
            $system_date_val=date("m/d/Y , H:i:s");
            $Items->setServertime($system_date_val);
		
            if($count_query_product>0)
            {
                $Items->setStatusCode('0');
                $Items->setStatusMessage('All Ok');
                $Items->setTotalRecordFound($count_query_product?$count_query_product:'0');
                $Items->setTotalRecordSent(count($items_query_product->getItems())?count($items_query_product->getItems()):'0');

                #get the manufacturer
                $manufacturerData = $this->_getmanufacturers();

                foreach ($items_query_product->getItems() as $itemVal)
                {
					
                    $productData['category_ids'] = $itemVal->getCategoryIds();
                    
                    $productData = $itemVal->toArray();
                    //print_r($productData);continue;
                    if($productData['type_id']=='simple' || $productData['type_id']=='virtual' || $productData['type_id']=='downloadable')
                    {
                        $Item = new Item();
                        if($set_Short_Description)
                        {
                            $desc=addslashes(htmlspecialchars(substr($productData['short_description'],0,4000),ENT_QUOTES));
                        }else{
                            $desc=addslashes(htmlspecialchars(substr($productData['description'],0,4000),ENT_QUOTES));
                        }
						
                        ##working to get based on website id
                        //$stockStateInterface = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                        //$stockItem = $stockStateInterface->getStockQty($productData['entity_id'],1);//getStockItem($productData['entity_id']);
                        //end

                        $stockItem = $stockRegistryInterface->getStockItem($productData['entity_id'])->toArray();
                        
                        $Item->setItemID($productData['entity_id']);
                        $Item->setItemCode($productData['sku']);

                        //$desc=addslashes(htmlspecialchars(substr($productData['description'],0,4000),ENT_QUOTES));
                        //$desc=addslashes(substr($productData['description'],0,4000));
                        
                        $desc=substr($productData['description'],0,4000);
                        $Item->setItemDescription(strip_tags($productData['name']));
                        $Item->setItemShortDescr(strip_tags($desc));

                        $catArray = array();
                        if(is_array($productData['category_ids']))
                        {
                            $categoriesI = 0;
                            foreach ($productData['category_ids'] as $category)
                            {
                                unset($catArray);
                                $catArray['CategoryId'] = $category;
                                $Item->setCategories($catArray);
                                $categoriesI++;
                            }
                        }
                        if(!$categoriesI)
                            $Item->setCategories($catArray);
						
						
                        @$productData['manufacturer'] = @$productData['manufacturer']?$manufacturerData[@$productData['manufacturer']]:@$productData['manufacturer'];
												
                        $Item->setManufacturer($productData['manufacturer']);
                        $Item->setQuantity($stockItem['qty']);
                        $Item->setCostPrice(@$productData['cost']);
                        
                        if($set_Special_Price)
                        {
                            $Item->setUnitPrice($productData['special_price']);
                        }else{ 
                            $Item->setUnitPrice($productData['price']);
                        }
                        
                        $Item->setListPrice(@$productData['cost']);
                        $Item->setWeight($productData['weight']);
                        $Item->setLowQtyLimit($stockItem['min_qty']);
                        $Item->setFreeShipping('N');
                        $Item->setDiscounted('');
                        $Item->setShippingFreight('');
                        $Item->setWeight_Symbol('lbs');
                        $Item->setWeight_Symbol_Grams('453.6');
						
                        if(empty($productData['tax_class_id']) || $productData['tax_class_id']=="0" )
                        {
                            $Item->setTaxExempt('N');
                        }else{
                            $Item->setTaxExempt('Y');
                        }
                        $Item->setUpdatedAt($productData["updated_at"]);
                        $Item->setItemType($productData['type_id']);
                        $Items->setItems($Item->getItem()); 
						
                    }
                    //$itemI++;
                } // end items
            }
            else
            {
                $Items->setStatusCode('0');
                $Items->setStatusMessage('All Ok');
            }
            return $this->response($Items->getItems());
	}

	function getOrderStatus($username,$password,$storeid=1,$others)
	{
		$storeId=$this->getDefaultStore($storeid);
		$status = $this->CheckUser($username,$password);
		if($status!="0")
		{		
			return $status;
		}
		$OrderStatuses = new OrderStatuses();
		$OrderStatuses->setStatusCode('0');
		$OrderStatuses->setStatusMessage('All Ok');
		
		$orderstatusesData = $this->_getorderstatuses($storeid);
		if($orderstatusesData)
		{
			$invoiceflag = 0;
			$i=0;
			foreach($orderstatusesData as $id=>$val)
			{
				$OrderStatus =new OrderStatus();
				$OrderStatus->setOrderStatusID($id);
				$OrderStatus->setOrderStatusName($val);
				$OrderStatuses->setOrderStatuses($OrderStatus->getOrderStatus());
				if($id == 'invoice')
					$invoiceflag = 1;
				$i++;
			}
			if($invoiceflag != 1){
				$OrderStatus =new OrderStatus();
				$OrderStatus->setOrderStatusID('Invoice');
				$OrderStatus->setOrderStatusName('Invoice');
				$OrderStatuses->setOrderStatuses($OrderStatus->getOrderStatus());
			}
		}
		return $this->response($OrderStatuses->getOrderStatuses());
	}

	public function _getorderstatuses($storeId=1)
	{
            global $objectManager;
            $orderStatusData = $objectManager->get('Magento\Sales\Model\Order\Config')->getStatuses();
            return($orderStatusData);
	}
        
	function getAttributesets($username,$password,$storeid=1,$others)
	{
		//$storeId=$this->getDefaultStore($storeid);
		$status = $this->CheckUser($username,$password);
		if($status!="0")
		{		
			return $status;
		}
		$Attributesets = new Attributesets();
		$Attributesets->setStatusCode('0');
		$Attributesets->setStatusMessage('All Ok');
		
		$attributesetsData = $this->_getattributesets($storeid);
		
                if(count($attributesetsData)>0)
		{
			$i =0;
			foreach($attributesetsData as $aSet_value)
			{
				$Attributeset =new Attributeset();
				$Attributeset->setAttributesetID($aSet_value['attribute_set_id']);
				$Attributeset->setAttributesetName($aSet_value['attribute_set_name']);
				$Attributesets->setAttributesets($Attributeset->getAttributeset());
				$i++;
			}
		}
		return $this->response($Attributesets->getAttributesets());
	}
	
	public function _getattributesets($storeId=1)
	{
            global $objectManager;
            
            $entityTypeCode = 'catalog_product';
            //$entityType = $objectManager->get('Magento\Eav\Model\Config')->getEntityType($entityTypeCode);
            //$searchCriteria = $objectManager->get('\Magento\Framework\Api\SearchCriteriaInterface');
            
            $searchCriteriaBuilder = $objectManager->get('\Magento\Framework\Api\SearchCriteriaBuilder');
            $filterBuilder = $objectManager->get('\Magento\Framework\Api\FilterBuilder');
            
            $searchCriteriaBuilder->addFilters(
                [
                    $filterBuilder
                        ->setField('entity_type_code')
                            ->setValue($entityTypeCode)
                        ->setConditionType('eq')
                        ->create(),
                ]
            );
            
            $attributeSetData = $objectManager->get('Magento\Eav\Model\AttributeSetRepository')->getList($searchCriteriaBuilder->create());
            
            $attributeSet = array();
            foreach($attributeSetData->getItems() as $val){
                $attributeSet[] = $val->toArray();
                //    echo $temp->getId();print_r($temp->toArray());
            }
            return $attributeSet;
	}
        
	public function _addproduct($storeId=1)
	{
            global $objectManager;
			$Product = $objectManager->create('Magento\Catalog\Model\Product');
            $Product  = $Product->setStoreId($storeId);
            $Product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
            $Product->setWebsiteIds(array($objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getWebsiteId()));
            return $Product;
	}


	function addProduct($username,$password,$item_json_array,$storeid=1,$others)
	{
            global $objectManager,$Set_ReorderPoint;
            
            $status=$this->CheckUser($username,$password);		
            if($status!="0")
            {		
                return $status;
            }
            
            $version = $this->getVersion();
            if($version != '1.2.1.2')
            {
                $storeId=$this->getDefaultStore($storeid);
            }
		
            $stores = $objectManager->get('Magento\Store\Model\Store')->getResourceCollection()->setLoadDefault(true)->addGroupFilter($storeId)->load();
            $stores = $stores->toArray();
            $website_id = $stores['items'][0]['website_id'];
            unset($stores);

            $Items = new Items();
            $Items->setStatusCode('0');
            $Items->setStatusMessage('All Ok');
		
            $requestArray = $item_json_array;

            if (!is_array($requestArray))
            {
                $Items->setStatusCode('9997');
                $Items->setStatusMessage('Unknown request or request not in proper format');				
                return $this->response($Items->getItems());
            }

            if (count($requestArray) == 0)
            {
                $Items->setStatusCode('9996');
                $Items->setStatusMessage('REQUEST tag(s) doesnt have correct input format');
                return $this->response($Items->getItems());
            }
		
            //print_r($requestArray);die('testing');
            foreach($requestArray as $k=>$vItem)//request
            { 
                $productcode=$vItem['ItemCode'];
                $product=$vItem['ItemName'];
                $descr=$vItem['ItemDesc'];
                $free_shipping=$vItem['FreeShipping'];
                $free_tax=$vItem['TaxExempt'];
                $tax_id=$vItem['TaxID'];
                $item_match=$vItem['ItemMatchBy'];
                $manufacturerid=$vItem['ManufacturerID'];
                $avail_qty=$vItem['Quantity'];
                $price=$vItem['ListPrice'];
                $cost =$vItem['CostPrice'];
                $weight=$vItem['Weight'];

                $visibility=$vItem['ItemVisibility'];
                $ItemStatus=$vItem['ItemStatus'];
                $attributesetid=$vItem['AttributeSetID'];
                if($Set_ReorderPoint)
                {
                    $ReorderPoint=$vItem['ReorderPoint'];
                }

                $categoryid = "";
                if(is_array($vItem['Categories']))
                {
                    $arrayCategories=$vItem['Categories'];
                    $cateId = array();
                    foreach($arrayCategories as $k3=>$vCategories)//Categories
                    {
                        $categoryid .= $vCategories['CategoryId'].",";
                    }
                    $categoryid = strrev(substr($categoryid,0,-1));
                    $categoryid = strrev($categoryid);
                }
						
                $forsale = "Y";
                $provider = "master";
                $list_price = $price;
                $fulldescr =$descr;
                $min_amount = "1";
						
                $entityTypeId = $objectManager->get('Magento\Eav\Model\Config')->getEntityType('catalog_product');
                
                if(isset($attributesetid)&& $attributesetid!='')
                {
                    $attributeSet['attribute_set_id'] = $attributesetid;
                }else
                {
                    $attributeSet = $objectManager->get('\Magento\Eav\Model\Entity\Attribute\Set')->getResourceCollection()
                                        ->setEntityTypeFilter($entityTypeId)
                                        ->addFilter('attribute_set_name', 'Default')
                                        ->getLastItem();
                                                                    
                    $attributeSet = $attributeSet->toArray();
                }
						
                if ($this->getduplicaterecord($product,$productcode)==0)
                {
                        $data = array();

                        $Product = $this->_addproduct($storeId);
                        $Product->setCategoryIds($categoryid);

                        $data['name'] = trim($product);//'testp';
                        $data['sku'] = trim($productcode);//'testp114512';

                        $data['manufacturer'] = $manufacturerid;//'''122';
                        $data['description'] = $descr."testing";//'test';
                        $data['short_description'] = $descr;//'test';
                        $data['qty'] = $avail_qty;//'test';
                        //$data['stock_data']['qty'] = $avail_qty;//'58';
                        $data['attribute_set_id']=$attributeSet['attribute_set_id'];
                        $data['price'] = $price;//'100';
                        $data['cost'] = $cost;//'100';
                        if($tax_id)
                                $data['tax_class_id'] = $tax_id;// '1';
                        else
                                $data['tax_class_id'] = 0;
                        $data['weight'] = $weight;//'1';
                        $data['stock_data']['use_config_manage_stock'] = 1;
                        $data['stock_data']['is_in_stock'] = 1;
                        
                        ##CUSTOMIZATION Ticket ID # KBE-163-87926
                        if($Set_ReorderPoint)
                        {
                            $data['stock_data']['min_qty']=$ReorderPoint;
                        }
                        
                        $data['status'] = $ItemStatus;
                        $data['website_id'] = $website_id;
                        //$data['entity_type_id'] = 4;
                        
                        if($entityTypeId!="" && isset($entityTypeId))
                        {
                            $data['entity_type_id'] = $entityTypeId;
                        }else{
                            $data['entity_type_id'] = 4;
                        }

                        if($visibility)
                            $data['visibility'] = $visibility;
                        else
                            $data['visibility'] = '1';
								
			##CUSTOMIZATION FOR inserting some new attributes Ticket ID # RMO-576-72514 				
                        $attributeNameIdArray=array();
                        /*
                        if((is_array($vItem['Others']['CustomFields'])) && (!empty($vItem['Others']['CustomFields'])))
                        {

                                $OthersCustomFields=$vItem['Others']['CustomFields'];
                                if(!empty($OthersCustomFields))
                                {
                                        foreach($OthersCustomFields as $key=>$value)
                                        {
                                                 $FieldName=$value['FieldName'];
                                                 $attributeNameIdArray[$FieldName]=$this->attributeExist($value['FieldName'],$value['FieldValue']);

                                        }
                                }

                                if(!empty($attributeNameIdArray))
                                {
                                        foreach($attributeNameIdArray as $k=> $v)
                                        {
                                                $key=strtolower($k);
                                                $data[$key]=$v;
                                        }	
                                }

                        }
                        */
                        ##END OF CUSTOMIZATION Ticket ID # RMO-576-72514
								
                        $Product->addData($data);

                        $Product->save();
                        $productId = $Product->getId();
                        
                        $stockRegistryInterface = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
                        $stockItem = $stockRegistryInterface->getStockItem($productId);
                        //$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                        $stockItem->setQty($avail_qty);
                        $stockItem->setIsInStock(true);
                        $stockItem->save();						
							
                        #Calling function for add image
                        if($vItem['Image']) {
                            $this->addItemImage($productId,$vItem['Image'],$storeid=1,$Thumbnail=1);
                        }
                        if(!empty($vItem['Image2'])) {
                            $this->addItemImage($productId,$vItem['Image2'],$storeid=1,$Thumbnail=0);
                        }
							
                        $Item = new Item();
                        $Item->setStatus('Success');
                        $Item->setProductID($productId);
                        $Item->setSku($productcode);
                        $Item->setProductName($product);
                        $Items->setItems($Item->getItem());
							
                }
                else
                {			
                    $Item = new Item();
                    $Item->setStatus('Duplicate product code exists');
                    $Item->setProductID('');
                    $Item->setSku($productcode);
                    $Item->setProductName($product);
                    $Items->setItems($Item->getItem());
                }				
                unset($attributes,$n_id,$attribute_option,$attribute_option1);
            }			
            return $this->response($Items->getItems());
	}

    	/*
         ##USED IN ADD PRODUCT BUT NO MORE IN USE @ 12-FAB-2016
         function attributeExist($attributename,$attributevalue)
	{
            $model_att_options = Mage::getModel('eav/entity_attribute_source_table');
            $attribute_model = Mage::getModel('eav/entity_attribute'); 
            $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ; 
            $attribute_code = $attribute_model->getIdByCode('catalog_product',$attributename);
            $attribute = $attribute_model->load($attribute_code)->setStoreId(0);  

            $attribute_table        = $attribute_options_model->setAttribute($attribute);
            $options                = $attribute_options_model->getAllOptions(true);



            if(is_array($options)&& !empty($options))
            {
                    foreach($options as $key=>$values)
                    {

                            if($values['label']==$attributevalue ||  strtolower($values['label'])==strtolower($attributevalue))
                            {
                                    return $values['value'];
                            }
                    }
            }else{

                    return false;

            }
	}
         */
	
	
	function GetImage($username,$password,$data,$storeid=1,$others)
	{
	    global $objectManager;
            $storeId=$this->getDefaultStore($storeid);		
            $status=$this->CheckUser($username,$password);

            if($status!="0")
            {		
                    return $status;
            }

            $Items = new Items();
            $Items->setStatusCode('0');
            $Items->setStatusMessage('All Ok');

            $requestArray = $data;

            if (!is_array($requestArray))
            {
                $Items->setStatusCode('9997');
                $Items->setStatusMessage('Unknown request or request not in proper format');				
                return $this->response($Items->getItems());				
            }

            if (count($requestArray) == 0)
            {
                $Items->setStatusCode('9996');
                $Items->setStatusMessage('REQUEST array(s) doesnt have correct input format');				
                return $this->response($Items->getItems());				
            }

            $version = $this->getVersion();
		
            foreach($requestArray as $k=>$v4)//request
            {
                $status ="Success";
                $productID = $v4['ItemID'];
                $_images = $objectManager->get('Magento\Catalog\Model\Product')->load($productID)->getMediaGalleryImages();

                if($_images)
                {
                    foreach($_images as $_image)
                    {
                        $responseArray = array();
                        $responseArray['ItemID'] = $productID;
                        $responseArray['Image'] = base64_encode(file_get_contents($_image->path));
                        $Items->setItems($responseArray);
                        continue;			
                   }
                }
            }		
            return $this->response($Items->getItems());
	}


	#Add image functionality code area

	public function getProductById($storeId=1,$start_item_no=0,$items)
	{
		if($start_item_no > 0)
		{
			if($start_item_no>$limit)
			{
				$start_no=intval($start_item_no/$limit)+1;
			}else{
				$start_no=intval($limit/$start_item_no)+1;
			}
		}else{
			$start_no = 0;
		}
		$productsCollection = Mage::getResourceModel('catalog/product_collection')
								->addAttributeToSelect('*')
								->addStoreFilter($storeId)
								->addAttributeToFilter('entity_id', $start_item_no)
								->addAttributeToSort('entity_id', 'asc')
								->setPageSize($limit)
								->setCurPage($start_no);

							

		return $productsCollection;
	}

	function getImageResponseArray($start, $fileNameParam, $storeid) {

		$objNew = new Webgility_Shop1_Model_Runmobile();
		$responseArray = array();

		$responseArray['StatusCode'] = '0';
		$responseArray['StatusMessage'] = 'All Ok';
		$responseArray['ItemImageFlag']	=	'1';

		$items_query_product = $objNew->getProductById($storeid,$start,1);
		$count_query_product = $items_query_product->getSize();
		if(count($items_query_product)>0)
		{

			$itemI = 0;
			foreach ($items_query_product->getItems() as $iInfo11)
			{
				$iInfo = $iInfo11->toArray();//print_r($iInfo);
				if($iInfo['type_id']=='simple' || $iInfo['type_id']=='virtual' || $iInfo['type_id']=='downloadable')
				{


					$obj = Mage::getModel('catalog/product');
					$_product_for_image_url = $obj->load($iInfo['entity_id']); // Enter your Product Id in $product_id

					$_images = Mage::getModel('catalog/product')->load($iInfo11->getId())->getMediaGalleryImages();

					/*?><pre><?php print_r($_images->toArray());die('reached');?></pre><?php*/

					if($_images){
					   $i=0;
					   foreach($_images as $_image){

							$fileNameStr	=	$_image->getFile();
							//echo strrpos($fileNameStr,'/').'<br/>'.strlen($fileNameStr).'<br/>';
							$fileName		=	substr($fileNameStr, strrpos($fileNameStr,'/')+1, strlen($fileNameStr));
							$fileNameParamValue = substr($fileNameParam,0,strpos($fileNameParam, '.'));
							if(strpos($fileName,$fileNameParamValue,0)===false) {}
							else{
								$responseArray['Items'][$itemI]['ItemImages'][$i]['ItemID']				=	$iInfo['entity_id'];
								$responseArray['Items'][$itemI]['ItemImages'][$i]['ItemImageID']		=	$_image->getId();
								$responseArray['Items'][$itemI]['ItemImages'][$i]['ItemImageFileName']	=	$fileName;
								$responseArray['Items'][$itemI]['ItemImages'][$i]['ItemImageUrl']		=	$_image->getUrl();
								$i++;
								break;
							}
					   }
					}
				}
				$itemI++;
			} // end items
		}
		return $responseArray;
	}

	function addItemImage($itemid,$image,$storeid=1, $Thumbnail) 
        {
            //echo $username.','.$password.','.$itemid;die('reached');
            $responseArray = array();

            $fileName = time().'.jpg';
            //Base 64 encoded string $image
            $ImageData = base64_decode($image);
            $imageUrl = $this->saveImage($itemid, $fileName, $ImageData, $Thumbnail);
	}


	function _createDestinationFolder($destinationFolder)
	{
		if( !$destinationFolder ) {
			return $this;
		}

		if (substr($destinationFolder, -1) == DIRECTORY_SEPARATOR) {
			$destinationFolder = substr($destinationFolder, 0, -1);
		}

		if (!(@is_dir($destinationFolder) || @mkdir($destinationFolder, 0755, true))) {
                    die("Unable to create directory '{$destinationFolder}'.");
			throw new Exception("Unable to create directory '{$destinationFolder}'.");
		}
		return $this;

                ##BELOW CODE NO MORE IN USE SINCE ALREADY RETURN BEFORE @12-FAB-2016
                /*
		$destinationFolder = str_replace('/', DIRECTORY_SEPARATOR, $destinationFolder);
		$path = explode(DIRECTORY_SEPARATOR, $destinationFolder);
		$newPath = null;
		$oldPath = null;
		foreach( $path as $key => $directory ) {
			if (trim($directory)=='') {
				continue;
			}
			if (strlen($directory)===2 && $directory{1}===':') {
				$newPath = $directory;
				continue;
			}
			$newPath.= ( $newPath != DIRECTORY_SEPARATOR ) ? DIRECTORY_SEPARATOR . $directory : $directory;
			if( is_dir($newPath) ) {
				$oldPath = $newPath;
				continue;
			} else {
				if( is_writable($oldPath) ) {
					mkdir($newPath, 0777);
				} else {
					throw new Exception("Unable to create directory '{$newPath}'. Access forbidden.");
				}
			}
			$oldPath = $newPath;
		}
		return $this;
                */
	}

	function _addDirSeparator($dir)
	{
            if (substr($dir,-1) != DIRECTORY_SEPARATOR) {
                    $dir.= DIRECTORY_SEPARATOR;
            }
            return $dir;
	}

	function getDispretionPath($fileName)
	{
		$char = 0;
		$dispretionPath = '';
		while( ($char < 2) && ($char < strlen($fileName)) ) {
			if (empty($dispretionPath)) {
				$dispretionPath = DIRECTORY_SEPARATOR.('.' == $fileName[$char] ? '_' : $fileName[$char]);
			}
			else {
				$dispretionPath = $this->_addDirSeparator($dispretionPath) . ('.' == $fileName[$char] ? '_' : $fileName[$char]);
			}
			$char ++;
		}
		return $dispretionPath;
	}



	function saveImage($productId, $fileName, $ImageData, $Thumbnail)
	{
            global $objectManager;
            
            $config = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');

            $mediaDirectory = $objectManager->get(
                'Magento\Framework\Filesystem'
            )->getDirectoryWrite(
                Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            );
            
            $imgpath = $config->getBaseMediaPath();
			$destFile = $mediaDirectory->getAbsolutePath($imgpath) . DIRECTORY_SEPARATOR . $fileName;
			
            $file = @fopen($destFile, 'w+');// or die('Permission error');
            if($file){
                fwrite($file, $ImageData);
                fclose($file);

                $product = $objectManager->get('Magento\Catalog\Model\Product');
                $product->load($productId);

                if($Thumbnail==1) 
                {
                    $mediaAttribute = array ('image','thumbnail' );
                }
                else 
                {
                    $mediaAttribute = array ('image');//array ('types' => 'image')
                }

                $product->addImageToMediaGallery($destFile, $mediaAttribute, true, false);
                $product->save();//die('test');
            }
            return $destFile; 
	}
	#Add image functionality code area


	function synchronizeItems($username,$password,$item_json_array,$storeid=1,$others)
	{
	    global $objectManager,$set_Special_Price,$set_Short_Description;
		
            $storeId=$this->getDefaultStore($storeid);		
            $status=$this->CheckUser($username,$password);

            if($status!="0")
            {		
                return $status;
            }
		
            $Items = new Items();
            $Items->setStatusCode('0');
            $Items->setStatusMessage('All Ok');
		
            $requestArray = $item_json_array;
            $pos = strpos($others,'/');
            if($pos)
            {
                $array_others = explode("/",$others);

            }else{
                $array_others=array();
                $array_others[0]=$others;       
            }
            if (!is_array($requestArray))
            {
                $Items->setStatusCode('9997');
                $Items->setStatusMessage('Unknown request or request not in proper format');				
                return $this->response($Items->getItems());				
            }

            if (count($requestArray) == 0)
            {
                $Items->setStatusCode('9996');
                $Items->setStatusMessage('REQUEST array(s) doesnt have correct input format');				
                return $this->response($Items->getItems());				
            }
                
            $itemsProcessed = 0;
            $i=0;
            $version = $this->getVersion();
            
            $stockRegistryInterface = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
            
            //$storeManagerObj = $objectManager->get('\Magento\Store\Model\Store');
            $Item = new Item();
            foreach($requestArray as $k=>$v4)//request
            {
                $status     = "Success";
                $productID  = $v4['ProductID'];
                //$Quantity   = $v4['Quantity'];
                $Price      = $v4['Price'];
                $ProductName= $v4['ProductName'];
                $CostPrice  = $v4['Cost'];
				
				$productsCollection = $objectManager->get('Magento\Catalog\Model\Product')->getCollection()
					->addAttributeToSelect('entity_id')
					->addAttributeToFilter('sku', $v4['Sku'])
					->load();

					$productsCollection = $productsCollection->toArray();
                    if(count($productsCollection)=="" || count($productsCollection)==0)
					{
								$Item->setStatus('Failed');
								$Item->setProductID($v4['ProductID']);
								$Item->setSku($v4['Sku']);
								$Item->setProductName($ProductName);   
								$Item->setQuantity($v4['Qty']);   
								$Item->setPrice($Price);                   
								$Item->setItemUpdateStatus('Failed');                           
								$Items->setItems($Item->getItem());
					}else{
                foreach($array_others as $ot)
                {
                    if($others=="QTY" || $others=="BOTH" || $ot=="QTY") 
                    {
                        $informationObj = $objectManager->get('Magento\CatalogInventory\Model\Configuration');
                        $configItemMinQty = $informationObj->getMinQty($storeId);
            
                        $stockItem = $stockRegistryInterface->getStockItem($productID);
                        $product_stack_detail = $stockItem->toArray();
                        
                        $ConfigBackordersValue = $objectManager->get('Magento\CatalogInventory\Api\StockConfigurationInterface')->getBackorders($storeId);
                        
                        $stockItem->setQty($v4['Qty']);

                        if($product_stack_detail['use_config_min_qty']==1)
                        {
                            $config_qty = $configItemMinQty;
                        }
                        else
                        {
                            $config_qty=$product_stack_detail['min_qty'];
                        }

                        if($product_stack_detail['use_config_backorders']==1)
                        {
                            // In this if when product geting values from config as config check box  checked
                            if($ConfigBackordersValue==0)
                            {
                                // In this if when config has  NoBackorders option
                                if($v4['Qty'] <= $config_qty)
                                {
                                    $stockItem->setIs_in_stock(0);
                                }else if($v4['Qty']==0 && $config_qty!=0)
                                {
                                    $stockItem->setIs_in_stock(1);
                                }else{
                                    $stockItem->setIs_in_stock(1);
                                }
                            }
                            else
                            {
                                // In this Else when config has  Allow Qty Below 0,Allow Qty Below 0 and Notyfy Custmor option   
                                $stockItem->setIs_in_stock(1);
                            }	
                        }else
                        {
                            // In this Else when product not geting values from config as config check box  unchecked
                            if($product_stack_detail['backorders']==0)
                            {
                                // In this if when product has  NoBackorders option
                                if($v4['Qty'] <= $config_qty)
                                {
                                    $stockItem->setIs_in_stock(0);
                                }else if($v4['Qty']==0 && $config_qty!=0)
                                {
                                    $stockItem->setIs_in_stock(1);
                                }else{
                                    $stockItem->setIs_in_stock(1);
                                }
                            }
                            else
                            {
                                // In this else when product has Allow Qty Below 0,Allow Qty Below 0 and Notyfy Custmor option 
                                $stockItem->setIs_in_stock(1);
                            }	
                        }
                        $stockItem->save();
                        
                        /*
                         ##REPEATED CODE , HAVE TO CHECK IF NO USE WE CAN REMOVE IT @ 12-FAB-2016 // JUST ONE CHANGE USING load($productID) METHOD HERE
                         if($stockItem->getQty() != $v4['Qty'])
                        {
                            $stockItem = $product_stack_detail = $stockRegistryInterface->load($productID);
                        
                            //$stockItem = Mage::getModel('cataloginventory/stock_item');
                            //$stockItem->load($productID);
                            //$stockItem->setQty($v4['Qty']);
									
                            if($product_stack_detail['use_config_backorders']==1)
                            {
                                if($ConfigBackordersValue==0)
                                {
                                    if($v4['Qty']<=$config_qty)
                                    {
                                        $stockItem->setIs_in_stock(0);
                                    }else if($v4['Qty']==0 && $config_qty!=0)
                                    {
                                        $stockItem->setIs_in_stock(1);
                                    }else{
                                        $stockItem->setIs_in_stock(1);
                                    }
                                }
                                else
                                {
                                    $stockItem->setIs_in_stock(1);
                                }	
                            }
                            else
                            {
                                if($product_stack_detail['backorders']==0)
                                {
                                    if($v4['Qty']<=$config_qty)
                                    {
                                        $stockItem->setIs_in_stock(0);
                                    }else if($v4['Qty']==0 && $config_qty!=0)
                                    {
                                        $stockItem->setIs_in_stock(1);
                                    }else{
                                        $stockItem->setIs_in_stock(1);
                                    }
                                }
                                else
                                {
                                    $stockItem->setIs_in_stock(1);
                                }	
                            }	
                            $stockItem->save();
                        }
                         */
                    }
							
                    if($others=="PRICE" || $others=="BOTH" || $ot=="PRICE") 
                    {
                        $p = $objectManager->get('Magento\Catalog\Model\Product');//new Mage_Catalog_Model_Product();
                        $p->load($productID);

                        if($set_Special_Price)
                        {
                            $p->special_price = $v4['Price'];
                            $p->save();
                            if($p->getSpecialPrice() != $v4['Price'])
                            {
                                $Product = $this->_editproduct($storeId,$productID);
                                $Product->setSpecialPrice($v4['Price']);
                                $Product->save();
                            }
                        }
                        else
                        {
                            $p->price = $v4['Price'];
                            $p->save();
                            if($p->getPrice() != $v4['Price'])
                            {
                                $Product = $this->_editproduct($storeId,$productID);
                                $Product->setPrice($v4['Price']);
                                $Product->save();
                            }
                        }
                    }

                    if($others=="COST" || $ot=="COST") 
                    {	
                        $p = $objectManager->get('Magento\Catalog\Model\Product');
                        $p->load($productID);
                        $p->cost =$CostPrice;
                        $p->save();
                        if($p->getCost() != $CostPrice)
                        {
                            $Product = $this->_editproduct($storeId,$productID);
                            $Product->setCost($CostPrice);
                            $Product->save();
                        }else{
                            $status ="Cost Price for this product not found";
                        }
                    }

                    $Item = new Item();
                    $Item->setStatus('Success');
                    $Item->setProductID($v4['ProductID']);
                    $Item->setSku($v4['Sku']);
                    $Item->setProductName($ProductName);	
                    $Item->setQuantity($v4['Qty']);	
                    $Item->setPrice($Price);					
                    $Item->setItemUpdateStatus('Success');							

                    $Items->setItems($Item->getItem());
                }	
            }
			}			
            return $this->response($Items->getItems());
	}

				
	function getOrders($username,$password,$datefrom,$start_order_no,$ecc_excl_list,$order_per_response="25",$storeid=1,$others,$ccdetails,$do_not_download_configurable_product_as_line_item,$do_not_download_bundle_product_as_line_item,$discount_as_line_item,$download_option_as_item,$donwload_state_code,$LastModifiedDate,$ProductType)
	{
            global $objectManager;
            global $display_discount_desc,$get_Active_Carriers,$RewardsPoints_Name,$set_Short_Description,$set_field_Q_CIM_and_Q_Authorization;
     			
            $Orders = new Orders();
            $orderlist='';	
            if(is_array($others))	
            foreach($others as $k=>$v)
            {
                $orderlist[] =  $v['OrderId'];
            } 		
		
            if($do_not_download_configurable_product_as_line_item && $do_not_download_bundle_product_as_line_item && $download_option_as_item )
	    {
		 $do_not_download_configurable_product_as_line_item=false;
	   	 $do_not_download_bundle_product_as_line_item=false;
	    }
		
            if($do_not_download_configurable_product_as_line_item && $do_not_download_bundle_product_as_line_item)
	    {
		 $download_option_as_item=true;
		 $do_not_download_configurable_product_as_line_item=false;
	   	 $do_not_download_bundle_product_as_line_item=false;
	    }
		
            if($do_not_download_configurable_product_as_line_item || $do_not_download_bundle_product_as_line_item)
	    {
		 $download_option_as_item=true;
	    }
	
            if(!$start_order_no==0)
            {
                $my_orders = $objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($start_order_no);
                $my_orders1 = $my_orders->toArray();
                $start_order_no = isset($my_orders1['entity_id'])?$my_orders1['entity_id'] : "";
                if(!isset($start_order_no) || $start_order_no=='')
                {
                    $start_order_no=0;
                }
            }
		
            #$start_order_no=3;
            $storeId=$this->getDefaultStore($storeid);
            if(!isset($datefrom) or empty($datefrom)) 
                $datefrom=date('m-d-Y');
            if(!isset($dateto) or empty($dateto)) 
                $dateto=date('m-d-Y');		
            
            $status=$this->CheckUser($username,$password);
            if($status!="0")
            {		
                return $status;
            }
	
            $by_updated_date = '';
            $_orders = $this->_GetOrders($datefrom,$start_order_no,$ecc_excl_list,$storeId,$order_per_response,$by_updated_date,$orderlist,$LastModifiedDate);
            $orders_array = $countorders_array = $_orders->toArray();
                
            //print_r($countorders_array);die('test');
            $country = array();
            $countryObj = $objectManager->get('\Magento\Directory\Model\Country');
            $country_data = $countryObj->getResourceCollection()->load()->toOptionArray();
                
            foreach($country_data as $ck=>$cv)
            {
                if($cv['value']!='')
                    $country[$cv['value']] = trim($cv['label']);
            }
            unset($country_data);
            
            if(array_key_exists('items',$countorders_array))
                $countorders_array = $countorders_array['items'];
            if(count($countorders_array)>0)
            {
                $orders_remained = count($countorders_array);
            }else{
                $orders_remained = 0;
            }
            
            $no_orders = false;
            if($orders_remained < 1)
            {
                $no_orders = true;
            }
            
            $Orders->setStatusCode($no_orders?"9999":"0");
            $Orders->setStatusMessage($no_orders?"No Orders returned":"Total Orders:".$_orders->getSize());
            $Orders->setTotalRecordFound($_orders->getSize()?$_orders->getSize():"0");
            $Orders->setTotalRecordSent(count($countorders_array)?count($countorders_array):"0");

            if ($no_orders)
            {
                return $this->response($Orders->getOrders());
                exit();
            }
			
            foreach ($_orders as $_order)
            {
                $Order = new Order();
                $shipments = $_order->getShipmentsCollection();
                $shippedOn='';
                foreach ($shipments as $shipment)
                {
                    $increment_id = $shipment->getIncrementId();
                    $shippedOn = $shipment->getCreated_at();
                    $shippedOn =$this->convertdateformate($shippedOn);
                }
                
                $orders=$_order->toArray();				
				
                if(!$_order->getGiftMessage())
                {
                    $giftMsgObj = $objectManager->get('\Magento\GiftMessage\Helper\Message');
                    $_order->setGiftMessage( $giftMsgObj->getGiftMessage($_order->getGiftMessageId()));
                }				
				
                $giftMessage = $_order->getGiftMessage()->toArray();
				
                ##GET PAYMENT DATA
                $_payment=$_order->getPayment();
                $payment=$_payment->toArray();
                //print_r($payment);echo"\n";
                # Latest code modififed date for  all country
                
                #changed on request of nilesh sir
                $localDate = $objectManager->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
                //echo $orders['created_at'];
                
                //if(strtotime($localDate->date($orders["created_at"],  \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT)))
                if(strtotime($orders["created_at"]))
                {
                    # Latest code modififed date for all country
                    $fdate = date("m-d-Y | h:i:s A",strtotime($orders["created_at"]));
                    $fdate = explode("|",$fdate);
                    $dateCreateOrder= trim($fdate[0]);
                    $timeCreateOrder= trim($fdate[1]);
                }else{
                    #Code is custamize for this customer
                    $dateObj= $localDate->date($orders["created_at"]);
                    $dateStrToTime=$dateObj->getTimestamp();
                    $fdate = date("m-d-Y | h:i:s A",$dateStrToTime);
                    $fdate = explode("|",$fdate);
                    $dateCreateOrder= trim($fdate[0]);
                    $timeCreateOrder= trim($fdate[1]);
                }
			
                if(!array_key_exists('billing_firstname',$orders) && !array_key_exists('billing_lastname',$orders) )
                {
                    ##GET BILLING DATA
                    $billingAddressArray = $_order->getBillingAddress()->toArray();

                    $orders["billing_prefix"]=	$billingAddressArray["prefix"];
                    $orders["billing_firstname"]=	$billingAddressArray["firstname"];
                    $orders["billing_lastname"]	=	$billingAddressArray["lastname"];
                    $orders["billing_company"]	=	$billingAddressArray["company"];
                    $orders["billing_street"]	=	$billingAddressArray["street"];
                    $orders["billing_city"]	=	$billingAddressArray["city"];
                    $orders["billing_region"]	=	$billingAddressArray["region"];
					$orders["billing_region_id"]	=	$billingAddressArray["region_id"];
                    $orders["billing_postcode"]	=	$billingAddressArray["postcode"];
                    $orders["billing_country"]	=	$billingAddressArray["country_id"];
                    $orders["customer_email"]	=	isset($billingAddressArray["customer_email"])?$billingAddressArray["customer_email"]:$orders["customer_email"];
                    $orders["billing_telephone"]=	$billingAddressArray["telephone"];
                }
                
                $Order = new Order();
                                
                $setSalesRep = '';//PENDING

                $Order->setOrderId($orders['increment_id']);
                $Order->setTitle('');
                $Order->setFirstName($orders["billing_firstname"]);
                $Order->setLastName($orders["billing_lastname"]);
                $Order->setDate($dateCreateOrder);
                $Order->setTime($timeCreateOrder);
                $Order->setLastModifiedDate($this->_dateformat_wg($orders["updated_at"]));
                $Order->setStoreID($orders['store_id']);
                $Order->setStoreName('');
                $Order->setCurrency($orders['order_currency_code']);
                $Order->setWeight_Symbol('lbs');
                $Order->setWeight_Symbol_Grams('453.6');
                $Order->setCustomerId($orders['customer_id']);
					#Credit Memo
					
				if($ProductType=='UNIFY')
				{
					$orderC = $objectManager->get('\Magento\Sales\Model\Order\Creditmemo');
				 $CreditMemoCollection = $orderC->getResourceCollection()
                    ->setOrderFilter($orders['entity_id']) ;
					
				
				$Order->setIsCreditMemoCreated("0");
				
				foreach($CreditMemoCollection as $CreditMemo1)
				{
				
					$Order->setIsCreditMemoCreated("1");
					$CreditMemo = new CreditMemo();
					
					$CreditMemo->setCreditMemoID($CreditMemo1->getincrement_id());
					$CreditMemo->setCreditMemoDate($CreditMemo1->getcreated_at());
					$CreditMemo->setSubtotal($CreditMemo1->getgrand_total());
					
						
					
					foreach ($CreditMemo1->getAllItems() as $CreditMemoItem) 
					{
						
						if(trim($CreditMemoItem->getsku())=='')
						{
							continue;
						}
						$itemInOrder = $objectManager->get('\Magento\Sales\Model\Order\Item')->load($CreditMemoItem->getorder_item_id());
						
						$OrderQty = $itemInOrder->getQtyOrdered();
						$ItemPrice = $itemInOrder->getPrice();
						
						$CancelItemDetail = new CancelItemDetail();
						$CancelItemDetail->setItemID($CreditMemoItem->getproduct_id());
						$CancelItemDetail->setItemSku($CreditMemoItem->getsku());
						$CancelItemDetail->setItemName($CreditMemoItem->getname());
						$CancelItemDetail->setQtyCancel($CreditMemoItem->getQty());
						$CancelItemDetail->setQtyInOrder($OrderQty);
					
						$CancelItemDetail->setPriceCancel($CreditMemoItem->getprice());
						$CancelItemDetail->setItemPrice($ItemPrice);						
						$CreditMemo->setCancelItemDetail($CancelItemDetail->getCancelItemDetail()); 
						
					}
					$Order->setCreditMemos($CreditMemo->getCreditMemo());
				}
				
				
				unset($CreditMemoCollection);
					
				}else{
			
				
					$orderC = $objectManager->get('\Magento\Sales\Model\Order\Creditmemo');
					$CreditMemoCollection = $orderC->getResourceCollection()
						->setOrderFilter($orders['entity_id']) ;
					
				
					$Order->setIsCreditMemoCreated("0");
					$totalccgrandtotal=0;
					$ccitemArray =  array();
					$totalccitemqty =0;

					foreach($CreditMemoCollection as $CreditMemo1)
					{
					$Order->setIsCreditMemoCreated("1");
				
					$totalccgrandtotal = $totalccgrandtotal+$CreditMemo1->getgrand_total();
						
				
					foreach ($CreditMemo1->getAllItems() as $CreditMemoItem) 
					{
						
						if(trim($CreditMemoItem->getsku())=='')
						{
							continue;
						}
						
						$totalccitemqty = $totalccitemqty+$CreditMemoItem->getQty();
						if(array_key_exists($CreditMemoItem->getsku(),$ccitemArray))
						{
							$ccitemArray[$CreditMemoItem->getsku()]["qty"] = $ccitemArray[$CreditMemoItem->getsku()]["qty"]+$CreditMemoItem->getQty();
							
						}else
						{
							$ccitemArray[$CreditMemoItem->getsku()]["qty"] =$CreditMemoItem->getQty();
							$ccitemArray[$CreditMemoItem->getsku()]["name"] =$CreditMemoItem->getname();
							$ccitemArray[$CreditMemoItem->getsku()]["ID"] =$CreditMemoItem->getproduct_id();
							$ccitemArray[$CreditMemoItem->getsku()]["orderItemId"] =$CreditMemoItem->getorder_item_id();
							
						}	
						
					}
					}
				
					if($totalccgrandtotal!=0) {
					$CreditMemo = new CreditMemo();
					$CreditMemo->setSubtotal($totalccgrandtotal);
					$CreditMemo->setCreditMemoDate($CreditMemo1->getcreated_at());
				
					foreach($ccitemArray as $cckey=>$ccval)
					{
					$itemInOrder = $objectManager->get('\Magento\Sales\Model\Order\Item')->load($ccval["orderItemId"]);
					$OrderQty = $itemInOrder->getQtyOrdered ();
					
					$CancelItemDetail = new CancelItemDetail();
					$CancelItemDetail->setItemID($ccval["ID"]);
					$CancelItemDetail->setItemSku($cckey);
					$CancelItemDetail->setItemName($ccval["name"]);
					$CancelItemDetail->setQtyCancel($ccval["qty"]);
					$CancelItemDetail->setQtyInOrder($OrderQty);
					//$CancelItemDetail->setQtyInOrder('0');
					$CancelItemDetail->setPriceCancel(round(($totalccgrandtotal/$totalccitemqty),3));
					$CancelItemDetail->setItemPrice(round(($totalccgrandtotal/$totalccitemqty),3));						
					$CreditMemo->setCancelItemDetail($CancelItemDetail->getCancelItemDetail()); 
					}
				
					$Order->setCreditMemos($CreditMemo->getCreditMemo());
				
					}
					unset($CreditMemoCollection);
				}
				
				#CREDIT MEMO END#
				
			
                if($shippedOn=='' || empty($shippedOn))
                {
                    $shippedOn=$dateCreateOrder;				
                }	
				 
                $orderStatus = $this->_getorderstatuses($storeId);
                if(array_key_exists($orders['status'],$orderStatus ))
                    $Order->setStatus($orderStatus[$orders['status']]);
                else
                    $Order->setStatus($orders['status']);

                if($payment['method']=='purchaseorder')
                {
                    $orders['customer_note'] = $orders['customer_note'] ." Purchase Order Number: ".$payment['po_number'];
                }
		
                $order_comment = '';
                $cust_comment = "";
				
                foreach ($_order->getStatusHistoryCollection(true) as $_comment)
                {
                    if($_comment->getComment())
                    {
                        $cust_comment = $_comment->getComment();
                    }
                }

                foreach ($_order->getStatusHistoryCollection(true) as $_comment)
                {
                    if($_comment->getComment())
                    {
                        $order_comment = $_comment->getComment();
                        break;
                    }
                }
			
                $Order->setNotes(isset($order_comment)?$order_comment:"");
                $giftMessage['message'] = isset($giftMessage['message'])?$giftMessage['message']:"";
                $Order->setComment($cust_comment);
				
		$Order->setFax('');
				
                /***************************************************************************************************
                Custamization for XPU-623-53661 Start: We create a config variable to manage this.
                ****************************************************************************************************/
                if($set_field_Q_CIM_and_Q_Authorization)
                {
                    $po_number_str=$payment['po_number'];
                    $po_number=explode("-",$po_number_str);
                    if(!empty($po_number['0']))
                    {
                        $q_cim=$po_number['0'];
                    }
				
                    if($q_cim!="" || $payment['last_trans_id']!="")
                    {
                        // code for custom fields  
                        $WG_OtherInfo = new WG_OtherInfo();
                        $WG_Other = new WG_Other();
                        $other_field= array('Q_CIM'=>$q_cim);
                        foreach($other_field as $key=>$value)
                        {
                            $WG_OtherInfo->setFieldName($key);
                            $WG_OtherInfo->setFieldValue(html_entity_decode($value));
                            $WG_Other->setCustomFeilds($WG_OtherInfo->getOtherinfo());
                        }		
                        $Order->setOrderOtherInfo($WG_Other->getOther());	
                    }	
                }
                /***************************************************************************************************
                Custamization for XPU-623-53661 Ends.
                ****************************************************************************************************/
				
                $item_array = $this->getorderitems($orders["entity_id"],$orders["increment_id"],$download_option_as_item);

                $item_array = $item_array['items'];
                $onlineInfo = array();
				
                if($do_not_download_configurable_product_as_line_item==true && $download_option_as_item==true)
                {
                    unset($orderConfigItems);
                    $orderConfigItems = array();
                }

                if($do_not_download_bundle_product_as_line_item==true && $download_option_as_item==true)
                {
                    unset($orderBundalItems);
                    $orderBundalItems = array();
                }

                $itemI = 0;
                foreach($item_array as $iInfo)
                {
						
                    if(is_object($iInfo['product']))
                    $onlineInfo =  $iInfo['product']->toArray();

                    if(intval($iInfo["qty_ordered"])>0 && is_numeric($iInfo["price"]))
                    {
                        unset($productoptions);
                        $productoptions = array();

                        if(isset($iInfo['product_options']))
                            $productoptions = unserialize($iInfo['product_options']);

                        if(isset($productoptions['options']) && is_array($productoptions['options']))
                        {
                            if($productoptions['options'])
                            {
                                if(is_array($productoptions['options']) && !empty($productoptions['options']))
                                {
                                    if(is_array($productoptions['attributes_info']))
                                    {
                                        $productoptions['attributes_info']     =    array_merge($productoptions['attributes_info'],$productoptions['options']);
                                    }else{
                                        $productoptions['attributes_info']     =    $productoptions['options'];
                                    }
                                }
                                unset($productoptions['options']);
                            }
                        }
                        
                        if(!empty($productoptions['bundle_options']) && is_array($productoptions['bundle_options']))
                        {

                            if(array_key_exists('attributes_info', $productoptions))
                            {
                                $productoptions['attributes_info'] = array_merge($productoptions['attributes_info'],$productoptions['bundle_options']);

                            }else{
                                $productoptions['attributes_info'] = $productoptions['bundle_options'];
                            }							
                            unset($productoptions['bundle_options']);
                        }						
                        if(isset($iInfo['product']))
                        {
                            $product = $iInfo;
                            $product['type_id'] = $iInfo['product_type'];
                            $product_base = $iInfo['product']->toArray();
                            $product['tax_class_id'] = $product_base['tax_class_id'];
							
							if($do_not_download_configurable_product_as_line_item==true && $download_option_as_item==true)
							{
								if(in_array($iInfo['parent_item_id'],$orderConfigItems))		
								{
								$product['sku'] = $product_base['sku'];
								}
							}
							
							#die($product_base['sku']);
							/* $currentProduct2 = $objectManager->get('Magento\Catalog\Model\Product')->load($iInfo['product_id']);
                            $product_base2 = $currentProduct2->toArray(); */
                        }else{
                            $product = $iInfo;
                            $product['type_id'] = $iInfo['product_type'];
                            $currentProduct = $objectManager->get('Magento\Catalog\Model\Product')->load($iInfo['product_id']);
                            $product_base = $currentProduct->toArray();
                            $product['tax_class_id'] = $product_base['tax_class_id'];
                            $productoptions['simple_sku'] = $iInfo['sku'];
                        }
						
                        if($do_not_download_configurable_product_as_line_item==true && $download_option_as_item==true)
                        {
                            if(in_array($iInfo['parent_item_id'],$orderConfigItems))		
                            {
                                continue;
                            }
                        }
						
                        if($do_not_download_bundle_product_as_line_item==true && $download_option_as_item==true)
                        {
                            if(in_array($iInfo['parent_item_id'],$orderBundalItems))		
                            {
                                continue;
                            }
                        }

                        if($product['type_id']=='bundle')
                        {
                            if($download_option_as_item  == true && $iInfo['product']->getPriceType()==0)
                            {
                                $iInfo["qty_ordered"] =0;
                                continue;
                            }
                        }

                        $Item = new Item();						
                        if($product['type_id']!='configurable')
                        {
                            if($do_not_download_bundle_product_as_line_item==true && $download_option_as_item==true)
                            {
                                $orderBundalItems[] = $iInfo['item_id'];
                            }
                            $Item->setItemCode($product['sku']);
                        }else{

                            if($do_not_download_configurable_product_as_line_item==true && $download_option_as_item==true)
                            {
                                $orderConfigItems[] = $iInfo['item_id'];
                            }
                            #$Item->setItemCode($productoptions['simple_sku']);
							$Item->setItemCode($product['sku']);
                        } 
						
                        $Item->setItemDescription($product['name']);

                        if($set_Short_Description)
                        {
                            $Item->setItemShortDescr(empty($onlineInfo['short_description'])?substr($product['short_description'],0,2000):substr($onlineInfo['short_description'],0,2000));
                        }else{
                            $Item->setItemShortDescr(empty($onlineInfo['description'])?substr($product['description'],0,2000):substr($onlineInfo['description'],0,2000));
                        }
                                                
                       /*  $attributeInfo = $objectManager->get('\Magento\EAV\Model\Entity\Attribute')->getResourceCollection();
                        $attributeInfo = $attributeInfo->setCodeFilter('ecc')->getFirstItem();
                        $attributeInfo = $attributeInfo->getData();	

                        if(isset($attributeInfo) && !empty($attributeInfo)){
                            $attributeValue = $objectManager->get('\Magento\Catalog\Model\Product')
                                                ->load($iInfo["product_id"])->getAttributeText('ecc');
                        }

                        if(isset($attributeValue) && $attributeValue=='Yes' && $iInfo["weight"]>0 )
                        {
                            $iInfo["qty_ordered"] = $iInfo["qty_ordered"]*$iInfo["weight"];
                            $iInfo["price"] = $iInfo["price"]/$iInfo["qty_ordered"];
                            $iInfo["weight"] = $iInfo["weight"]/$iInfo["qty_ordered"];
                        } */

                        $Item->setItemID($iInfo['item_id']);
                        $Item->setQuantity($iInfo["qty_ordered"]);
                        $Item->setShippedQuantity($iInfo["qty_shipped"]);
                        $Item->setUnitPrice($iInfo["price"]);
                        $Item->setCostPrice($onlineInfo["cost"]);
                        $Item->setWeight($iInfo["weight"]);
                        $Item->setFreeShipping("N");
                        $Item->setDiscounted("N");
                        $Item->setshippingFreight("0.00");
                        $Item->setWeight_Symbol("lbs");
                        $Item->setWeight_Symbol_Grams("453.6");

                        if($product['tax_class_id']<=0 || $product['tax_class_id']="")
                        {
                            $Item->setTaxExempt("Y");
                        }else{
                            $Item->setTaxExempt("N");
                        }

                        $iInfo['onetime_charges']="0.00";
                        $Item->setOneTimeCharge(number_format($iInfo['onetime_charges'],2,'.',''));
                        $Item->setItemTaxAmount("");
                        //$responseArray['ItemOptions'] = array();
                        
                        if(array_key_exists("attributes_info",$productoptions))
                        {
                            $optionI = 0;
                            foreach($productoptions['attributes_info'] as $item_option12)
                            {
                                $Itemoption = new Itemoption();
                                if(is_array($item_option12['value']))
                                {
                                    $item_option1234='';
                                    foreach($item_option12['value'] as $item_option123)
                                    {

                                        $item_option1234 = " ".$item_option123['qty']." x ".$item_option123['title']." $".$item_option123['price'];
                                        $Itemoption->setOptionValue($item_option1234);
                                        $Itemoption->setOptionName($item_option12['label']);
                                        $Itemoption->setOptionPrice($item_option123['price']);
                                        $Item->setItemOptions($Itemoption->getItemoption());                                                                                                                        }
                                    unset($item_option1234);
                                }
                                else{
                                    $Itemoption->setOptionValue($item_option12['value']);
                                    $Itemoption->setOptionName($item_option12['label']);
                                    $Item->setItemOptions($Itemoption->getItemoption());
                                }
                                $optionI++;
                            }
                        }
                        
                        #custamization for client date: 08 may 2012
                        if(isset($iInfo['nonreturnable']) && $iInfo['nonreturnable']=="Yes")
                        {
                            $Itemoption = new Itemoption();
                            $Itemoption->setOptionValue("Non-returnable");
                            $Itemoption->setOptionName("Clearance");
                            $Item->setItemOptions($Itemoption->getItemoption());
                        }
                    }

                    $itemI++;
                    $Order->setOrderItems($Item->getItem());
                }


                $discountadd =true;
                #Discount Coupon as line item
                $orders["discount_amount"] = $orders["discount_amount"]?$orders["discount_amount"]:$orders["base_discount_amount"];

                if(($orders['coupon_code']!='' || $orders['discount_description']!='') && $discount_as_line_item==true)
                {
                    $discountadd =false;
                    $orders["discount_amount"] = $orders["discount_amount"]?$orders["discount_amount"]:$orders["base_discount_amount"];

                    if($display_discount_desc){ 
                        $DESCR1 = $orders['discount_description'];  
                    }
                    else{ 
                        $DESCR1 = $orders['coupon_code']; 
                    }
                    //$DESCR1 = $orders['coupon_code']?$orders['coupon_code']:$orders['discount_description'];
                    $itemI++;					
                    $Item = new Item();
                    $Item->setItemCode("Discount Coupon");
                    $Item->setItemDescription(substr($DESCR1,0,50));
                    $Item->setItemShortDescr("Coupon code ".htmlentities(substr($DESCR1,0,50),ENT_QUOTES));
                    $Item->setQuantity(intval(1));
                    $discount_amount=$orders["discount_amount"];
                    if($discount_amount< 0)
                    {			
                        $Item->setUnitPrice($orders["discount_amount"]);
                    }else{
                        $Item->setUnitPrice("-".$orders["discount_amount"]);
                    }
                    $Item->setWeight('');
                    $Item->setFreeShipping("N");					
                    $Item->setshippingFreight("0.00");
                    $Item->setWeight_Symbol("lbs");
                    $Item->setWeight_Symbol_Grams("453.6");
                    $Item->setDiscounted("Y");
                    $Order->setOrderItems($Item->getItem());
                }
                
                #Reward Points as line item
                if(isset($orders["reward_points_balance"]))
                {
                    $itemI++;					
                    $Item = new Item();
                    $Item->setItemCode($RewardsPoints_Name);
                    $Item->setItemDescription($orders["reward_points_balance"].'reward points');
                    $Item->setItemShortDescr($orders["reward_points_balance"].'reward points');
                    $Item->setQuantity(intval(1));
                    $Item->setUnitPrice("-".$orders["base_reward_currency_amount"]);
                    $Item->setWeight('');
                    $Item->setFreeShipping("N");					
                    $Item->setshippingFreight("0.00");
                    $Item->setWeight_Symbol("lbs");
                    $Item->setWeight_Symbol_Grams("453.6");
                    $Item->setDiscounted("Y");
                    $Order->setOrderItems($Item->getItem());
                }
				
                if(isset($orders["customer_credit_amount"]) && $orders["customer_credit_amount"]>0)
                {
                    $itemI++;					
                    $Item = new Item();
                    $Item->setItemCode("InternalCredit");
                    $Item->setItemDescription('Internal Credit');
                    $Item->setItemShortDescr('Internal Credit');
                    $Item->setQuantity(intval(1));
                    $Item->setUnitPrice("-".$orders["customer_credit_amount"]);
                    $Item->setWeight('');
                    $Item->setFreeShipping("N");					
                    $Item->setshippingFreight("0.00");
                    $Item->setWeight_Symbol("lbs");
                    $Item->setWeight_Symbol_Grams("453.6");
                    $Item->setDiscounted("Y");
                    $Order->setOrderItems($Item->getItem());
                }


                if(isset($orders["gift_cards"]))
                {
                    $gift_cards = unserialize($orders["gift_cards"]);
                    foreach($gift_cards as $gift_card)
                    {
                        $itemI++;
                        $Item = new Item();
                        $Item->setItemCode("GiftCard");
                        $Item->setItemDescription(substr("GiftCard #.".$gift_card['c'],0,50));
                        $Item->setItemShortDescr(substr($gift_card['c'],0,50));
                        $Item->setQuantity(intval(1));
                        $Item->setUnitPrice("-".$gift_card['a']);
                        $Item->setWeight('');
                        $Item->setFreeShipping("N");					
                        $Item->setshippingFreight("0.00");
                        $Item->setWeight_Symbol("lbs");
                        $Item->setWeight_Symbol_Grams("453.6");
                        $Item->setDiscounted("Y");
                        $Order->setOrderItems($Item->getItem());
                    }
                }
                
                if(isset($orders["giftcert_code"]))
                {
                    $Item = new Item();
                    $Item->setItemCode("Gift Certificate" );
                    $Item->setItemDescription($orders["giftcert_code"]);
                    $Item->setItemShortDescr("Gift Certificate");
                    $Item->setQuantity(intval(1));
                    $Item->setUnitPrice("-".$orders['giftcert_amount']);
                    $Item->setWeight('');
                    $Item->setFreeShipping("N");
                    $Item->setshippingFreight("0.00");
                    $Item->setWeight_Symbol("lbs");
                    $Item->setWeight_Symbol_Grams("453.6");
                    $Item->setDiscounted("Y");
                    $Order->setOrderItems($Item->getItem());
                }
				
				
                if(isset($orders["gw_price"]) && $orders["gw_price"]!="0.0" && $orders["gw_price"]>"0.0")
                {
                    $Item = new Item();
                    $Item->setItemCode("Gift Wrapping for Order");
                    $Item->setItemDescription("Gift Wrapping for Order");
                    $Item->setItemShortDescr("Gift Wrapping for Order");
                    $Item->setQuantity(intval(1));
                    $Item->setUnitPrice($orders['gw_price']);
                    $Item->setWeight('');
                    $Item->setFreeShipping("N");
                    $Item->setshippingFreight("0.00");
                    $Item->setWeight_Symbol("lbs");
                    $Item->setWeight_Symbol_Grams("453.6");
                    $Item->setDiscounted("Y");
                    $Order->setOrderItems($Item->getItem());
                }
				
                if(isset($orders["gw_items_price"]) && $orders["gw_items_price"]!="0.0" && $orders["gw_items_price"]>"0.0")
                {
                    $Item = new Item();
                    $Item->setItemCode("Gift Wrapping for Items");
                    $Item->setItemDescription("Gift Wrapping for Items");
                    $Item->setItemShortDescr("Gift Wrapping for Items");
                    $Item->setQuantity(intval(1));
                    $Item->setUnitPrice($orders['gw_items_price']);
                    $Item->setWeight('');
                    $Item->setFreeShipping("N");
                    $Item->setshippingFreight("0.00");
                    $Item->setWeight_Symbol("lbs");
                    $Item->setWeight_Symbol_Grams("453.6");
                    $Item->setDiscounted("Y");
                    $Order->setOrderItems($Item->getItem());
                }
				
		##FETCH BILLING INFORMATION
                $Bill = new Bill();
                $CreditCard = new CreditCard();
                $PayStatus = "Cleared";
                if ($payment['cc_type']!="")
                {
                    if($ccdetails!=='DONOTSEND')
                    {				
                        $CreditCard->setCreditCardType($this->getCcTypeName($payment['cc_type']));
                        if (isset($payment['amount_paid']))
                        {
                            $CreditCard->setCreditCardCharge($payment['amount_paid']);
                        }else{
                            $CreditCard->setCreditCardCharge('0.00');
                        }

                        if (isset($payment['cc_exp_month']) && isset($payment['cc_exp_year'])){
                            $CreditCard->setExpirationDate(sprintf('%02d',$payment['cc_exp_month']).substr($payment['cc_exp_year'],-2,2));
                        }else{
                            $CreditCard->setExpirationDate("");
                        }

                        $CreditCardName = $payment['cc_owner']?($payment['cc_owner']):"";					
                        $CreditCard->setCreditCardName($CreditCardName);
                        $encryptor = $objectManager->get('\Magento\Framework\Encryption\EncryptorInterface');

                        $payment['cc_number_enc'] = $encryptor->decrypt($payment['cc_number_enc']);
                        $CreditCardNumber = $payment['cc_number_enc']?$payment['cc_number_enc']:$payment['cc_last4'];					
                        $CreditCard->setCreditCardNumber(utf8_encode($CreditCardNumber));

                        if(!empty($orders['quote_id']))
                        {
                            $getQuote = $objectManager->get('\Magento\Quote\Model\Quote\Payment')->getCollection()->setQuoteFilter($orders['quote_id']);
                            $getQuote_val=$getQuote->toArray();
                            $cc_cid = $encryptor->decrypt($getQuote_val['items']['0']['cc_cid_enc']);   
                            $CreditCard->setCVV2($cc_cid);
                        }
                        else
                        {
                            $CreditCard->setCVV2('');
                        }

                        $CreditCard->setAdvanceInfo('');										
                        $transcationId ="";
                        $transcationId = (isset($payment['cc_trans_id'])?($payment['cc_trans_id']):"");
                        $transcationId  = $transcationId ? $transcationId : $payment['last_trans_id'];
                    }					
                    $CreditCard->setTransactionId($transcationId);
                    $CreditCard->getCreditCard();					
                    $Bill->setCreditCardInfo($CreditCard->getCreditCard());					
                }
                else
                {
                    $transcationId ="";
                    //$additional_information_authorize_cards = $payment['additional_information']['authorize_cards'];
                    $additional_information_authorize_cards = $payment['additional_information'];
                    
                    /**PENDING //COMMENTED FOR TEMPRORY, SINCE NOT ABLE TO CHECK WITH CREDIT CARD
                     * if(is_array($additional_information_authorize_cards))
                    foreach($additional_information_authorize_cards as $key =>$value)
                    {
                        $payment['last_trans_id'] = $value['last_trans_id'];
                        $payment['cc_type']= $value['cc_type'];
                        $payment['cc_exp_month'] = $value['cc_exp_month'];
                        $payment['cc_exp_year'] = $value['cc_exp_year'];
                        $payment['cc_last4'] = $value['cc_last4'];
                    }
                     */
                     
                    
                    if($ccdetails!=='DONOTSEND')
                    {			
                        $CreditCard->setCreditCardType($this->getCcTypeName($payment['cc_type']));
                        $CreditCard->setCreditCardCharge($payment['amount_paid']);
                        $CreditCard->setExpirationDate(sprintf('%02d',$payment['cc_exp_month']).substr($payment['cc_exp_year'],-2,2));
                        //$CreditCard->setCreditCardName($CreditCardName);
                        $CreditCard->setCreditCardName($payment['cc_owner']);
                        $CreditCardNumber = $payment['cc_number_enc']?$payment['cc_number_enc']:$payment['cc_last_4'];	
                        $CreditCard->setCreditCardNumber(utf8_encode($CreditCardNumber));
                        if(!empty($orders['quote_id']))
                        {
                            $getQuote= $objectManager->get('\Magento\Quote\Model\Quote\Payment')->getCollection()->setQuoteFilter($orders['quote_id']);
                            $getQuote_val=$getQuote->toArray();
                            $encryptor = $objectManager->get('\Magento\Framework\Encryption\EncryptorInterface');
                            $cc_cid = $encryptor->decrypt($getQuote_val['items']['0']['cc_cid_enc']);   
                            $CreditCard->setCVV2($cc_cid);
                        }
                        else
                        {
                            $CreditCard->setCVV2('');
                        }
                        $CreditCard->setAdvanceInfo('');	
                    }				

                    $transcationId  = $transcationId ? $transcationId : $payment['last_trans_id'];
                    $CreditCard->setTransactionId($transcationId);
                    $CreditCard->getCreditCard();                   
                    $Bill->setCreditCardInfo($CreditCard->getCreditCard());       
                }

                if (isset($payment['amount_ordered'])&&isset($payment['amount_paid']))
                {
                    if (($payment['amount_paid']==$payment['amount_ordered']))
                        $PayStatus = "Pending";
                }

                # for version 1.4.1.0
                //$Bill->setPayMethod($this->getPaymentlabel($payment['method']));
                $Bill->setPayMethod($payment['additional_information']['method_title']);
                $Bill->setTitle($orders["billing_prefix"]);
                $Bill->setFirstName($orders["billing_firstname"]);
                $Bill->setLastName($orders["billing_lastname"]);

                if (!empty($orders["billing_company"]))
                {
                    $Bill->setCompanyName($orders["billing_company"]);				
                }else{
                    $Bill->setCompanyName("");				
                }
				
					$region = $objectManager->get('Magento\Directory\Model\Region')->load($orders['billing_region_id']);
				$state_code = $region->getCode(); //12
				
				if($donwload_state_code=='True') {
				
					$Bill->setState($state_code);
				}else {
					$Bill->setState($orders["billing_region"]);
				}

                $orders["billing_street"] = explode("\n",$orders["billing_street"]);
                $Bill->setAddress1($orders["billing_street"][0]);				
                $Bill->setAddress2(isset($orders["billing_street"][1])?$orders["billing_street"][1]:"");				
                $Bill->setCity($orders["billing_city"]);				
             			
                $Bill->setZip($orders["billing_postcode"]);				
                $Bill->setCountry(trim($country[$orders["billing_country"]]));				
                $Bill->setEmail($orders["customer_email"]);				
                $Bill->setPhone($orders["billing_telephone"]);				
                $Bill->setPONumber($payment['po_number']);					

                $customer = $objectManager->get('\Magento\Customer\Model\Customer')->load($orders["customer_id"]);
                $customerGroupId = $customer->getGroupId();
                $group = $objectManager->get('\Magento\Customer\Model\Group')->load($customerGroupId);
                $group_nam=$group->getCode();

                $Bill->setGroupName($group_nam);

                $Order->setOrderBillInfo($Bill->getBill());
				
                ##GET CREDIT CARD INFO
                $Ship =new Ship();
		
                ##CASE 1 FOR SHIPPING INFO
                $shippingDesc = $_order->getShippingDescription();
                $ship_career = explode("-",$shippingDesc,2);
                $ShipMethod = $ship_career[1];
                $Carrier = $carrier_code = $ship_career[0];
                $shipTrack1 = "";
                
                ##CASE 2 FOR SHIPPING INFO
                $shipmentCollection = $_order->getShipmentsCollection();
                foreach ($shipmentCollection as $shipment){
                    foreach($shipment->getAllTracks() as $ship_data)
                    {
                        $Req_ship_detail_arry=$ship_data->toArray();
                        $ShipMethod=$Req_ship_detail_arry['title'];
                        $carrier_code=$Req_ship_detail_arry['carrier_code'];
                        $shipTrack1=$Req_ship_detail_arry['track_number'];
                    } 
                }
                
                if($get_Active_Carriers)
                {
                    $carrierInstances = $objectManager->get('\Magento\Shipping\Model\Config')->getActiveCarriers($storeid);
                }else{
                    $carrierInstances = $objectManager->get('\Magento\Shipping\Model\Config')->getAllCarriers($storeid);
                }
		
                $carriers['custom'] = (string)__("Custom Value");//Mage::helper('sales')->__('Custom Value');//PENDING
                foreach ($carrierInstances as $code => $carrier) {
                    if ($carrier->isTrackingAvailable()) {
                        $carriers[$code] = $carrier->getConfigData('title');
                    }
                }
                $c_code='';	
                //print_r($carriers);echo $carrier_code;
                foreach($carriers as $c_key => $c_val)
                {
                    if($carrier_code == $c_key)
                    {
                        $Carrier=$c_val;
                        break;
                    }
                }
                                
                unset($carrier_code);
                $Carrier=strtolower($Carrier);
                $ship_career = explode("-",$orders["shipping_description"],2);
                $Ship->setShipMethod(empty($ShipMethod)?$ship_career[1]:$ShipMethod);
                $Ship->setCarrier(empty($Carrier)?$ship_career[0]:$Carrier);
                $Ship->setTrackingNumber(!empty($shipTrack1)?$shipTrack1:'');
                #End
				
                unset($shipTrack);
                $Ship->setTitle("");

                if(!array_key_exists('shipping_firstname',$orders) && !array_key_exists('shipping_lastname',$orders) )
                {
                    $shippingAddressArray = $_order->getShippingAddress();
                    if(is_array($shippingAddressArray))
                    $shippingAddressArray = $shippingAddressArray->toArray();
                    $orders["shipping_firstname"]=$shippingAddressArray["firstname"];
                    $orders["shipping_lastname"]=$shippingAddressArray["lastname"];
                    $orders["shipping_company"]=$shippingAddressArray["company"];
                    $orders["shipping_street"]=$shippingAddressArray["street"];
                    $orders["shipping_city"]=$shippingAddressArray["city"];
                    $orders["shipping_region"]=$shippingAddressArray["region"];
					$orders["shipping_region_id"]=$shippingAddressArray["region_id"];
                    $orders["shipping_postcode"]=$shippingAddressArray["postcode"];
                    $orders["shipping_country"]=$shippingAddressArray["country_id"];
                    $orders["customer_email"]=$shippingAddressArray["customer_email"]?$shippingAddressArray["customer_email"]:$orders["customer_email"];
                    $orders["shipping_telephone"]=$shippingAddressArray["telephone"];
                }
				
                $Ship->setFirstName($orders["shipping_firstname"]);
                $Ship->setLastName($orders["shipping_lastname"]);
                if (!empty($orders["shipping_company"]))
                {
                    $Ship->setCompanyName($orders["shipping_company"]);
                }else{
                    $Ship->setCompanyName("");
                }
				
                $orders["shipping_street"] = explode("\n",$orders["shipping_street"]);
				
				$region_shipping = $objectManager->get('Magento\Directory\Model\Region')->load($orders['shipping_region_id']);
				$state_code = $region_shipping->getCode(); //12
				
				if($donwload_state_code=='True') {
				
					$Ship->setState($state_code);
				}else {
					$Ship->setState($orders["shipping_region"]);
				}
				

                $Ship->setAddress1($orders["shipping_street"][0]);
                $Ship->setAddress2(isset($orders["shipping_street"][1])?$orders["shipping_street"][1]:"");
                $Ship->setCity($orders["shipping_city"]);
            
                $Ship->setZip($orders["shipping_postcode"]);
                $Ship->setCountry(trim($country[$orders["shipping_country"]]));
                $Ship->setEmail($orders["customer_email"]);
                $Ship->setPhone($orders["shipping_telephone"]);

                $Order->setOrderShipInfo($Ship->getShip());

                $charges =new Charges();
                $charges->setDiscount($discountadd?abs($orders["discount_amount"]):'');
                //$charges->setStoreCredit($orders["customer_balance_amount"]);
                $charges->setStoreCredit(isset($orders["customer_balance_amount"])?$orders["customer_balance_amount"]:0.00);
                $charges->setTax($orders["tax_amount"]);
                $charges->setShipping($orders["shipping_amount"]);
                $charges->setTotal( $orders["grand_total"]);
                $charges->setSubTotal();
                $Order->setOrderChargeInfo($charges->getCharges());

                $Order->setShippedOn($shippedOn);

                $Order->setShippedVia(empty($Carrier)?$ship_career[0]:$Carrier);
                unset($Carrier,$shipTrack1,$ShipMethod);
                $Order->setSalesRep($setSalesRep);
                $Orders->setOrders($Order->getOrder());
                //$ord++;
            }

            return $this->response($Orders->getOrders());
        }
	
	public function _dateformat_wg($date)
	{
            global $objectManager;
            $localDate = $objectManager->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            //$localDate->date($orders["created_at"],  \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT);
            if(strtotime($date))
            {
                # Latest code modififed date for all country
                $fdate = date("m-d-Y H:i:s",strtotime($date));
            }else{
                #Code is custamize for this customer
                $dateObj= $localDate->date($date);
                $dateStrToTime=$dateObj->getTimestamp();
                $fdate = date("m-d-Y H:i:s",$dateStrToTime);
            }
            return $fdate;
	}
	
	public  function _GetOrders($datefrom,$start_order_no=0,$order_status_list='',$storeId=1,$no_of_orders=20,$by_updated_date='',$orderlist,$LastModifiedDate)
	{
            global $objectManager;
            if(strtolower($order_status_list)=='all' || strtolower($order_status_list)=="'all'")
            {
                $order_status = array();
                $orderStatusData = $this->_getorderstatuses($storeId);
                foreach ($orderStatusData as $sk=>$sv)
                {
                    $order_status[]= $sk;
                }
            }else{
                $order_status_list = str_replace("'","",$order_status_list);
                $order_status_list = explode(",",$order_status_list);
                $order_status = $this->_orderStatustofetch($order_status_list,$storeId);
            }
			
            if($LastModifiedDate)
            {
                $datefrom2 = explode(" ",$LastModifiedDate);
                $datetime1 = explode("-",$datefrom2[0]);			
                $LastModifiedDate = $datetime1[2]."-".$datetime1[0]."-".$datetime1[1];			
                $LastModifiedDate .=" ".$datefrom2[1]; 

            }else
            {
                $datetime1 = explode("-",$datefrom);			
                $datefrom = $datetime1[2]."-".$datetime1[0]."-".$datetime1[1];			
                $datefrom .=" 00:00:00"; 
            }
            
            $orderFactory = $objectManager->get('\Magento\Sales\Model\Order');
            $orderCollection = $orderFactory->getResourceCollection();
                      
            $orderObject =  $orderCollection->addAttributeToSelect('*');/*
                            ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
                            ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
                            ->joinAttribute('billing_street', 'order_address/street', 'billing_address_id', null, 'left')
                            ->joinAttribute('billing_company', 'order_address/company', 'billing_address_id', null, 'left')
                            ->joinAttribute('billing_city', 'order_address/city', 'billing_address_id', null, 'left')
                            ->joinAttribute('billing_region', 'order_address/region', 'billing_address_id', null, 'left')
                            ->joinAttribute('billing_country', 'order_address/country_id', 'billing_address_id', null, 'left')
                            ->joinAttribute('billing_postcode', 'order_address/postcode', 'billing_address_id', null, 'left')
                            ->joinAttribute('billing_telephone', 'order_address/telephone', 'billing_address_id', null, 'left')
                            ->joinAttribute('billing_fax', 'order_address/fax', 'billing_address_id', null, 'left')
                            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                            ->joinAttribute('shipping_street', 'order_address/street', 'shipping_address_id', null, 'left')
                            ->joinAttribute('shipping_company', 'order_address/company', 'shipping_address_id', null, 'left')
                            ->joinAttribute('shipping_city', 'order_address/city', 'shipping_address_id', null, 'left')
                            ->joinAttribute('shipping_region', 'order_address/region', 'shipping_address_id', null, 'left')
                            ->joinAttribute('shipping_country', 'order_address/country_id', 'shipping_address_id', null, 'left')
                            ->joinAttribute('shipping_postcode', 'order_address/postcode', 'shipping_address_id', null, 'left')
                            ->joinAttribute('shipping_telephone', 'order_address/telephone', 'shipping_address_id', null, 'left')
                            ->joinAttribute('shipping_fax', 'order_address/fax', 'shipping_address_id', null, 'left');
            */
            if(!$orderlist && $LastModifiedDate)
            {
		$orderObject->addAttributeToFilter('updated_at', array('gt' => $LastModifiedDate,'datetime' => true))
                            ->addAttributeToFilter('store_id', $storeId)
                            //->addAttributeToFilter('entity_id', array('gt' => $start_order_no))
                            ->addAttributeToFilter('status', array('in' => $order_status))
                            ->addAttributeToSort('updated_at', 'asc')
                            ->setPageSize($no_of_orders);
            }elseif(!$orderlist && $datefrom)
            {
                $orderObject->addAttributeToFilter('created_at', array('from' => $datefrom,'datetime' => true))
                            ->addAttributeToFilter('store_id', $storeId)
                            ->addAttributeToFilter('entity_id', array('gt' => $start_order_no))
                            ->addAttributeToFilter('status', array('in' => $order_status))
                            ->addAttributeToSort('entity_id', 'asc')
                            ->setPageSize($no_of_orders);
            }else
            {
                $orderObject->addAttributeToFilter('store_id', $storeId)
                            ->addAttributeToFilter('increment_id', array('in' => $orderlist))		
                            ->addAttributeToSort('entity_id', 'asc');
            }	
            
            $this->_orders = $orderObject->load();
            return $this->_orders;
	}
        
        ##NO MORE IN USE @ 11-FAB-2016
	public function getPaymentlabel($paymethod='')
        {
            global $objectManager;
            $orderFactory = $objectManager->get('\Magento\Sales\Model\Order');
            $method = "";
            foreach ($this->_getPaymentMethods() as $paymentCode=>$paymentModel)
            {
                //$paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
                $paymentTitle = "paypal";
                if($paymentCode==$paymethod)
                {
                    return $paymentTitle;
                    break;
                }
            }
                    return $method;
        }

	public function _orderStatustofetch($order_status_list,$storeId)
	{
            $orderStatus = $this->_getorderstatuses($storeId);
            $order_status = array();
            foreach ($orderStatus as $sk=>$sv)
            {
                if(in_array(trim($sv),$order_status_list))
                {
                    $order_status[] =$sk;
                }
            }
            return $order_status;
	}

        function getDefaultStore($storeId)
	{
            global $objectManager;
            $storeManagerObj = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $storeModel = $objectManager->get('\Magento\Store\Model\Store');
            if(isset($storeId) && $storeId!="")
            {
                $stores = $storeModel->getResourceCollection()
                                    ->setLoadDefault(true)
                                    ->addIdFilter($storeId)
                                    ->load();
                $stores = $stores->toArray();
                $store_Id = $stores['items'][0]['store_id'];
                return $store_Id;
            }
            if(!defined("__STORE_ID"))
            {
                $name = $storeManagerObj->getDefaultStoreView();
                $name = $name->toArray();
                define("__STORE_ID",$name['store_id']);
                return $name['store_id'];

            }elseif(__STORE_ID!=''){
                return __STORE_ID;
            }else{
                return 1;
            }
            
            $storeObj = $storeManagerObj->getStore(true);
            $defaultStoreId = $storeObj->getId();
            if(isset($defaultStoreId) && $defaultStoreId >=1)
                return $defaultStoreId;
            else
                return 1;
	}

	public function getduplicaterecord($productname,$productcode)
	{
            global $objectManager;
            $productsCollection = $objectManager->get('Magento\Catalog\Model\Product')->getCollection()
                                                                    ->addAttributeToSelect('*')
                                                                    ->addAttributeToFilter('sku', $productcode)
                                                                    ->load();

            $productsCollection = $productsCollection->toArray();

            if(count($productsCollection)>0)
            {
                return "1";
            }else{
                return "0";
            }
	}
	public function getorderitems($Id,$incrementID,$download_option_as_item = false)
	{
            global $objectManager;
            $orderItem = $objectManager->get('\Magento\Sales\Model\Order\Item');
            $productObj = $objectManager->get('\Magento\Catalog\Model\Product');
            #global $download_option_as_item;
            #config option
            #$download_option_as_item =false;
            if($download_option_as_item==true)
            {
                    $collection = $orderItem->getResourceCollection()
                    ->setOrderFilter($Id)
                    ->setOrder('item_id','asc');
            }else{
                    $collection = $orderItem->getResourceCollection()
                    ->setOrderFilter($Id)
                    ->addFieldToFilter('parent_item_id', array('null' => true))
                    ->setOrder('item_id','asc');
            }
            $products = array();
            foreach ($collection as $item)
            {
                            $products[] = $item->getProductId();
                            #$products[] = $item->toArray();
            }
            #print_r($products);die('testing');
            $productsCollection = $productObj->getCollection()
                                    ->addAttributeToSelect('*')
                                    ->addIdFilter($products)
                                    ->load();
									
									#print_r($productsCollection);
            foreach ($collection as $item)
            {
                    $item->setProduct($productsCollection->getItemById($item->getProductId()));
            }
            $collection = $collection->toArray();
            $productsCollection = $productsCollection->toArray();
            return $collection;
	}
	
	public	function UpdateOrdersShippingStatus($username,$password,$Orders_json_array,$emailAlert='N',$statustype,$storeid=1,$others)
	{
		#ini_set("display_errors","ON");
		#error_reporting(E_ALL);
            global $objectManager,$set_capture_case;

            $storeId=$this->getDefaultStore($storeid);		
            
            $status = $this->CheckUser($username,$password);
            if($status!="0")
            {		
                return $status;
            }
            
            $Orders = new Orders();		
            $requestArray=$Orders_json_array;
            if (!is_array($requestArray))
            {
                $Orders->setStatusCode("9997");
                $Orders->setStatusMessage("Unknown request or request not in proper format");	
                return $this->response($Orders->getOrders());exit();				
            }
            
            if (count($requestArray) == 0)
            {
                $Orders->setStatusCode("9996");
                $Orders->setStatusMessage("REQUEST array(s) doesnt have correct input format");				
                return $this->response($Orders->getOrders());exit();
            }
            
            if(count($requestArray) == 0) {
                $no_orders = true;
            }else {
                $no_orders = false;
            }
            
            $Orders->setStatusCode($no_orders?"1000":"0");
            $Orders->setStatusMessage($no_orders?"No new orders.":"All Ok");	

            if ($no_orders)
            {
                return json_encode($response_array);
            }
            $i=0;
            
            foreach($requestArray as $k2=>$order)//request
            {
                if(isset($order['UpdateOrderNote']) && $update_note=="Y")
                {
                    $orders1 = $this->_UpdateOrdersShippingStatus($order['OrderID'],$storeId);
                    $orders_array=$orders1->toArray();

                    // Increment ID of the order to add the comment to
                    $orderIncrementID = $order['OrderID'];

                    // Get the order - we could also use the internal Magento order ID and the load() method here
                    $order_1 = $objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($orderIncrementID);

                    // Add the comment and save the order
                    $order_1->addStatusToHistory($order_1->getStatus(), $order['OrderNotes'], false);
                    $order_1->save();

                    $result = "Success";


                    $Order = new Order();
                    $Order->setOrderID($order['OrderID']);
                    $Order->setStatus($result);
                    //$Order->setLastModifiedDate($this->_dateformat_wg($current_order->updated_at));
                    //$Order->setOrderNotes($order['OrderNotes']);
                    //$Order->setOrderStatus($current_order->getState());	
                    $Orders->setOrders($Order->getOrder());
                }

                else
                {
                    $orderStatus = $this->_getorderstatuses($storeId);
                    $status = $order['OrderStatus'];

                    $emailAlert = $order['IsNotifyCustomer'];

                    $order_status_list = array(0=>$status);
                    $status_w = $this->_orderStatustofetch($order_status_list,$storeId);
                    $order['OrderStatus'] = $status_w[0];

                    $info = "\nOrder shipped ";
                    if ($order['ShippedOn']!="")
                        $info .= " on ". substr($order['ShippedOn'],0,10);

                    if ($order['ShippedVia']!="" || $order['ServiceUsed']!="" )
                        $info .= " via ".$order['ShippedVia']." ".$order['ServiceUsed'];

                    if ($order['TrackingNumber']!="")
                        $info .= " with Tracking Number ".$order['TrackingNumber'].".";

                    if ($order['OrderNotes']!="")
                        $info .=" \n".$order['OrderNotes'];

                    $orders1 = $this->_UpdateOrdersShippingStatus($order['OrderID'],$storeId);
                    $orders_array = $orders1->toArray();

                    //print_r($orders_array);die('test');
                    // Updated for 1.4.1.0
                    if(array_key_exists('items',$orders_array))
                        $orders_array_w = $orders_array['items'];
                    else
                        $orders_array_w = $orders_array;
                    
                    foreach($orders_array_w as $orders_el)
                    {
                        $orderObj = $objectManager->get('\Magento\Sales\Model\Order');
                        $current_order = $orderObj->load($orders_el['entity_id']);
                        if(isset($order['IsCreateCreditMemo']) && $order['IsCreateCreditMemo']==true && $current_order->canCreditMemo() && $current_order->hasInvoices())
			{
                            
                            $this->CreateCreditMemo($orders_el['entity_id'],$order['CancelItemDetail'],$storeId, true);
                            $result ="Error: Order cannot be cancelled. Please review manually. Credit memo successfully created.";
                        }
                        elseif(strtolower($order['OrderStatus'])=='canceled'  || strtolower($statustype) == strtolower('Cancel'))
                        { 
                            if($current_order->getState()== strtolower($order['OrderStatus']))
                            {
                                $result ="Success: Order is already Canceled";
                                $emailAlert = "N";
                            }
                            else
                            {
                                $result = $this->cancelAction($orders_el['entity_id']);										
                                $info ='';
                                if(trim($result)!="" && $result==1)
                                {
                                    $result ="";
                                }else
                                {
                                    $emailAlert = "N";
                                }
                                $current_order->setStatus($order['OrderStatus']);
                                //Mage::unregister('sales_order');
                                //Mage::unregister('current_order');
                            }
                        }
                        elseif(strtolower($order['OrderStatus'])=='holded')
			{
                            $result = $this->holdAction($orders_el['entity_id']);
                            if(trim($result)!="" && $result==1)
                            {
                                $result ="";
                            }else
                            {
                                $emailAlert = "N";
                            }
                            //Mage::unregister('sales_order');
                            //Mage::unregister('current_order');
			}
                        elseif(strtolower($order['OrderStatus'])=='unholded')
                        {
                            $result = $this->unholdAction($orders_el['entity_id']);
                            if(trim($result)!="" && $result==1)
                            {
                                $result ="";
                            }else
                            {
                                $emailAlert = "N";
                            }
                            //Mage::unregister('sales_order');
                            //Mage::unregister('current_order');

                        }
                        elseif (strtolower($order['OrderStatus']) == "complete")
			{
									
                            if($current_order->getState()== strtolower($order['OrderStatus']))
                            {
                                $result = "Success: Order has already been completed";
                                $emailAlert = "N";
                            }
                            elseif(strtolower($current_order->getState())=="processing" || strtolower($current_order->getState()) =="pending" || strtolower($current_order->getState()) =="new")
                            {

                                $current_order->setTotal_paid($orders_el['grand_total']);
                                $current_order->setBase_total_paid($orders_el['base_grand_total']);

                                $current_order->setTotal_invoiced($orders_el['grand_total']);
                                $current_order->setBase_total_invoiced($orders_el['base_grand_total']);

                                $current_order->setDiscount_invoiced($orders_el['discount_amount']);
                                $current_order->setBase_discount_invoiced($orders_el['base_discount_amount']);

                                $current_order->setSubtotal_invoiced($orders_el['subtotal']);
                                $current_order->setTax_invoiced($orders_el['tax_amount']);

                                $current_order->setShipping_invoiced($orders_el['shipping_amount']);
                                $current_order->setBase_subtotal_invoiced($orders_el['base_subtotal']);
                                $current_order->setBase_tax_invoiced($orders_el['base_tax_amount']);
                                $current_order->setBase_shipping_invoiced($orders_el['base_shipping_amount']);

                                foreach ($current_order->getAllItems() as $item_o)
                                {
                                    $item_o->setQtyInvoiced($item_o->getQtyToShip());
                                    $data['items'][$item_o->getId()] = $item_o->getQtyToShip();
                                }
                                $data['comment_text'] = $order['OrderNotes'];

                                #Enable these lines to send invoice notification  
                                /*
                                $data['send_email']=true;
                                $data['comment_customer_notify']=true;
                                */
                                if($set_capture_case)
                                {
                                    $data['capture_case']='offline';
                                }

                                $this->_saveInvoice($data,$orders_el['entity_id']);
                                
                                $RequestOrders = array("TRACKINGNUMBER"=>$order['TrackingNumber'],"SHIPPEDVIA"=>$order['ShippedVia'],"SERVICEUSED"=>$order['ServiceUsed']);
                                if($current_order->canShip())
                                {
                                    if($shipment = $this->_initShipment($current_order,$RequestOrders,$data))
                                    {												
                                        $shipment->register();
                                        #make second param true to notify customer.
                                        $shipment->addComment($info,true);
                                        $shipment_arr = $this->_saveShipment($shipment);
                                        //$shipment->sendEmail(true);
                                        //$shipment->setEmailSent(true);
										$emailSender2 = $objectManager->get('Magento\Sales\Model\Order\Email\Sender\ShipmentSender');
										$emailSender2->send($shipment, true);
                                    }
                                }	

                                if($current_order->getState()!=$order['OrderStatus'])
                                {
                                    $state = $order['OrderStatus'];
                                    $current_order->setData('state', $state);
                                    // add status history
                                    if($status) 
                                    {
                                        if ($status === true) {
                                            $status = $current_order->getConfig()->getStateDefaultStatus($state);
                                        }
                                        $current_order->setStatus($status);
                                    }
                                }

                                $invoiceNotifies = false;
                                if($emailAlert=='Y')										
                                    $invoiceNotified = true;
                                $current_order->addStatusToHistory($order['OrderStatus'], $info, $invoiceNotified);
                                $current_order->save();
                                $result = "Success: Order has been completed";
                                //Mage::unregister('sales_order');
                                //Mage::unregister('current_order');
                                //Mage::unregister('current_invoice');
                            }else
                            {
                                $result = "Error: Order cannot be completed. Please review manually";
                                $emailAlert = "N";
                            }
                        }else
                        {
                            $result = 'Error : Order cannot be '.$current_order->getState()." . Please review manually";
                            $emailAlert = "N";
                        }
								
                        if($emailAlert=='Y')
                        {
                            $_SERVER['SCRIPT_FILENAME'] = str_replace("webgility/webgility-magento","index",$_SERVER['SCRIPT_FILENAME']);
                            $_SERVER['REQUEST_URI'] = str_replace("webgility/webgility-magento.php","index",$_SERVER['REQUEST_URI']);
                            $_SERVER['PHP_SELF'] = str_replace("webgility/webgility-magento.php","index",$_SERVER['PHP_SELF']);
                            $_SERVER['SCRIPT_NAME'] = str_replace("webgility/webgility-magento.php","index",$_SERVER['SCRIPT_NAME']);
                            
                           /*  $orderObj = $objectManager->get('\Magento\Sales\Model\Order');
                            $current_order = $orderObj->load($orders_el['entity_id']);
                            #send the order update nitofication.
                            $emailSender = $objectManager->get('\Magento\Sales\Model\AdminOrder\EmailSender');
                            $emailSender->send($current_order); */
                            //$current_order->sendOrderUpdateEmail(true,$info);
							
							$orderObj = $objectManager->get('\Magento\Sales\Model\Order');
                            $current_order = $orderObj->loadByIncrementId($order['OrderID']);
                            #send the order update nitofication.
                            #$emailSender = $objectManager->get('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
                            $emailSender = $objectManager->get('\Magento\Sales\Model\AdminOrder\EmailSender');
                            $emailSender->send($current_order);
							
                            unset($info);
                        }
                    }
                    
                    $result = $result ? $result:'Success: Order has been '.ucfirst($order['OrderStatus']);
                    
                    $current_order = $objectManager->get('\Magento\Sales\Model\Order')->loadByIncrementId($order['OrderID']);
                    $currentOrderArray = $current_order->toArray();
                    $Order = new Order();
                    $Order->setOrderID($currentOrderArray['increment_id']);
                    $Order->setStatus($result);
                    $Order->setLastModifiedDate($this->_dateformat_wg($currentOrderArray['updated_at']));
                    $Order->setOrderNotes($info);
                    $Order->setOrderStatus($current_order->getState());	
                    $Orders->setOrders($Order->getOrder());
                }
            }
            return $this->response($Orders->getOrders());
	}
	
       
		public function CreateCreditMemo($orderId, $ccitemdetails,$storeId=1, $mailCreditMemo = false)
	{
            global $objectManager;
            
			$creditmemoFactory	= $objectManager->get('\Magento\Sales\Model\Order\CreditmemoFactory');
			
            $orderFactory = $objectManager->get('\Magento\Sales\Model\OrderFactory');
            $orderObj = $orderFactory->create()->load($orderId);
            
            if(!$orderObj->canCreditMemo())
            {
                return false;
            }

            if ($orderObj->hasInvoices())
            {
                $args['do_offline'] = 1;
                $args['comment_text'] = ""; 
                $args['shipping_amount'] = $orderObj->getBaseShippingAmount();
                $args['adjustment_positive'] = 0;
                $args['adjustment_negative'] = 0;
                    
                if($mailCreditMemo == true){
                    $args['send_email'] = 1;
                }
                    
                $creditmemoLoader = $objectManager->get('\Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader');
                $creditmemoLoader->setOrderId($orderId);
                $creditmemo = $creditmemoLoader->load();
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }

                if (!empty($args['comment_text'])) 
                {
                    $creditmemo->addComment(
                        $args['comment_text'],
                        isset($args['comment_customer_notify']),
                        isset($args['is_visible_on_front'])
                    );

                    $creditmemo->setCustomerNote($args['comment_text']);
                    $creditmemo->setCustomerNoteNotify(isset($args['comment_customer_notify']));
                }

                if (isset($args['do_offline']))
                {
                    //do not allow online refund for Refund to Store Credit
                    if (!$args['do_offline'] && !empty($args['refund_customerbalance_return_enable'])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Cannot create online refund for Refund to Store Credit.')
                        );
                    }
                }
				$items = $ccitemdetails;
			$ccqty1 =0;
			$totalQty = 0;
			$item_array ="";
			foreach ( $items as $item ) {
			
				//$item->getId();
				$totalQty += $item['QtyCancel'];
				$item_array[$item['ItemID']] = $item['QtyCancel'];
				if($item['QtyInOrder']!=$item['QtyCancel']) {
					$ccqty1 = 1 ;
				}
			}

		$data = array(
		    'qtys' => $item_array,
			"do_offline" => "1"        
		);
	
		if($ccqty1==1) {
		
		 $creditmemo = $creditmemoFactory->createByOrder($orderObj, $data);
		 	
		}	
                    
                $creditmemoManagement = $objectManager->create('Magento\Sales\Api\CreditmemoManagementInterface');
                $creditmemoManagement->refund($creditmemo, (bool)$args['do_offline'], !empty($args['send_email']));
                    
                if (!empty($args['send_email'])) {
                    $creditmemoSender = new wg_sender($objectManager);
                    //$creditmemoSender->wg_send($creditmemo);
                }
            }
			if($ccqty1==1) {
				$orderCreditMemoStatusComment = "Credit Memo Created";
                $orderObj->setData('state', $orderObj->getState(),true);
                $orderObj->setStatus($orderObj->getState());
                $orderObj->addStatusToHistory ( $orderObj->getState(), $orderCreditMemoStatusComment, false );
                $orderObj->save();
			
			}else {
            if(strtolower($orderObj->getState())=="processing")
            {
                $orderCreditMemoStatusComment = "Credit Memo Created";
                $orderObj->setData('state', "closed",true);
                $orderObj->setStatus("closed");
                $orderObj->addStatusToHistory ( "closed", $orderCreditMemoStatusComment, false );
                $orderObj->save();
            } 
			}
        }

        
	public  function _UpdateOrdersShippingStatus($orderId,$storeId=1)
	{
            global $objectManager;
            $orderCollectionObj = $objectManager->get('\Magento\Sales\Model\Order')->getResourceCollection();
            
            $orders = $orderCollectionObj->addAttributeToSelect('*')
                        ->addFieldToFilter('increment_id', $orderId)
                        ->addAttributeToFilter('store_id', $storeId)
                        ->load();
            return $orders;
	}	
	
			
	#
	# Update Orders via status type method
	# Will update Order Notes and tracking number of  order
	# Input parameter Username,Password, array (OrderID,ShippedOn,ShippedVia,ServiceUsed,TrackingNumber)
	#
	function AutoSyncOrder($username,$password,$data,$statustype,$storeid,$others)
	{
            global $objectManager,$get_Active_Carriers;
		
            $status = $this->CheckUser($username,$password);
            if($status !='0')
            {
                return $status;
            }
				
            $Orders = new Orders();		

            $response_array = $data; 

            if (!is_array($response_array))
            {
                $Orders->setStatusCode("9997");
                $Orders->setStatusMessage("Unknown request or request not in proper format");	
                return $this->response($Orders->getOrders());exit();				
            }

            if (count($response_array) == 0)
            {
                $Orders->setStatusCode("9996");
                $Orders->setStatusMessage("REQUEST array(s) doesnt have correct input format");				
                return $this->response($Orders->getOrders());exit();
            }

            if(count($response_array) == 0) {
                    $no_orders = true; 
            }else {
                    $no_orders = false;
            }
            $Orders->setStatusCode($no_orders?"1000":"0");
            $Orders->setStatusMessage($no_orders?"No new orders.":"All Ok");

            if ($no_orders){
                    return json_encode($response_array);
            }
            $storeId=$this->getDefaultStore($storeid);	
				
            foreach($response_array as $k=>$v)//request
            {

                if(isset($order_wg))
                {
                    unset($order_wg);
                }
                foreach($v as $k1=>$v1)
                {
                    $order_wg[$k1] = $v1;
                }
		

			
                $order_id = $order_wg['OrderID'];
                $current_order = $objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($order_id);

                if($order_wg['IsNotifyCustomer']=='N')
                {
                    $IsNotifyCustomer = false; 
                }else
                {
                    $IsNotifyCustomer = true;
                }
                $isupdated = "error";
                switch ($statustype)
                {
			case 'paymentUpdate':
			break;

			case 'statusUpdate':
			break;
			
			case 'notesUpdate':
			try{
                            $current_order->addStatusToHistory($current_order->getStatus() ,$order_wg['OrderNotes'],$IsNotifyCustomer );
                            $current_order->save();
                            //Mage::unregister('current_order');
                            $isupdated = "success";
			}catch(Exception $e)
			{
                            $isupdated = "error";
			}
			break;
			
			case 'shipmentUpdate':
                        try
                        {
                            //$carrier_name= strtoupper($order_wg['ServiceUsed']);
                            $carrier_name=$order_wg['ServiceUsed'];
                            if($get_Active_Carriers)
                            {
                                $carrierInstances = $objectManager->get('\Magento\Shipping\Model\Config')->getActiveCarriers($storeid);
                            }else{
                                $carrierInstances = $objectManager->get('\Magento\Shipping\Model\Config')->getAllCarriers($storeId);
                            }

                            $carriers['custom'] = (string)__("Custom Value");   //Mage::helper('sales')->__('Custom Value');

                            foreach ($carrierInstances as $code => $carrier)
                            {
                                if ($carrier->isTrackingAvailable()) {
                                    $carriers[$code] = $carrier->getConfigData('title');
                                }
                            }

                            $c_code='';	

                            if (in_array($carrier_name,$carriers,true)){
                                $c_code= array_search($carrier_name, $carriers); 
                            }
				
                            if ($current_order->canShip() && !empty($c_code))
                            {
                                    $CarrierCode = $this->_getShippingCode($order['ShippedVia']);
                                    //Create shipment
                                    //$objectManager->get('\Magento\Sales\Api\ShipmentRepositoryInterface');
                                    
                                    $convertor = $objectManager->get('\Magento\Sales\Model\Convert\Order');
                                    $_shipment = $convertor->toShipment($current_order);
                                    
                                    
                                    $_track = $objectManager->get('\Magento\Sales\Model\Order\Shipment\Track')
                                                    ->setNumber($order_wg['TrackingNumber'])
                                                    ->setCarrierCode($c_code)
                                                    ->setTitle($order_wg['ShippedVia']);
                                    $_shipment->addTrack($_track);
                                    
                                    $isupdated = "success";
                            }else{
                                $isupdated = "error";
                            }
                            }catch(Exception $e)
                            {
                                $isupdated = "error";
                            }
			break;	
                }
                $current_order = $objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($order_id);

                $Order = new Order();
                #$Order->setOrderID($current_order->increment_id);
				$Order->setOrderID($order_id);
                $Order->setStatus($isupdated);
                $Order->setLastModifiedDate($this->_dateformat_wg($current_order->updated_at));
                $Order->setOrderNotes($order_wg['OrderNotes']);
                $statuses = $objectManager->get('Magento\Sales\Model\Order\Config')->getStateStatuses($current_order->getState(),true);
                foreach($statuses as $statval)
                {
                    $statuses=$statval;
                }
                $Order->setOrderStatus($statuses);	
                $Orders->setOrders($Order->getOrder());
            }	
            return $this->response($Orders->getOrders());
	}

	public function _editproduct($storeId=1,$productId)
	{
            global $objectManager;
            $Product = $objectManager->get('Magento\Catalog\Model\Product')->setStoreId($storeId);;
            $Product->load($productId);
            return $Product;
	}	
	
        public function _initShipment($current_order,$RequestOrders,$data)
	{
            global $objectManager;
	    try
            {
                $shipment = false;
                if (!$current_order->getId())
                {
                    $this->Msg[] = 'Error. Order not longer exist.';
                    $this->result = 'Failed';
                    return false;
                }

                if (!$current_order->canShip())
                {
                    return false;
                }

                // Not Ship
                
                $convertor  = $objectManager->get('\Magento\Sales\Model\Convert\Order');
                $_shipment    = $convertor->toShipment($current_order);
                $savedQtys = $this->_getItemQtys($data);
			
                foreach ($current_order->getAllItems() as $orderItem)
                {
                    if(!$orderItem->getQtyToShip())
                    {
                        continue;
                    }
				
                    $_item = $convertor->itemToShipmentItem($orderItem);
                    if (isset($savedQtys[$orderItem->getId()]))
                    {
                        $qty = $savedQtys[$orderItem->getId()];
                    }
                    /* else{
                         $qty = $orderItem->getQtyToShip();
                     }*/
			        	
                    $_item->setQty($qty);
				
                    $_shipment->addItem($_item);
                    unset($qty);
                }
                
                if(is_array($RequestOrders['TRACKINGNUMBER']))
                {
                    $t = 0;
                    foreach($RequestOrders['TRACKINGNUMBER'] as $trackNumber)
                    {
                        if (!empty($trackNumber))
                        {
                            if (!$CarrierCode =$this->_getShippingCode($RequestOrders['SHIPPEDVIA'][$t]))
                            {
                                $CarrierCode="custom";
                                $Title = $RequestOrders['SHIPPEDVIA'][$t];
                            }elseif(isset($RequestOrders['SERVICEUSED'][$t])){
                                $Title = $RequestOrders['SERVICEUSED'][$t];
                            }else{
                                $Title = $RequestOrders['SHIPPEDVIA'][$t];
                            }
                            
                            /* $_track = $objectManager->get('\Magento\Sales\Model\Order\Shipment\Track')
                                                            ->setNumber($trackNumber)
                                                            ->setCarrierCode($CarrierCode)
                                                            ->setTitle($Title);

                            $_shipment->addTrack($_track); */
							$data = array(
							'carrier_code' => $CarrierCode,
							'title' => $Title,
							'number' => $trackNumber, // Replace with your tracking number
							);

							#$track = $this->trackFactory->create()->addData($data);$orderObj->
							$track = $objectManager->get('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
							$_shipment->addTrack($track); 
                        }
                        $t++;
                    }
                }else{
				
                $trackNumber = $RequestOrders['TRACKINGNUMBER'];
                if (!empty($trackNumber))
                {
				
                    if (!$CarrierCode =$this->_getShippingCode($RequestOrders['SHIPPEDVIA']))
                    {
                        $CarrierCode="custom";
                        $Title = $RequestOrders['SHIPPEDVIA'];
                    }elseif(isset($RequestOrders['SERVICEUSED']))
                    {
                        $Title = $RequestOrders['SERVICEUSED'];
                    }else{
                        $Title = $RequestOrders['SHIPPEDVIA'];
                    }
				
                   /*  $_track = $objectManager->get('\Magento\Sales\Model\Order\Shipment\Track')
                                                    ->setNumber($trackNumber)
                                                    ->setCarrierCode($CarrierCode)
                                                    ->setTitle($Title);
                                                    $_shipment->addTrack($_track); */
													
					$data = array(
					'carrier_code' => $CarrierCode,
					'title' => $Title,
					'number' => $trackNumber, // Replace with your tracking number
					);

					#$track = $this->trackFactory->create()->addData($data);$orderObj->
					$track = $objectManager->get('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
					$_shipment->addTrack($track); 
                }
            }
            return $_shipment;
        }catch (Exception $e) {
            $this->Msg[] = "Critical Error _initShipment (Exception e)" ;
        }
    }
    
    public function _saveShipment($shipment)
    {
        global $objectManager;
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = $objectManager->get('Magento\Framework\DB\Transaction')
                            ->addObject($shipment)
                            ->addObject($shipment->getOrder())
                            ->save();
        return $this;
    }
	
    function _saveInvoice($data,$orderId)
    {
        global $objectManager;
        try
        {
            if ($invoice = $this->_initInvoice($orderId,$data,false))
            {
                if (!empty($data['capture_case']))
                {
                    $invoice->setRequestedCaptureCase($data['capture_case']);
                }

                if (!empty($data['comment_text'])) {
                    $invoice->addComment($data['comment_text'], isset($data['comment_customer_notify']));
                }

                $invoice->register();
                if (!empty($data['send_email'])) {
                    $invoice->setEmailSent(true);
                }

                $invoice->getOrder()->setIsInProcess(true);
                
                $transactionSave = $objectManager->get('Magento\Framework\DB\Transaction')->addObject($invoice)->addObject($invoice->getOrder());
                $shipment = false;

                if (!empty($data['do_shipment']))
                {
                    $shipment = $this->_prepareShipment($invoice);##PENDING
                    if ($shipment)
                    {
                        $shipment->setEmailSent($invoice->getEmailSent());
                        $transactionSave->addObject($shipment);
                    }
                }
                $transactionSave->save();

                /**
                 * Sending emails
                 */
                $comment = '';
                if (isset($data['comment_customer_notify']))
                {
                    $comment = $data['comment_text'];
                }

                //$invoice->sendEmail(!empty($data['send_email']), $comment);//PENDING

                if ($shipment)
                {
                    $shipment->sendEmail(!empty($data['send_email']));
                }
            }
        }catch (Mage_Core_Exception $e){
            die($e->getMessage());
            //$this->_getSession()->addError($e->getMessage());
        }catch (Exception $e){
            die('Can not save Invoice'.$e);
            //$this->_getSession()->addError($this->__('Can not save invoice'));
        }
    }
	
    public function _getShippingCode($shipp)
    {
	   	
       $shipp = strtoupper($shipp);	   
       if (array_key_exists($shipp, $this->carriers_)){
          return $this->carriers_[$shipp];
       }
       return false;
    }
	function _getStoreDetails()
	{   
            global $objectManager;
            $storeManagerObj = $objectManager->get('\Magento\Store\Model\Store');
            $informationObj = $objectManager->get('\Magento\Store\Model\Information');
            $regionObj = $objectManager->get('\Magento\Directory\Model\Region');
            $countryObj = $objectManager->get('\Magento\Directory\Model\Country');
            
            $companyInfo['storeName'] = $storeManagerObj->getConfig($informationObj::XML_PATH_STORE_INFO_NAME);
            $companyInfo['storeAddress1'] = $storeManagerObj->getConfig($informationObj::XML_PATH_STORE_INFO_STREET_LINE1);
            $companyInfo['storeAddress2'] = $storeManagerObj->getConfig($informationObj::XML_PATH_STORE_INFO_STREET_LINE2);
            $companyInfo['storeCity'] = $storeManagerObj->getConfig($informationObj::XML_PATH_STORE_INFO_CITY);
            $country_code = $companyInfo['storeCountry'] = $storeManagerObj->getConfig($informationObj::XML_PATH_STORE_INFO_COUNTRY_CODE);
            
            if($companyInfo['storeCountry']=='canada' || $companyInfo['storeCountry']=='Canada')
            {
                    $country_code='CA';
            }
            //echo $country_id = $countryObj->loadByCode($country_code)->getId();
            
            $companyInfo['storeRegion'] = $storeManagerObj->getConfig($informationObj::XML_PATH_STORE_INFO_REGION_CODE);
            $companyInfo['storeRegion'] = $regionObj->load($companyInfo['storeRegion'])->getCode();
            
            
            $companyInfo['storePostCode'] = $storeManagerObj->getConfig($informationObj::XML_PATH_STORE_INFO_POSTCODE);
            $companyInfo['storePhone'] = $storeManagerObj->getConfig($informationObj::XML_PATH_STORE_INFO_PHONE);
            $companyInfo['websiteUrl'] = $storeManagerObj->getConfig($storeManagerObj::XML_PATH_SECURE_BASE_URL);
            //$companyInfo['cataloginventory']['item_options']['min_qty']  = $storeManagerObj->getConfig($storeManagerObj::XML_PATH_MIN_QTY);
            
            //$recipientRegionCode = $this->_regionFactory->create()->load($companyInfo['storeRegion'])->getCode();
            //print_r($companyInfo);
            return($companyInfo);
	}
	
	public function _getPaymentMethods($store=null)
	{
            global $objectManager;
            $paymentObj = $objectManager->get('\Magento\Payment\Model\Config');
            $method = $paymentObj->getActiveMethods();
		if(is_array($method))
		{
			return $method;
		}
	}

    function _initInvoice($orderId,$data,$update = false)
    {
        global $objectManager; 
        $invoice = false;
        $order = $objectManager->get('Magento\Sales\Model\Order')->load($orderId);
        
        if (!$order->canInvoice())
        {
            return false;
        }

	$convertor  = $objectManager->get('Magento\Sales\Model\Convert\Order');
        $invoice    = $convertor->toInvoice($order);
        $savedQtys = $this->_getItemQtys($data);

        foreach ($order->getAllItems() as $orderItem)
	{
            $item = $convertor->itemToInvoiceItem($orderItem);
            if (isset($savedQtys[$orderItem->getId()]))
            {
                $qty = $savedQtys[$orderItem->getId()];
            }else
            {
                if ($orderItem->isDummy())
                {
                    $qty = 1;
                }
                else
                {
                    $qty = $orderItem->getQtyToInvoice();
                }
            }
            
            $item->setQty($qty);
            $invoice->addItem($item);
        }
        
        $invoice->collectTotals();
        //Mage::register('current_invoice', $invoice);
        return $invoice;
    }
	
    public function _getItemQtys($data)
    {
     	if (isset($data['items']))
        {
            $qtys = $data['items'];
        }else{
            $qtys = array();
        }
        return $qtys;
    }
    
	public function getItemsByName($username,$password,$start_item_no=0,$limit=500,$itemname ,$storeId=1,$others)
	{
		
		$storeId=$this->getDefaultStore($storeId);		
		$status = $this->CheckUser($username,$password);
		if($status!="0")
		{		
			return $status;
		}
		$Items = new Items();		
		
		
		$items_query_product = $this->_getProductByName($storeId,$start_item_no,$limit,"");
		$count_query_product = $items_query_product->getSize();
		
		$Items->setStatusCode('0');
		$Items->setStatusMessage('All Ok');
		$Items->setTotalRecordFound($count_query_product?$count_query_product:'0');
		$Items->setTotalRecordSent(count($items_query_product->getItems())?count($items_query_product->getItems()):'0');

		if(count($items_query_product)>0)
			{
				#get the manufacturer
				$manufacturer = $this->_getmanufacturers($storeId);
				if($manufacturer['totalRecords']>0)
				{
					foreach($manufacturer['items'] as $manufacturer1)
					{
						$manufacturer2[$manufacturer1['option_id']] = $manufacturer1['value'];
					}
				}
				unset($manufacturer,$manufacturer1);
				$itemI = 0;
				foreach ($items_query_product->getItems() as $iInfo11)
				{
					$iInfo['category_ids'] = $iInfo11->getCategoryIds();
					$options = $this->_getoptions($iInfo11);
					$iInfo = $iInfo11->toArray();
					if($iInfo['type_id']=='simple' || $iInfo['type_id']=='virtual' || $iInfo['type_id']=='downloadable')
					{
						$Item = new Item();
						$desc=addslashes(htmlspecialchars(substr($iInfo['description'],0,4000),ENT_QUOTES));
						$stockItem =  Mage::getModel('cataloginventory/stock_item')->loadByProduct($iInfo['entity_id']);
						$stockItem=$stockItem->toArray();
						
						$Item->setItemID($iInfo['entity_id']);						
						$Item->setItemCode($iInfo['sku']);
						$Item->setItemDescription($iInfo['name']);
						$Item->setItemShortDescr(substr($desc,0,300));

						if(is_array($iInfo['category_ids']))
						{
							$categoriesI = 0;
							foreach ($iInfo['category_ids'] as $category)
							{
								//$catArray['Category'] = '';
								unset($catArray);
								$catArray['CategoryId'] = $category;
								$Item->setCategories($catArray);
								$categoriesI++;
							}
						}
						if(!$categoriesI)$Item->setCategories('');
						
						$iInfo['manufacturer'] = $iInfo['manufacturer']?$manufacturer2[$iInfo['manufacturer']]:$iInfo['manufacturer'];
						
						$Item->setManufacturer($iInfo['manufacturer']);
						$Item->setQuantity($stockItem['qty']);
						$Item->setUnitPrice($iInfo11->getPrice());
						$Item->setListPrice($iInfo['cost']);
						$Item->setWeight($iInfo11->getWeight());
						$Item->setLowQtyLimit($stockItem['min_qty']);
						$Item->setFreeShipping('N');
						$Item->setDiscounted('');
						$Item->setShippingFreight('');
						$Item->setWeight_Symbol('lbs');
						$Item->setWeight_Symbol_Grams('453.6');
						$Item->setTaxExempt('N');
						$Item->setUpdatedAt($iInfo["updated_at"]);
						
						$responseArray['Items'][$itemI]['ItemVariants'] = '';
						if(is_array($options) && count($options)>0)
						{
							$optionI = 0;
							foreach($options as $ioInfo)
							{
								$ioInfo = parseSpecCharsA($ioInfo);
								unset($responseArray['ItemOption']);
								$responseArray['ItemOption']['ID'] = $ioInfo['option_type_id'];
								$responseArray['ItemOption']['Value'] = htmlspecialchars($ioInfo['title'],ENT_QUOTES);
								$responseArray['ItemOption']['Name'] = htmlspecialchars($ioInfo['option_title'],ENT_QUOTES);
								$Item->setItemOption($responseArray['ItemOption']);		
								$optionI++;
							}
						}
						
						$Item->setItemVariants('');
						$Items->setItems($Item->getItem()); 
						
					}
					$itemI++;
				} // end items
			}
			return $this->response($Items->getItems());
	}
	public function _getoptions($product)
	{
		$collection = $product->getOptionInstance()->getProductOptionCollection($product);
		$lastvalues = array();
		$j=0;
		$collection = $collection->toArray();
		if(count($collection['items'])>0)
		{
			foreach($collection['items'] as $items)
			{
				$values = Mage::getModel('catalog/product_option_value')
								->getCollection()
								->addTitleToResult(1)
								->addPriceToResult(1)
								->addOptionToFilter(array($items['option_id']))
								->setOrder('sort_order', 'asc')
								->setOrder('title', 'asc');
				$values = $values->toArray();
				for($i=0;$i<(count($values['items']));$i++)
				{
					$values['items'][$i]['option_title']= $items['default_title'];
					$lastvalues[$j] = $values['items'][$i];
					$j++;
				}
			}
			return $lastvalues;
		}

	}
	
	public function _getProductByName($storeId=1,$start_item_no=0,$limit=20,$itemname)
	{
		if($start_item_no > 0)
		{
			if($start_item_no>$limit)
			{
				$start_no=intval($start_item_no/$limit)+1;
			}else{
				$start_no=intval($limit/$start_item_no)+1;
			}
		}else{
			$start_no = 0;
		}
		$productsCollection = Mage::getResourceModel('catalog/product_collection')
								->addAttributeToSelect('*')
								->addStoreFilter($storeId)
								->addFieldToFilter(array(array('attribute'=>'name', 'like'=>"%$itemname%")))
								->addAttributeToSort('entity_id', 'asc')
								->setPageSize($limit)
								->setCurPage($start_no);
		return $productsCollection;
	}
	
	function getItemsQuantity($username,$password,$storeid=1,$others)
	{

		$storeId=$this->getDefaultStore($storeid);
		$status =  $this->CheckUser($username,$password);
		if($status!="0")
		{		
			return $status;
		}
		$Items = new Items();
		$Items->setStatusCode('0');
		$Items->setStatusMessage('All Ok');

		

		
		$product = Mage::getModel('catalog/product');
		$stockItemObj = $product->getCollection()
							->addAttributeToSelect('name', true)
							->addAttributeToSelect('sku', true)
							->addAttributeToSelect('price', true)
							->addAttributeToSelect('cost', true)
							->addAttributeToSelect('updated_at', true)
							->joinTable('cataloginventory/stock_item', 'product_id=entity_id', array('qty'=>'qty', 'notify_stock_qty'=>'notify_stock_qty', 'use_config' => 'use_config_notify_stock_qty','low_stock_date' => 'low_stock_date'))->load();
		$stockItem = $stockItemObj->toArray();
		
		$Items->setTotalRecordFound($stockItemObj->getSize());
		foreach($stockItem as $item)
		{
				$Item = new Item();
				$Item->setItemID($item['entity_id']);
				$Item->setQuantity($item['qty']);
				$Item->setUnitPrice($item['price']);
				$Item->setListPrice($item['cost']);
				$Item->setUpdatedAt($item['updated_at']);
				$Items->setItems($Item->getItem());
		}
		
		
		return $this->response($Items->getItems());
	}
	
	#Add new functions related to customer
	public function addCustomers($username,$password,$data,$storeid=1,$others='')
        {
	    global $objectManager;			
            $status = $this->CheckUser($username,$password);
            if($status!="0")
            {		
                return $status;
            }

            $storeId=$this->getDefaultStore($storeid);

            $Customers = new Customers();
            $Customers->setStatusCode('0');
            $Customers->setStatusMessage('All Ok');

            $requestArray = $data;
            //$requestArray = json_decode($item_json_array, true);
            if (!is_array($requestArray)) {
                $Items->setStatusCode('9997');
                $Items->setStatusMessage('Unknown request or request not in proper format');				
                return $this->response($Items->getItems());
            }

            if (count($requestArray) == 0) {
                $Items->setStatusCode('9996');
                $Items->setStatusMessage('REQUEST tag(s) doesnt have correct input format');
                return $this->response($Items->getItems());
            }
					
            foreach($requestArray as $k=>$vCustomer)
            {
                $customer = $objectManager->get('Magento\Customer\Model\Customer');
                $Email			=	$vCustomer['Email'];
                $CustomerId		=	$vCustomer['CustomerId'];
                $firstname		=	$vCustomer['FirstName'];
                $middlename		=	$vCustomer['MiddleName'];
                $lastname		=	$vCustomer['LastName'];
                $company		=	$vCustomer['Company'];
                $street1		=	$vCustomer['Address1'];
                $street2		=	$vCustomer['Address2'];
                $city			=	$vCustomer['City'];

                $postcode		=	$vCustomer['Zip'];
                $country_code           =	$vCustomer['Country'];
                $tel			=	$vCustomer['Phone'];
                $group			=	$vCustomer['CustomerGroup'];
                $password               =	md5(rand(6,10));

                $country_id='';
                $region_id = '';
                try 
                {
                    if($country_code=='canada' || $country_code=='Canada')
                    {
                        $country_code='CA';
                    }
                    $country_id = $objectManager->get('Magento\Directory\Model\Country')->loadByCode($country_code,'iso3_code')->getId();
                    $region	= $vCustomer['State'];
                    $regionModel = $objectManager->get('Magento\Directory\Model\Region')->loadByName($region,$country_id);
                    $region_id = $regionModel->getId();
                }
                catch (Exception $ex) 
                {

                }
                
                $customer->setWebsiteId($objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getWebsiteId());
                $customer->loadByEmail($Email);

                if(!$customer->getId())
                {
                    $customer->setEmail($Email);
                    $customer->setFirstname($firstname);
                    $customer->setLastname($lastname);
                    $customer->setPassword($password);
                    $customer->setData( 'group_id', $group);

                    try
                    {
                        $customer->save();
                        $customer->setConfirmation(null);
                        $customer->save();
                        $isNewCustomer = $customer->isObjectNew();
                        // send welcome email

                        $storeId = $customer->getSendemailStoreId();
                        if ($isNewCustomer)
                        {
                            if($vCustomer['IsNotifyCustomer']=='Y')
                            {
                                $customer->sendNewAccountEmail('registered', '', $storeId);
                            }	
                        }

                        // confirm not confirmed customer
                        else if ((!$customer->getConfirmation()))
                        {
                            if($vCustomer['IsNotifyCustomer']=='Y')
                            {
                                $customer->sendNewAccountEmail('confirmed', '', $storeId);
                            }	
                        }

                        $newPassword='auto';

                        if ($newPassword == 'auto')
                        {
                            $newPassword = $customer->generatePassword();
                        }

                        $customer->changePassword($newPassword);

                        if($vCustomer['IsNotifyCustomer']=='Y')
                        {
                            $customer->sendPasswordReminderEmail();
                        }
                    }
                    catch (Exception $ex) 
                    {
                        $result = $ex->getMessage();
                    }

                    //Build billing and shipping address for customer, for checkout
                    $_custom_address = array (
                            'firstname' => $firstname,
                            'lastname' => $lastname,
                            'company' => $company,
                            'street' => array (
                                    '0' => $street1,
                                    '1' => $street2,
                            ),

                            'city' => $city,
                            'region_id' => $region_id,
                            'region' => $region,
                            'postcode' => $postcode,
                            'country_id' => $country_id, /* Croatia */
                            'telephone' => $tel,
                    );
                    
                    $customAddress = $objectManager->get('Magento\Customer\Model\Address');
                    //$customAddress = new Mage_Customer_Model_Address();
                    $customAddress->setData($_custom_address)
                                            ->setCustomerId($customer->getId())
                                            ->setIsDefaultBilling('1')
                                            ->setIsDefaultShipping('1')
                                            ->setSaveInAddressBook('1');

                    try
                    {
                        $customAddress->save();
                    }
                    catch (Exception $ex) {
                            $result = $ex->getMessage();
                            die($result."in catch");
                    }

                    $Customer = new Customer();
                    $Customer->setCustomerId($customer->getId());
                    $Customer->setStatus('Success');
                    $Customer->setFirstName($firstname);
                    $Customer->setMiddleName($middlename);
                    $Customer->setLastName($lastname);
                    $Customer->setCustomerGroup($group);
                    $Customer->setemail($Email);
                    $Customer->setCompany($company);
                    $Customer->setAddress1($vCustomer['Address1']);
                    $Customer->setAddress2($vCustomer['Address2']);
                    $Customer->setCity($city);
                    $Customer->setState($region);
                    $Customer->setZip($postcode);
                    $Customer->setCountry($country_code);
                    $Customer->setPhone($tel);

                    $Customers->setCustomer($Customer->getCustomer());

                } 
                else
                {
                    $Customer = new Customer();
                    $Customer->setStatus('Customer email already exist');
                    $Customer->setCustomerId($customer->getId());
                    $Customer->setFirstName($firstname);
                    $Customer->setLastName($lastname);
                    $Customer->setemail($Email);
                    $Customer->setCompany($company);
                    $Customers->setCustomer($Customer->getCustomer());
		}
            }
            return $this->response($Customers->getCustomers());					
	}
	
	
	function getCustomersNew($username,$password,$datefrom,$customerid,$limit,$storeid=1,$others)
        {
            global $objectManager;
            
            $datefrom =$datefrom ?$datefrom:0;		
            $status = $this->CheckUser($username,$password);
            if($status!="0")
            {		
                    return $status;
            }
		
            $storeId=$this->getDefaultStore($storeid);
            $Customers = new Customers();
		
            $customersArray = $this->_getCustomer($customerid,$datefrom,$storeId,$limit);
            
            $count_query_customers = $customersArray->getSize();
		
            //$customersArray = $customersArray->toarray();
            
            $no_customer =false;	
            if(count($customersArray)<=0)
            {
                $no_customer = true;
            }
            $Customer = new Customer();	

            $Customers->setStatusCode('0');
            $Customers->setStatusMessage('All Ok');
            $Customers->setTotalRecordFound($count_query_customers?$count_query_customers:'0');
            $Customers->setTotalRecordSent(count($customersArray)?count($customersArray):'0');


            foreach($customersArray as $customer)
            {

                $address = $objectManager->get('Magento\Customer\Model\Address')->load($customer->getDefaultBilling());
                $address = $address->toArray();
                $customer = $customer->toArray();

                $Customer = new Customer();

                $Customer->setCustomerId($customer["entity_id"]);
                $Customer->setFirstName($customer["firstname"]);
                $Customer->setMiddleName($customer["middlename"]);
                $Customer->setLastName($customer["lastname"]);
                $Customer->setCustomerGroup($customer["group_id"]);

                if(!isset($customer["created_at"]) || $customer["created_at"]==''){
                    $customer["created_at"]='2007-01-01 00:00:00' ;
                }

                $Customer->setCreatedAt($customer["created_at"]);

                if(!isset($customer["updated_at"]) || $customer["updated_at"]=='')
                {
                    $customer["updated_at"]='2007-01-01 00:00:00' ;
                }

                $Customer->setUpdatedAt($customer["updated_at"]);
                $Customer->setemail($customer["email"]);
                $subscriber = $objectManager->get('Magento\Newsletter\Model\Subscriber')->loadByEmail($customer["email"]);

                if($subscriber->getId())
                {
                    $Customer->setsubscribedToEmail("true");
                }
                else
                {
                    $Customer->setsubscribedToEmail("false");
                }

                $Customer->setcompany($address["company"]);
                $Customer->setAddress1($address["street"]);
                $Customer->setAddress2("");
                $Customer->setCity($address["city"]);
                $Customer->setState($address["region"]);
                $Customer->setZip($address["postcode"]);
                $Customer->setCountry($address["country_id"]);
                $Customer->setPhone($address["telephone"]);
                $group = $objectManager->get('Magento\Customer\Model\Group')->load($customer["group_id"]);
                $group_nam=$group->getCode();
                $Customer->setGroupName($group_nam);

                $Customers->setCustomer($Customer->getCustomer());
            }
            return $this->response($Customers->getCustomers());
	}
	
	public function _getCustomer($start_item_no,$datefrom,$storeId,$limit)
	{
            global $objectManager;
            $productsCollection = $objectManager->get('Magento\Customer\Model\Customer')
                                    ->getCollection()
                                    ->addAttributeToSelect('*')
                                    ->addAttributeToSort('entity_id', 'asc')
                                    ->addFieldToFilter('entity_id',  array('gt'=> intval($start_item_no)))
                                    ->setPageSize($limit);
            return $productsCollection;
	}
	
	
	function getCustomerGroup($username,$password,$storeid=1,$others)
	{
	
		$storeId=$this->getDefaultStore($storeid);
		$status = $this->CheckUser($username,$password);
		if($status!="0")
		{		
			return $status;
		}
		$Groupsets = new Groupsets();
		$Groupsets->setStatusCode('0');
		$Groupsets->setStatusMessage('All Ok');
		
		$groupsData = $this->_getCustomerGroup($storeid);

		if(count($groupsData)>0)
		{
			$i =0;
			foreach($groupsData as $key=>$val)
			{
				$Groupset =new Groupset();
				$Groupset->setGroupsetID($key);
				$Groupset->setGroupsetName($val);
				$Groupsets->setGroupsets($Groupset->getGroupset());
				$i++;
			}
		}
		return $this->response($Groupsets->getGroupsets());
	}

	
	public function _getCustomerGroup($storeId=1)
	{
            global $objectManager;
            
            $searchCriteriaBuilder = $objectManager->get('Magento\Framework\Api\SearchCriteriaInterface');
            $groupRepository = $objectManager->get('Magento\Customer\Model\ResourceModel\GroupRepository');
            
            foreach ($groupRepository->getList($searchCriteriaBuilder)->getItems() as $item) {
                $customerGroup[$item->getId()] = $item->getCode();
            }
            return($customerGroup);
	}
	#below are  

	public function AddShipmentByOrder($current_order,$RequestOrders,$data)
	{
		try
		{
            if ($shipment = $this->_initShipment($current_order,$RequestOrders,$data))
			{
				$shipment->register();
                $this->Msg[] = 'Create Shipment .';
                $comment = $data['comment_text'];
                $shipment->addComment($comment,true );
                if ($this->send_email)
				{
                    $shipment->setEmailSent(true);
                }
                $this->_saveShipment($shipment);
				if($data['copy_email'] == 1)
				{
					if($data['append_comment'] == 1)
					{
						$shipment->sendUpdateEmail($this->send_email, $comment);
						$this->send_email;
					}else{
						$shipment->sendUpdateEmail($this->send_email, '');
						$this->send_email;
					}
				}
                return true;
            }else {
                return false;
            }
        }catch(Mage_Core_Exception $e) {
			$this->Msg[] = "Critical Error AddShipment (Mage_Core_Exception e)";
		}
        catch (Exception $e)
		{
			$this->Msg[] = "Critical Error AddShipment (Exception e)" ;
		}
    }

    public function sendOrderUpdateEmail($notifyCustomer=true, $comment='')
    {
        $bcc = $this->_getEmails(self::XML_PATH_UPDATE_EMAIL_COPY_TO);
        if (!$notifyCustomer && !$bcc)
		{
            return $this;
        }
        $mailTemplate = Mage::getModel('core/email_template');
        if ($notifyCustomer)
		{
            $customerEmail = $this->getCustomerEmail();
            $mailTemplate->addBcc($bcc);
        }else{
            $customerEmail = $bcc;
        }
        if ($this->getCustomerIsGuest())
		{
            $template = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $this->getStoreId());
            $customerName = $this->getBillingAddress()->getName();
        }else{
            $template = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $this->getStoreId());
            $customerName = $this->getCustomerName();
        }
        $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store' => $this->getStoreId()))
            ->sendTransactional(
                $template,
                Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $this->getStoreId()),
                $customerEmail,
                $customerName,
                array(
                    'order'     => $this,
                    'billing'   => $this->getBillingAddress(),
                    'comment'   => $comment
                )
        	);
        return $this;
    }

	public function getProduct($storeId=1,$start_item_no=0,$limit=20,$date_time,$others)
	{
            global $objectManager;
            
            if($start_item_no > 0)
            {
                if($start_item_no>$limit)
                {
                    $start_no=intval($start_item_no/$limit)+1;
                }else{
                    $start_no=intval($limit/$start_item_no)+1;
                }
            }else{
                $start_no = 0;
            }
		
            $date_time = $date_time? $date_time:'0';
            
            $filter_array = array(array('attribute'=>'updated_at','gteq'=>$date_time),array('attribute'=>'created_at','gteq'=>$date_time));
            
            //$others[0]['ItemCode'] = "'Xolo'";
            ##SINCE MAGENTO ADD SINGLE QUOTE, WE HAVE TO REMOVE PROVIDED SINGLE QUOTES FROM EACH SKU
            
            if(isset($others[0]['ItemCode']) && trim($others[0]['ItemCode'])!='')
            {
                $others[0]['ItemCode'] = str_replace("'","",$others[0]['ItemCode']);
                $date_time = 'NA';
                $skuArray = explode(",",$others[0]['ItemCode']);
                //print_r($skuArray);
                $filter_array = array(array('attribute'=>'sku','in'=>array($skuArray)));
            }
            
            
            $productFactory = $objectManager->get('\Magento\Catalog\Model\Product');
            $productCollection = $productFactory->getResourceCollection();
            $productData = $productCollection->addAttributeToSelect('*')
                    //->addStoreFilter($storeId)
                    ->addAttributeToFilter($filter_array)
                    ->addAttributeToSort('entity_id', 'asc')
                    ->setPageSize($limit)
                    ->setCurPage($start_no);
            
            return $productData;
	}





	public  function  GetOrdersRemained($datefrom,$start_order_no=0,$order_status_list='',$storeId=1)
	{
		$order_status_list = str_replace("'","",$order_status_list);
		$order_status_list = explode(",",$order_status_list);
		$order_status = $this->_orderStatustofetch($order_status_list,$storeId);
		try{
			$datetime1 = explode("-",$datefrom);
			$datefrom = $datetime1[2]."-".$datetime1[0]."-".$datetime1[1];
			$datefrom .=" 00:00:00";
			$this->_orders = Mage::getResourceModel('sales/order_collection')
									->addAttributeToSelect('*')
									->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
									->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
									->joinAttribute('billing_street', 'order_address/street', 'billing_address_id', null, 'left')
									->joinAttribute('billing_company', 'order_address/company', 'billing_address_id', null, 'left')
									->joinAttribute('billing_city', 'order_address/city', 'billing_address_id', null, 'left')
									->joinAttribute('billing_region', 'order_address/region', 'billing_address_id', null, 'left')
									->joinAttribute('billing_country', 'order_address/country_id', 'billing_address_id', null, 'left')
									->joinAttribute('billing_postcode', 'order_address/postcode', 'billing_address_id', null, 'left')
									->joinAttribute('billing_telephone', 'order_address/telephone', 'billing_address_id', null, 'left')
									->joinAttribute('billing_fax', 'order_address/fax', 'billing_address_id', null, 'left')
									->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
									->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
									->joinAttribute('shipping_street', 'order_address/street', 'shipping_address_id', null, 'left')
									->joinAttribute('shipping_company', 'order_address/company', 'shipping_address_id', null, 'left')
									->joinAttribute('shipping_city', 'order_address/city', 'shipping_address_id', null, 'left')
									->joinAttribute('shipping_region', 'order_address/region', 'shipping_address_id', null, 'left')
									->joinAttribute('shipping_country', 'order_address/country_id', 'shipping_address_id', null, 'left')
									->joinAttribute('shipping_postcode', 'order_address/postcode', 'shipping_address_id', null, 'left')
									->joinAttribute('shipping_telephone', 'order_address/telephone', 'shipping_address_id', null, 'left')
									->joinAttribute('shipping_fax', 'order_address/fax', 'shipping_address_id', null, 'left')
									->addAttributeToFilter('created_at', array('from' => $datefrom,'datetime' => true))
									->addAttributeToFilter('store_id', $storeId)
									->addAttributeToFilter('entity_id', array('gt' => $start_order_no))
									->addAttributeToFilter('status', array('in' => $order_status))
									->addAttributeToSort('entity_id', 'asc')
									->load();
			return $this->_orders;
		 	if (count($this->_orders)==0){
		   		exit;
		 	}
		}catch (Exception $e) {
			Mage::printException($e);
		}
	}
  


	public function _getvisibilitystatus()
	{
		$_visibilitystatus = Mage::getModel('Mage_Catalog_Model_Product_Visibility')->getOptionArray();
        return $_visibilitystatus;
	}
	
	
	
	public function isAuthorized($username,$password,$others)
	{
		$responseArray = array();
		$objNew = new Mage_Webgility_Model_Shop1_Run();
		$status =  $objNew->CheckUser($username,$password);
		if($status!="0")
		{ //login name invalid
			if($status=="1"){
				$responseArray['StatusCode'] = '1';
				$responseArray['StatusMessage'] = 'Invalid login. Authorization failed';
			}
			if($status=="2"){ //password invalid
				$responseArray['StatusCode'] = '2';
				$responseArray['StatusMessage'] = 'Invalid password. Authorization failed';
			}
			$response = json_encode($responseArray);
			return response($response);
			exit;
		}

	}


	public function saveShipment($username,$password,$xmlShipmentItem,$storeid=1)
	{
		$storeId=$this->getDefaultStore($storeid);
		$data =array();
		$items =array();
	
		$xmlResponse = new xml_doc();
		$xmlResponse->version='1.0';
		$xmlResponse->encoding='UTF-8';
		$root = $xmlResponse->createTag("RESPONSE", array());
		$objNew = new Mage_Webgility_Model_Shop1_Run();
		$status =  $objNew->CheckUser($username,$password);
		if($status!="0"){ //login name invalid
			if($status=="1"){
			$xmlResponse->createTag("StatusCode", array(), "1", $root, __ENCODE_RESPONSE);
			$xmlResponse->createTag("StatusMessage", array(), "Invalid login. Authorization failed", $root, __ENCODE_RESPONSE);
			return $xmlResponse->generate();
			}
			if($status=="2"){ //password invalid
			$xmlResponse->createTag("StatusCode", array(), "2", $root, __ENCODE_RESPONSE);
			$xmlResponse->createTag("StatusMessage", array(), "Invalid password. Authorization failed", $root, __ENCODE_RESPONSE);
			return $xmlResponse->generate();
			}
		}
	
		$xmlRequest = new xml_doc($xmlShipmentItem);
		$xmlRequest->parse();
		$xmlRequest->getTag(0, $_tagName, $_tagAttributes, $_tagContents, $_tagTags, __ENCODE_RESPONSE);
	
		if (strtoupper(trim($_tagName)) != 'REQUEST') {
			$xmlResponse->createTag("StatusCode", array(), "9997", $root, __ENCODE_RESPONSE);
			$xmlResponse->createTag("StatusMessage", array(), "Unknown request or request not in proper format", $root, __ENCODE_RESPONSE);
			return $xmlResponse->generate();
		}
	
		if (count($_tagTags) == 0) {
			$xmlResponse->createTag("StatusCode", array(), "9996", $root, __ENCODE_RESPONSE);
			$xmlResponse->createTag("StatusMessage", array(), "REQUEST tag(s) doesnt have correct input format", $root, __ENCODE_RESPONSE);
			return $xmlResponse->generate();
		}
	
		$ShipmentTag = $xmlRequest->getChildByName(0, "SHIPMENT");
		$xmlRequest->getTag($ShipmentTag, $_tagName, $_tagAttributes, $_tagContents, $_tagTags, __ENCODE_RESPONSE);
		foreach($_tagTags as $k=>$v){
			$xmlRequest->getTag($v, $_tagName, $_tagAttributes, $_tagContents, $_contentTags, __ENCODE_RESPONSE);
			if($_tagContents !=''){
				$data[$_tagName] = $_tagContents;
			}
			$i =0;
			foreach($_contentTags as $k1=>$v1){
				$xmlRequest->getTag($v1, $_tagName, $_tagAttributes, $_tagContents, $_itemsTags, __ENCODE_RESPONSE);
				if($_tagName == 'ITEM'){
					foreach($_itemsTags as $k2=>$v2){
						$xmlRequest->getTag($v2, $_tagName, $_tagAttributes, $_tagContents, $_itemsTags, __ENCODE_RESPONSE);
						$items[$i][$_tagName] = $_tagContents;
					}
				}
				if($_tagName == 'SHIPPING'){
					foreach($_itemsTags as $k2=>$v2){
						$xmlRequest->getTag($v2, $_tagName, $_tagAttributes, $_tagContents, $_itemsTags, __ENCODE_RESPONSE);
						$data['SHIPPING'][$i][$_tagName] = $_tagContents;
					}
				}
				$i++;
			}
		}
	
		$orders = $this->UpdateOrdersShippingStatus($data['ORDERID'],$storeId);
		$orders_array=$orders->toArray();
		unset($orders);
		if(array_key_exists('items',$orders_array))
			$orders_array_w =$orders_array['items'];
		else
			$orders_array_w =$orders_array;
	
		foreach($orders_array_w as $orders_el){
			$current_order = $ob->load($orders_el['entity_id']);
			$i=0;
			$qtyCount = 0;
			$totalQty = 0;
			$ItemCount = count($current_order->getAllItems());
			foreach ($current_order->getAllItems() as $item_o) {
				if(!empty($items[$i]['QTY']) || $items[$i]['QTY'] !=0){
					$item_o->setQtyInvoiced($item_o->getQtyToShip());
					$itemData['items'][$item_o->getId()] = $items[$i]['QTY'];
					$totalQty = $totalQty +	$items[$i]['QTY'];
					$i++;
				}else{
					$qtyCount++;
				}
			}
			$itemData['comment_text'] = $data['SHIPMENTCOMMENT'];
	
			if(array_key_exists('APPENDCOMMENT',$data))
				$itemData['append_comment'] = $data[APPENDCOMMENT];
			else
				$itemData['append_comment'] = 0;
	
			if(array_key_exists('EMAILCOPY',$data))
				$itemData['copy_email'] = $data[EMAILCOPY];
			else
				$itemData['copy_email'] = 0;
		}
	
		if($qtyCount == $ItemCount)
		{
			$xmlResponse->createTag("StatusCode", array(), "001", $root, __ENCODE_RESPONSE);
			$xmlResponse->createTag("StatusMessage", array(), "Item not found for shipment", $root, __ENCODE_RESPONSE);
			return $xmlResponse->generate();
		}
		$k =0;
		foreach($data['SHIPPING'] as $shippingcontent)
		{
			$shipingNo[$k] = $shippingcontent['NUMBER'];
			$trackingCarrier[$k] = $shippingcontent['TRACKINGCARRIER'];
			$k++;
		}
		$RequestOrders = array("TRACKINGNUMBER"=>$shipingNo,"SHIPPEDVIA"=>$trackingCarrier,"SERVICEUSED"=>$trackingCarrier);
		if($shipment = $objNew->AddShipmentByOrder($current_order,$RequestOrders,$itemData))
		{
			$msg = 'The shipment has been created.Item quantity is'.$totalQty;
			$xmlResponse->createTag("StatusCode", array(), "002", $root, __ENCODE_RESPONSE);
			$xmlResponse->createTag("StatusMessage", array(), $msg, $root, __ENCODE_RESPONSE);
			return $xmlResponse->generate();
		}else{
			$xmlResponse->createTag("StatusCode", array(), "003", $root, __ENCODE_RESPONSE);
			$xmlResponse->createTag("StatusMessage", array(), "The shipment has been failed", $root, __ENCODE_RESPONSE);
			return $xmlResponse->generate();
		}
	}

	public function getVisibilityStatus($username,$password,$storeid=1,$others)
	{
		$responseArray = array();
		$objNew = new Mage_Webgility_Model_Shop1_Run();
		$status =  $objNew->CheckUser($username,$password);
		if($status!="0"){ //login name invalid
			if($status=="1"){
				$responseArray['StatusCode'] = '1';
				$responseArray['StatusMessage'] = 'Invalid login. Authorization failed';
			}
			if($status=="2"){ //password invalid
				$responseArray['StatusCode'] = '2';
				$responseArray['StatusMessage'] = 'Invalid password. Authorization failed';
			}
		}else{
			$responseArray['StatusCode'] = '0';
			$responseArray['StatusMessage'] = 'All Ok';
			$visibilitystatus = $objNew->_getvisibilitystatus();
			if(is_array($visibilitystatus))
			{
				$i =0;
				foreach($visibilitystatus as $vstatusKey=>$vstatusVal)
				{
					$responseArray['VisibilityStatus'][$i]['StatusId'] = $vstatusKey;
					$responseArray['VisibilityStatus'][$i]['StatusName'] = $vstatusVal;
					$i++;
				}
			}
		}
		$response = json_encode($responseArray);
		return response($response);
	}

	public function getOrderStatusForOrder($username,$password,$storeid=1,$others)
	{
		$storeId=$this->getDefaultStore($storeid);
		$responseArray = array();
		$objNew = new Mage_Webgility_Model_Shop1_Run();
		$status =  $objNew->CheckUser($username,$password);
		if($status!="0"){ //login name invalid
			if($status=="1"){
				$responseArray['StatusCode'] = '1';
				$responseArray['StatusMessage'] = 'Invalid login. Authorization failed';
			}
			if($status=="2"){ //password invalid
				$responseArray['StatusCode'] = '2';
				$responseArray['StatusMessage'] = 'Invalid password. Authorization failed';
			}
		}else{
			$responseArray['StatusCode'] = '0';
			$responseArray['StatusMessage'] = 'All Ok';
			$orderStatus = $objNew->_getorderstatuses($storeId);
			$invoiceflag = 0;
			$i=0;
			foreach($orderStatus as $id=>$val)
			{
				$responseArray['OrderStatus'][$i]['StatusId'] = $id;
				$responseArray['OrderStatus'][$i]['StatusName'] = $val;
				if($id == 'invoice')
					$invoiceflag = 1;
				$i++;
			}
			if($invoiceflag != 1){
				$responseArray['OrderStatus'][$i]['StatusId'] = 'Invoice';
				$responseArray['OrderStatus'][$i]['StatusName'] = 'Invoice';
			}
		}
		$response = json_encode($responseArray);
		return response($response);
	}
	

	public function parseSpecCharsA($arr)
	{
		foreach($arr as $k=>$v)
		{
			if(is_array($k))
			{
				foreach($k as $l=>$m)
				{
					$arr[$l] = addslashes(htmlentities($m, ENT_QUOTES));
				}
			}else{
				$arr[$k] = addslashes(htmlentities($v, ENT_QUOTES));
			}
		}
		return $arr;
	}

    public function _initOrder($id)
    {
        global $objectManager;
        $orderObj = $objectManager->get('\Magento\Sales\Model\Order');
        $order = $orderObj->load($id);
        if (!$order->getId())
        {
            return "Error : Order not present";
        }
        //Mage::register('sales_order', $order);
        //Mage::register('current_order', $order);
        return $order;
    }
    /**
     * Cancel order
     */
   public function cancelAction($id)
    {
        if ($order = $this->_initOrder($id))
        {
            try
            {
                if($order->canCancel())
                {
                    $order->cancel()->save();
                    return true;
                }
                else{
                    return "Error: Order cannot be Cancelled. Please cancel it manually";
                }
            }catch(\Exception $e){
                return "Error: Order cannot be Cancelled. Please cancel it manually";
            }catch(\Exception $e){
                return "Error: Order cannot be Cancelled. Please cancel it manually";
            }
        }
    }
    /**
     * Hold order
     */
   public function holdAction($id)
    {
        if ($order = $this->_initOrder($id))
        {
            try
            {
                if($order->canHold())
                {
                    $order->hold()->save();
                    return true;
                }else{
                    return "Error: Order cannot be Holded. Please review it manually";
                }
            }catch(Mage_Core_Exception $e){
                return "Error: Order cannot be Holded. Please review it manually";
            }catch (Exception $e){
                return "Error: Order cannot be Holded. Please review it manually";
            }
        }
    }
    /**
     * Unhold order
     */
   public function unholdAction($id)
    {
        if ($order = $this->_initOrder($id))
        {
            try
            {
                if($order->canUnhold())
                {
                    $order->unhold()->save();
                    return true;
                }else{
                    return "Error: Order cannot be Un Holded. Please review it manually";
                }
            }catch (Mage_Core_Exception $e){
                return "Error: Order cannot be Un Holded. Please review it manually";
            }catch (Exception $e){
                return "Error: Order cannot be Un Holded. Please review it manually";
            }
        }
    }

public function convertdateformate($shippedOn)
{

$shippedOnAry=explode(" ",$shippedOn);
$shippedOnDate=explode("-",$shippedOnAry['0']);
$shippedOn=$shippedOnDate['1']."-".$shippedOnDate['2']."-".$shippedOnDate['0'];
return $shippedOn;
}	
public function writeFile($str)
{

	$fp = fopen('error_log.txt', 'w+');
	//fwrite($fp, "\n ".date('d/m/Y h:i:s');
	fwrite($fp, $str);
	fclose($fp);

}	
public function getCcTypeName($ccType)
   {
        return isset($this->types[$ccType]) ? $this->types[$ccType] : false;
   	}	
   
   
   function addOrderShipment($username,$password,$data,$storeid,$download_option_as_item)
    {
        global $objectManager;
        //global $download_option_as_item;
        
        $storeId=$this->getDefaultStore($storeid);		
        $status = $this->CheckUser($username,$password);
        $emailAlert = "N";
        if($status!="0")
        {		
            return $status;
        }
        $Orders_obj = new WG_OrdersShipment();		
        $requestArray = $data;
        //print_r($requestArray);die();
        if (!is_array($requestArray)) {
            $Orders_obj->setStatusCode('9997');
            $Orders_obj->setStatusMessage('Unknown request or request not in proper format');				
            return $this->response($Items->getItems());
         }

        if (count($requestArray) == 0) {
            $Orders_obj->setStatusCode('9996');
            $Orders_obj->setStatusMessage('REQUEST tag(s) doesnt have correct input format');
            return $this->response($Items->getItems());
        }

        if(count($requestArray) == 0) {
            $no_orders = true;
        }else {
            $no_orders = false;
        }
        
        $Orders_obj->setStatusCode('0');
        $Orders_obj->setStatusMessage('All Ok');
        
        //$current_order = $objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId($order_id);
        //$convertor = $objectManager->get('\Magento\Sales\Model\Convert\Order');
        //$_shipment = $convertor->toShipment($current_order);

        //$attributeInfo = $objectManager->get('\Magento\EAV\Model\Entity\Attribute')->getResourceCollection();
        //$attributeInfo = $attributeInfo->setCodeFilter('ecc')->getFirstItem();
        //$attributeInfo = $attributeInfo->getData();	
		
        foreach($requestArray as $orders)
        {
            foreach($orders as $order)
            {
                $order_id = $order['OrderId'];
                $order_no = $order['OrderNo'];

                $Order_obj = new WG_OrderShipment();
                $Order_obj->setOrderId($order['OrderId']);
                $Order_obj->setOrderNo($order['OrderNo']);
                if($order['IsNotifyCustomer'] || $order['IsNotifyCustomer']=='Y')
                {
                    $order['IsNotifyCustomer']="Y";
                }
                $emailAlert = $order['IsNotifyCustomer'];
						
                $orders1 = $this->_UpdateOrdersShippingStatus($order_no,$storeId);
                $orders_array=$orders1->toArray();
                
                // Updated for 1.4.1.0
                if(array_key_exists('items',$orders_array))
                    $orders_array_w =$orders_array['items'];
                else
                    $orders_array_w =$orders_array;

                foreach($orders_array_w as $orders_el)
                {
                    $orderObj = $objectManager->get('\Magento\Sales\Model\Order');
                    $current_order = $orderObj->load($orders_el['entity_id']);
                    $item_array = $this->getorderitems($orders_el["entity_id"],"");
                    $item_array = $item_array['items'];
                    $product_type_bundle = false;
                    $product_type_configurable = false;
                    $attributeValue_yes = false;
									
                    foreach($item_array as $item)
                    {
                        $product_type=$item['product_type'];

                        if($product_type=="bundle")
                        {
                            $product_type_bundle=true;
                        }
                        if($product_type=="configurable")
                        {
                            $product_type_configurable=true;
                        }

                        $product_id=$item['product_id'];

                        //if(isset($attributeInfo) && !empty($attributeInfo))
                        //$attributeValue = $objectManager->get('\Magento\Catalog\Model\Product')->load($product_id)->getAttributeText('ecc');
                        

                        if(isset($attributeValue) && $attributeValue=='Yes')
                        {
                            $attributeValue_yes=true;
                        }
                    }
		
                    if(!$current_order->canShip() || $attributeValue_yes)
                    {
                        $result = "Order cannot be shipped.Either its shipment is already created or there is other problem. Please review manually.";
                        foreach($order['Shipments'] as $shipment)
                        {
                            $ship_id=$shipment['ShipmentID'];
                        }
                        $emailAlert = "N";
                        $ShipmentObj = new WG_Shipment();
                        $ShipmentObj->setShipmentID($ship_id);
                        $ShipmentObj->setStatus($result);
                        $Order_obj->setShipments($ShipmentObj->getShipment());

                    }elseif($download_option_as_item && ($product_type_bundle || $product_type_configurable))
                    {
                        $result = "Download option as item cannot be shipped.Please review manually.";
                        $emailAlert = "N";
                        foreach($order['Shipments'] as $shipment)
                        {
                            $ship_id=$shipment['ShipmentID'];
                        }
                        $ShipmentObj = new WG_Shipment();
                        $ShipmentObj->setShipmentID($ship_id);
                        $ShipmentObj->setStatus($result);
                        $Order_obj->setShipments($ShipmentObj->getShipment());

                    }
                    elseif($current_order->canShip())
                    {
                        $current_order->setTotal_paid($orders_el['grand_total']);
                        $current_order->setBase_total_paid($orders_el['base_grand_total']);
                        $current_order->setTotal_invoiced($orders_el['grand_total']);
                        $current_order->setBase_total_invoiced($orders_el['base_grand_total']);
                        $current_order->setDiscount_invoiced($orders_el['discount_amount']);
                        $current_order->setBase_discount_invoiced($orders_el['base_discount_amount']);
                        $current_order->setSubtotal_invoiced($orders_el['subtotal']);
                        $current_order->setTax_invoiced($orders_el['tax_amount']);
                        $current_order->setShipping_invoiced($orders_el['shipping_amount']);
                        $current_order->setBase_subtotal_invoiced($orders_el['base_subtotal']);
                        $current_order->setBase_tax_invoiced($orders_el['base_tax_amount']);
                        $current_order->setBase_shipping_invoiced($orders_el['base_shipping_amount']);
                        foreach($order['Shipments'] as $shipment)
                        {
                            $tracking_num=$shipment['TrackingNumber'];
                            $method=$shipment['Method'];
                            $carrier=$shipment['Carrier'];
                            $ship_id=$shipment['ShipmentID'];
                            $RequestOrders = array("TRACKINGNUMBER"=>$tracking_num,"SHIPPEDVIA"=>$carrier,"SERVICEUSED"=>$method);
                            
                            foreach($shipment['Items'] as $item)
                            {
                                $item_qty_shipped=$item['ItemQtyShipped'];
                                $item_name=$item['ItemName'];
                                $item_sku=$item['ItemSku'];
                                $item_id=$item['ItemID'];
                                $data['items'][$item_id] = $item_qty_shipped;
                            }//end foreach Items
												
                            if($shipment_array = $this->_initShipment($current_order,$RequestOrders,$data))
                            {												
                                $shipment_array->register();
                                #make second param true to notify customer.
                                //$shipment_array->addComment($info,true);
                                if($emailAlert=="Y")
                                {
                                    //$shipment_array->sendEmail(true);
                                    $shipment_array->setEmailSent(true);	
                                }else{
                                    //$shipment_array->sendEmail(false);
                                    $shipment_array->setEmailSent(false);	
                                }						
                                $shipment_arr = $this->_saveShipment($shipment_array);
                            }

                            $result = "Success";
                            //$ShipmentObj = new WG_Shipment();
                        }//end foreach shipment
	            }// end elseif
		}// end foreach orders_array_w
                $ShipmentObj = new WG_Shipment();
                $ShipmentObj->setShipmentID($ship_id);
                $ShipmentObj->setStatus($result);
                $Order_obj->setShipments($ShipmentObj->getShipment());
                $Orders_obj->setOrder($Order_obj->getShipments());
            }// end foreach orders
			
        }// end for each requestArray
		
        return $this->response($Orders_obj->getOrdersShipment());
    }
}

class wg_sender extends CreditmemoSender{
        
    public function __construct($_objectManager) {
        global $objectManager;
        $objectManager = $_objectManager;
        $templateContainer = $objectManager->create('Magento\Sales\Model\Order\Email\Container\Template');
        $identityContainer = $objectManager->create('Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity');
        $senderBuilderFactory = $objectManager->create('Magento\Sales\Model\Order\Email\SenderBuilderFactory');
        $logger = $objectManager->create('Psr\Log\LoggerInterface');
        $addressRenderer = $objectManager->create('Magento\Sales\Model\Order\Address\Renderer');
        $paymentHelper = $objectManager->create('Magento\Payment\Helper\Data');
        $creditmemoResource = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\Creditmemo');
        $globalConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $eventManager = $objectManager->create('Magento\Framework\Event\ManagerInterface');

        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer, $paymentHelper, $creditmemoResource, $globalConfig,
                $eventManager);
    }
                       
                       
    public function wg_send($creditmemo, $forceSyncMode = false)
    {
        global $objectManager;
        $creditmemo->setSendEmail(true);

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            $order = $creditmemo->getOrder();
            
            $transport = [
                'order' => $order,
                'creditmemo' => $creditmemo,
                'comment' => $creditmemo->getCustomerNoteNotify() ? $creditmemo->getCustomerNote() : '',
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->wg_getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            ];

            $this->eventManager->dispatch(
                'email_creditmemo_set_template_vars_before',
                ['sender' => $this, 'transport' => $transport]
            );

            $this->templateContainer->setTemplateVars($transport);

            if ($this->checkAndSend($order)) {
                $creditmemo->setEmailSent(true);
                $this->creditmemoResource->saveAttribute($creditmemo, ['send_email', 'email_sent']);
                return true;
            }
        }

        $this->creditmemoResource->saveAttribute($creditmemo, 'send_email');

        return false;
    }
    
    public function wg_getPaymentHtml($order){
        global $objectManager;
        $templateFileName = "/home/giliwebstore/domains/magento.giliwebstore.com/public_html/magento202/vendor/magento/module-offline-payments/view/frontend/templates/info/checkmo.phtml";
        $paymentHelper = $objectManager->get('Magento\Payment\Helper\Data');
        $htmlData = $paymentHelper->getInfoBlock($order->getPayment());
        $templateObj = $objectManager->get('Magento\Framework\View\Element\Template');
        $templateEngine = $objectManager->get('Magento\Framework\View\TemplateEngine\Php');
        $html = $templateEngine->render($htmlData, $templateFileName);
        return($html);
    }
    
}
?>