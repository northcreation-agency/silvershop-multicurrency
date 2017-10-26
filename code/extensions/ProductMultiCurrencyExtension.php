<?php

namespace SilverShop\MultiCurrency\Extensions;

use Silvershop\MultiCurrency\Helper;

class ProductMultiCurrencyExtension extends \DataExtension
{
    /**
     * List of one-to-many relationships. {@link DataObject::$has_many}
     *
     * @var array
     */
    private static $has_many = array(
        'Currencies' => 'ProductMultiCurrency'
    );

    public function updateSellingPrice(&$price)
    {
        $price = $this->owner->getMultiCurrencyPrice();
    }

    /**
     * @return float
     */
    public function getMultiCurrencyPrice()
    {
        $currency = Helper::get_current_currency();

        $obj = $this->owner->Currencies()->filter("ValueCurrency", $currency)->first();
        if(!$obj){
            $obj = \ProductMultiCurrency::defaultCurrencyObject();
        }

        $price = $obj->dbObject("Value")->getAmount();

        $this->owner->extend('updateMultiCurrencyPrice', $price);

        return $price;
    }

    public function updateCMSFields(\FieldList $fields)
    {
/*        if (is_a($this->owner, 'Product')) {
            $tab = $fields->findOrMakeTab('Root.Pricing');
        } else {
            $tab = $fields;
            $fields->removeByName('Price');
        }*/

        $fields->removeByName(['BasePrice', 'CostPrice']);

        $config = \GridFieldConfig_RecordEditor::create(10);
        $gridfield = \GridField::create('Currencies', 'Currencies', $this->owner->Currencies(), $config);
        $fields->addFieldToTab('Root.Pricing', $gridfield);
    }

    public function onAfterWrite()
    {
        // always have default currency on object
        $default = \ProductMultiCurrency::defaultCurrencyObject();
        if (!$this->owner->Currencies()->filter('ID', $default->ID)->exists()){
            $this->owner->Currencies()->add($default);
        }
    }

}
