<?php

class Magestore_Affiliatepluslevel_Helper_Data extends Mage_Core_Helper_Abstract {

    /* Edit By Jack - Check Lifetime when create order for lower affiliate */
  
    /* End Edit */
    public function getAccountLevel($accountId) {
        $tier = Mage::getModel('affiliatepluslevel/tier')->getCollection()
                ->addFieldToFilter('tier_id', $accountId)
                ->getFirstItem();
        if ($tier && $tier->getId())
            return $tier->getLevel();
        else
            return 0;
    }

    public function getToptierIdByTierId($tierId) {
        $tier = Mage::getModel('affiliatepluslevel/tier')->getCollection()
                ->addFieldToFilter('tier_id', $tierId)
                ->getFirstItem();
        if ($tier && $tier->getId())
            return $tier->getToptierId();
        else
            return NULL;
    }

    public function getAllTierIds($toptierId, $storeId) { // tier will recived commission
        return $this->getFullTierIds($toptierId, $storeId);

        $perCommissions = Mage::getStoreConfig('affiliateplus/multilevel/commission_percentage', $storeId);
        $numLevel = count(explode(',', $perCommissions));

        $toptierIds = array($toptierId);
        $allTierIds = array();

        for ($i = 0; $i < $numLevel; $i++) {
            $tiers = Mage::getModel('affiliatepluslevel/tier')->getCollection()
                    ->addFieldToFilter('toptier_id', array('in' => $toptierIds));
            $toptierIds = array();

            foreach ($tiers as $tier) {
                $toptierIds[] = $tier->getTierId();
                $allTierIds[] = $tier->getTierId();
            }
            if (!count($toptierIds))
                break;
        }
        return $allTierIds;
    }

    public function getFullTierIds($toptierId, $storeId) { // all tier unlimit level 
        $toptierIds = array($toptierId);
        $allTierIds = array();

        while (true) {
            $tiers = Mage::getModel('affiliatepluslevel/tier')->getCollection()
                    ->addFieldToFilter('toptier_id', array('in' => $toptierIds));

            $toptierIds = array();

            foreach ($tiers as $tier) {
                $toptierIds[] = $tier->getTierId();
                $allTierIds[] = $tier->getTierId();
            }
            if (!count($toptierIds))
                break;
        }

        return $allTierIds;
    }

    /* hainh edit 25-04-2014 */

    public function isPluginEnabled() {
        // Changed By Adam 28/07/2014
        if(!Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) return false;
        
        $check = Mage::getStoreConfig('affiliateplus/level/enable');
        return $check;
    }

    public function isModuleDisabled($store = null) {
        if (Mage::helper('affiliateplus/account')->accountNotLogin())
            return TRUE;
        $check = Mage::getStoreConfig('affiliateplus/level/enable', $store);
        return !$check;
    }

    /* end update */
}
