<?php
/**
 * Observer for core events handling
 *
 */
class EcomDev_ScheduledProduct_Model_Observer
{
    /**
     * Observes event 'adminhtml_catalog_product_edit_prepare_form'
     * and adds custom format for date input
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function observeProductEditFortInitialization(Varien_Event_Observer $observer)
    {
        $form = $observer->getEvent()->getForm();
        $elementsToCheck = array(
            EcomDev_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_ACTIVATION_DATE,
            EcomDev_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE
        );
        foreach ($elementsToCheck as $elementCode) {
            $element = $form->getElement($elementCode);
            if (!$element) {
                continue;
            }
            $element->setFormat(
                Mage::app()->getLocale()->getDateTimeFormat(
                    Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
                )
            );
            $element->setTime(true);
        }
    }
	 /**
     * Cron job for processing of scheduled products
     *
     * @return void
     */
    public function cronProcessScheduledProducts()
    {
        $currentDate = Mage::app()->getLocale()->date()->toString(
           Varien_Date::DATETIME_INTERNAL_FORMAT
        );
		echo Mage::getModel('core/date')->date('Y-m-d H:i:s') .'<br>';
		echo $currentDate.'<br>',PHP_EOL;
		echo Mage_Catalog_Model_Product_Status::STATUS_DISABLED.'<br>',PHP_EOL;
		echo Mage_Catalog_Model_Product_Status::STATUS_ENABLED.'<br>',PHP_EOL;
		echo EcomDev_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE .'<br>',PHP_EOL;
        $productModel = Mage::getModel('catalog/product');
        /* @var $expiredProductsCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        // Prepare collection of scheduled for expiry but haven't yet deactivated products
        $expiredProductsCollection = $productModel->getCollection()
            // Add filter for expired but products haven't yet deactivated
           /* ->addFieldToFilter(
                EcomDev_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE,
                array(
                //     'nnull' => 1,  // Specifies that date shouldn't be empty
                   'lteq' => $currentDate // And lower than current date
                )
            ) */
			
		   ->addFieldToFilter(
                EcomDev_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE,
                array(
                  //   array('nnull' => 1), Specifies that date shouldn't be empty
                    array('lteq' => $currentDate) // And greater than current date
                )
            )
           ->addFieldToFilter(
                EcomDev_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_STATUS,
                Mage_Catalog_Model_Product_Status::STATUS_ENABLED
            )
		/**/	
		;
        // Retrieve product ids for deactivation ecomdev_expiry_date
		foreach( $expiredProductsCollection as $xx){
			echo $xx->getId().' - '.$xx->getData('ecomdev_expiry_date').' - '.$currentDate.'<br>',PHP_EOL;
			if( $xx->getData('ecomdev_expiry_date')!=''  ){
				 $expiredProductIds[]=$xx->getId();
				 if( $xx->getData('ecomdev_expiry_date')<= $currentDate ){
				echo $xx->getId().' - '.$xx->getData('ecomdev_expiry_date').' - '.$currentDate
				.' - '.$xx->getStatus()
				.'<br>',PHP_EOL;
				 }
				
				}
			
		
			}
	/*		
     $expiredProductIds = $expiredProductsCollection->getAllIds();*/
      
     if ($expiredProductIds) {
		 print_r($expiredProductIds); 	
          Mage::getSingleton('catalog/product_action')
                ->updateAttributes(
                         $expiredProductIds,
                         array('status' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED),
                         Mage_Core_Model_App::ADMIN_STORE_ID
                );
			  /*	*/
    }
	unset($expiredProductsCollection);
        /* @var $expiredProductsCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        // Prepare collection of scheduled for activation but haven't yet activated products
        /* $activatedProductsCollection = $productModel->getCollection()
         ->addFieldToFilter(
                EcomDev_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_ACTIVATION_DATE,
                array(
                    'nnull' => 1, // Specifies that date shouldn't be empty
                    'lteq' => $currentDate // And lower than current date
                )
            )
			
            // Exclude expired products
            ->addFieldToFilter(
                EcomDev_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_EXPIRY_DATE,
                array(
                    array('nnull' => 1), // Specifies that date shouldn't be empty
                    array('gt' => $currentDate) // And greater than current date
                )
            )
            ->addFieldToFilter(
                EcomDev_ScheduledProduct_Model_Attribute_Backend_Datetime::ATTRIBUTE_STATUS,
                Mage_Catalog_Model_Product_Status::STATUS_DISABLED
            );
			
		//	print_r($activatedProductsCollection);
        // Retrieve product ids for activation
        $activatedProductIds = $activatedProductsCollection->getAllIds();
        unset($activatedProductsCollection);
        if ($activatedProductIds) {
            Mage::getSingleton('catalog/product_action')
                ->updateAttributes(
                         $activatedProductIds,
                         array('status' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED),
                         Mage_Core_Model_App::ADMIN_STORE_ID
                );
				//print_r($activatedProductIds);
        } */ 
    }
}