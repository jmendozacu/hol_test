<?php

/**
 * Product:       Xtento_StockImport (2.1.5)
 * ID:            cIXIdZ5E8uNO9ltV9qw9FYc03wnFMytXL2xZanOHjQk=
 * Packaged:      2014-05-28T11:25:32+00:00
 * Last Modified: 2013-07-13T13:04:04+02:00
 * File:          app/code/local/Xtento/StockImport/Model/Mysql4/Profile/Collection.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_StockImport_Model_Mysql4_Profile_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('xtento_stockimport/profile');
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->getItems() as $item) {
            $item->setData('configuration', unserialize($item->getData('configuration')));
            $item->setDataChanges(false);
        }
        return $this;
    }
}