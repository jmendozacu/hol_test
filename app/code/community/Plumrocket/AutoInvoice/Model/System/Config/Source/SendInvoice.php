<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
class Plumrocket_AutoInvoice_Model_System_Config_Source_SendInvoice
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		$_helper = Mage::helper('sales');
        return array(
            array('value' => 0, 'label'=> $_helper->__('After order is created')),
            array('value' => 1, 'label'=> $_helper->__('After order is shipped')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
		$values = array();
		foreach($this->toOptionArray() as $data){
			$values[$data['value']] = $data['label'];
		}
        
        return $values;
    }

}
