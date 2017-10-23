<?php

namespace SilverShop\MultiCurrency;

class ProductMultiCurrency extends \DataExtension
{
    /*
     * Gets selling price depending on domain name
     * or fall backs to default currency in case there's no specific domain settings
     * sets first currency of domain name if no session variable is available
     */
    public function updateSellingPrice(&$price)
    {
        $currency = self::get_current_currency();
        $price = $this->owner->{'BasePrice_'.$currency};
    }

    /*
     * Setup CMS fields
     */
    public function updateCMSFields(\FieldList $fields)
    {
        if (is_a($this->owner, 'Product')) {
            $tab = $fields->findOrMakeTab('Root.Pricing');
        } else {
            $tab = $fields;
            $fields->removeByName('Price');
        }

        $fields->removeByName('BasePrice');
        $fields->removeByName('CostPrice');

        $availableCurrencies = self::get_currencies();
        foreach ($availableCurrencies as $currency) {
            $tab->push(\CurrencyField::create('BasePrice_'.$currency, $currency));
        }
    }

    /*
     * get's current currency by host or force_domain param
     */
    public static function get_current_currency()
    {
        $force_domain = \Controller::curr()->getRequest()->requestVar('force_domain');
        $currentDomain = $force_domain ? $force_domain : strtolower($_SERVER['HTTP_HOST']);
        $domains = \Config::inst()->get('ProductMultiCurrency', 'domains');

        if (isset($domains[$currentDomain])
            && isset($domains[$currentDomain]['currencies'])
        ) {
            $selectedCurrency = \Session::get('currency');
            $currency = array_search(
                \Session::get('currency'),
                $domains[$currentDomain]['currencies']
            );

            if ($selectedCurrency && $currency) {
                $currency = $domains[$currentDomain]['currencies'][$currency];
            } else {
                $currency = $domains[$currentDomain]['currencies'][0];
            }
        } else {
            $currency = \Config::inst()->get('ProductMultiCurrency', 'default_currency');
        }

        return $currency;
    }

    /*
     * Collects currencies from domains configuration
     */
    public static function get_currencies()
    {
        $domains = \Config::inst()->get('ProductMultiCurrency', 'domains');

        // collect available currencies from domain settings
        $availableCurrencies = [];
        foreach ($domains as $domain) {
            if (is_array($domain['currencies'])) {
                $availableCurrencies = array_merge($availableCurrencies, $domain['currencies']);
            }
        }

        return $availableCurrencies;
    }

    /*
     * Creates extra pricing fields on dev/build?flush
     * Returns array of db fields
     */
    public static function get_extra_config($class, $extension, $args)
    {
        // Force all subclass DB caches to invalidate themselves since their db attribute is now expired
        \DataObject::reset();

        // collect available currencies from domain settings
        $availableCurrencies = self::get_currencies();

        // setup field types
        $pricingFields = [];
        foreach ($availableCurrencies as $currency) {
            $pricingFields['BasePrice_'.$currency] = 'Currency(19,4)';
        }

        return [
            'db' => $pricingFields,
        ];
    }
}
