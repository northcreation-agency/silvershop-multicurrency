<?php
/**
 * Created by PhpStorm.
 * User: sanderhagenaars
 * Date: 26/10/2017
 * Time: 16.04
 */

namespace Silvershop\MultiCurrency;


class Helper extends \Object
{
    /**
     * get's current currency by host or force_domain param
     *
     * @return array|mixed
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

    /**
     * Collects currencies from domains configuration or fall back to default currency
     *
     * @return array
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

        if(empty($availableCurrencies)){
            $availableCurrencies[] = \Config::inst()->get('ProductMultiCurrency', 'default_currency');
        }

        return $availableCurrencies;
    }
}