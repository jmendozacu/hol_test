<?php
/**
 * Validatie geextend om controle expression in functie te disablen.
 *
 * SalesRule Validator Model
 *
 * Allows dispatching before and after events for each controller action
 *
 * @category   Mage
 * @package    Mage_SalesRule
 */

class BD_NegativeDiscount_Model_Validator extends Mage_SalesRule_Model_Validator
{
 /**
     * Quote item discount calculation process
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_SalesRule_Model_Validator
     */
    public function process(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
		
        $item->setDiscountAmount(0);
        $item->setBaseDiscountAmount(0);
        $item->setDiscountPercent(0);
        $quote      = $item->getQuote();
        $address    = $this->_getAddress($item);

        //Clearing applied rule ids for quote and address
        if ($this->_isFirstTimeProcessRun !== true){
            $this->_isFirstTimeProcessRun = true;
            $quote->setAppliedRuleIds('');
            $address->setAppliedRuleIds('');
        }

        $itemPrice  = $this->_getItemPrice($item);
        $baseItemPrice = $this->_getItemBasePrice($item);

        if ($itemPrice <= 0) {
            return $this;
        }

        $appliedRuleIds = array();
        foreach ($this->_getRules() as $rule) {
            /* @var $rule Mage_SalesRule_Model_Rule */
            if (!$this->_canProcessRule($rule, $address)) {
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                continue;
            }

            $qty = $this->_getItemQty($item, $rule);
            $rulePercent = min(100, $rule->getDiscountAmount());

            $discountAmount = 0;
            $baseDiscountAmount = 0;
            switch ($rule->getSimpleAction()) {
                case Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION:
                    $rulePercent = max(0, 100-$rule->getDiscountAmount());
                    //no break;
                case Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION:
                    $step = $rule->getDiscountStep();
                    if ($step) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $discountAmount    = ($qty*$itemPrice - $item->getDiscountAmount()) * $rulePercent/100;
                    $baseDiscountAmount= ($qty*$baseItemPrice - $item->getBaseDiscountAmount()) * $rulePercent/100;

                    if (!$rule->getDiscountQty() || $rule->getDiscountQty()>$qty) {
                        $discountPercent = min(100, $item->getDiscountPercent()+$rulePercent);
                        $item->setDiscountPercent($discountPercent);
                    }
                    break;
                case Mage_SalesRule_Model_Rule::TO_FIXED_ACTION:
                    $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount    = $qty*($itemPrice-$quoteAmount);
                    $baseDiscountAmount= $qty*($baseItemPrice-$rule->getDiscountAmount());
                    break;

                case Mage_SalesRule_Model_Rule::BY_FIXED_ACTION:
                    $step = $rule->getDiscountStep();
                    if ($step) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $quoteAmount        = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount     = $qty*$quoteAmount;
                    $baseDiscountAmount = $qty*$rule->getDiscountAmount();
                    break;

                case Mage_SalesRule_Model_Rule::CART_FIXED_ACTION:
                    if (empty($this->_rulesItemTotals[$rule->getId()])) {
                        Mage::throwException(Mage::helper('salesrule')->__('Item totals are not set for rule.'));
                    }
                    $cartRules = $address->getCartFixedRules();
                    if (!isset($cartRules[$rule->getId()])) {
                        $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                    }

                    if ($cartRules[$rule->getId()] < 0) {
                        if (1 >= $this->_rulesItemTotals[$rule->getId()]['items_count']) {
                            $quoteAmount = $quote->getStore()->convertPrice($cartRules[$rule->getId()]);

                            $discountAmount = min($itemPrice * $qty, $quoteAmount);
                            $baseDiscountAmount = min($baseItemPrice * $qty, $cartRules[$rule->getId()]);
                        } else {
                            $discountRate = $baseItemPrice * $qty / $this->_rulesItemTotals[$rule->getId()]['base_items_price'];
                            $maximumItemDiscount = $rule->getDiscountAmount() * $discountRate;
                            $quoteAmount = $quote->getStore()->convertPrice($maximumItemDiscount);

                            $discountAmount = min($itemPrice * $qty, $quoteAmount);
                            $baseDiscountAmount = min($baseItemPrice * $qty, $maximumItemDiscount);
                            $this->_rulesItemTotals[$rule->getId()]['items_count']--;
                        }
                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }
                    $address->setCartFixedRules($cartRules);
                    break;

                case Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION:
                    $x = $rule->getDiscountStep();
                    $y = $rule->getDiscountAmount();
                    if (!$x || $y>=$x) {
                        break;
                    }
                    $buyAndDiscountQty = $x + $y;

                    $fullRuleQtyPeriod = floor($qty / $buyAndDiscountQty);
                    $freeQty  = $qty - $fullRuleQtyPeriod * $buyAndDiscountQty;

                    $discountQty = $fullRuleQtyPeriod * $y;
                    if ($freeQty > $x) {
                         $discountQty += $freeQty - $x;
                    }

                    $discountAmount    = $discountQty * $itemPrice;
                    $baseDiscountAmount= $discountQty * $baseItemPrice;
                    break;
            }

            $result = new Varien_Object(array(
                'discount_amount'      => $discountAmount,
                'base_discount_amount' => $baseDiscountAmount,
            ));
            Mage::dispatchEvent('salesrule_validator_process', array(
                'rule'    => $rule,
                'item'    => $item,
                'address' => $address,
                'quote'   => $quote,
                'qty'     => $qty,
                'result'  => $result,
            ));

            $discountAmount = $result->getDiscountAmount();
            $baseDiscountAmount = $result->getBaseDiscountAmount();

            $percentKey = $item->getDiscountPercent();
            /**
             * Process "delta" rounding
             */
            if ($percentKey) {
                $delta      = isset($this->_roundingDeltas[$percentKey]) ? $this->_roundingDeltas[$percentKey] : 0;
                $baseDelta  = isset($this->_baseRoundingDeltas[$percentKey]) ? $this->_baseRoundingDeltas[$percentKey] : 0;
                $discountAmount+= $delta;
                $baseDiscountAmount+=$baseDelta;

                $this->_roundingDeltas[$percentKey]     = $discountAmount - $quote->getStore()->roundPrice($discountAmount);
                $this->_baseRoundingDeltas[$percentKey] = $baseDiscountAmount - $quote->getStore()->roundPrice($baseDiscountAmount);
                $discountAmount = $quote->getStore()->roundPrice($discountAmount);
                $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
            } else {
                $discountAmount     = $quote->getStore()->roundPrice($discountAmount);
                $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
            }

            /**
             * We can't use row total here because row total not include tax
             * Discount can be applied on price included tax
             */
            $discountAmount     = min($item->getDiscountAmount()+$discountAmount, $itemPrice*$qty);
            $baseDiscountAmount = min($item->getBaseDiscountAmount()+$baseDiscountAmount, $baseItemPrice*$qty);

            $item->setDiscountAmount($discountAmount);
            $item->setBaseDiscountAmount($baseDiscountAmount);

            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            $this->_maintainAddressCouponCode($address, $rule);
            $this->_addDiscountDescription($address, $rule);
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        $item->setAppliedRuleIds(join(',',$appliedRuleIds));
        $address->setAppliedRuleIds($this->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));
        return $this;
    }
}