<?php

/**
 * Product:       Xtento_StockImport (2.1.5)
 * ID:            cIXIdZ5E8uNO9ltV9qw9FYc03wnFMytXL2xZanOHjQk=
 * Packaged:      2014-05-28T11:25:32+00:00
 * Last Modified: 2013-06-26T18:01:00+02:00
 * File:          app/code/local/Xtento/StockImport/Block/Adminhtml/Source/Grid/Renderer/Result.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_StockImport_Block_Adminhtml_Source_Grid_Renderer_Result extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if ($row->getLastResult() === NULL) {
            return '<span class="grid-severity-major"><span>' . Mage::helper('xtento_stockimport')->__('No Result') . '</span></span>';
        } else if ($row->getLastResult() == 0) {
            return '<span class="grid-severity-critical"><span>' . Mage::helper('xtento_stockimport')->__('Failed') . '</span></span>';
        } else if ($row->getLastResult() == 1) {
            return '<span class="grid-severity-notice"><span>' . Mage::helper('xtento_stockimport')->__('Success') . '</span></span>';
        }
    }
}