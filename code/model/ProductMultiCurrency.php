<?php

/**
 * Stupid class name because no namespacing in this SS version yet
 */
class ProductMultiCurrency extends DataObject
{
    private static $singular_name = "Currency";
    private static $plural_name = "Currencies";

    /**
     * List of database fields. {@link DataObject::$db}
     *
     * @var array
     */
    private static $db = array(
        'Title' => 'Varchar(10)',
        'Value' => 'Money(19,4)',
        //'Currency' => 'Varchar(3)', // "USD" ISO 4217
    );

    /**
     * List of one-to-one relationships. {@link DataObject::$has_one}
     *
     * @var array
     */
    private static $has_one = array(
        'Product' => 'Product'
    );

    /**
     * @var array
     */
    private static $summary_fields = [
        'Title', 'Value'
    ];

    public function validate() {
        $result = parent::validate();

        // only allow one price per currency
        $currency = DataObject::get_one("ProductMultiCurrency", ["ProductID" => $this->ProductID, "ValueCurrency" => $this->getField("Value")->getCurrency()]);
        if($currency){
            // TODO this error also occurs on first create.. but item is still created..
            //$result->error("This currency has already been created (" . $this->getField("Value")->getCurrency() . ")");
        }

        return $result;
    }

    /**
     * Returns a FieldList with which to create the main editing form. {@link DataObject::getCMSFields()}
     *
     * @return FieldList The fields to be displayed in the CMS.
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $f = [
            MoneyField::create('Value')->setAllowedCurrencies(\Silvershop\MultiCurrency\Helper::get_currencies())
        ];

        $fields->addFieldsToTab('Root.Main', $f);

        return $fields;
    }

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if(!$this->Title){
            $this->Title = $this->dbObject("Value")->getCurrency();
        }
    }

    /**
     * Gets ProductMultiCurrency with Shops default currency. If none found, create one.
     *
     * @return ProductMultiCurrency
     */
    public static function defaultCurrencyObject()
    {
        $default = self::config()->get("default_currency");

        $obj = DataObject::get_one("ProductMultiCurrency", ["ValueCurrency" => $default]);

        if(!$obj){
            $obj = self::createDefaultCurrencyObject();
        }

        return $obj;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->dbObject('Value')->getCurrency();
    }

    /**
     * Create ProductMultiCurrency with Shop defauly currency
     *
     * @return ProductMultiCurrency
     */
    protected static function createDefaultCurrencyObject()
    {
        $default = self::config()->get("default_currency");

        $obj = self::create();
        $obj->setField("ValueCurrency", $default);
        $obj->write();

        return $obj;
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        $default = self::config()->get("default_currency");

        if(!self::get()->filter(["ValueCurrency" => $default])->first()){
            $obj = self::createDefaultCurrencyObject();
            DB::alteration_message("created Currency object " . $obj->getTitle(), "created");
        }

    }

}