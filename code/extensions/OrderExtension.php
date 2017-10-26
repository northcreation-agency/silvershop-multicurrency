<?php

namespace SilverShop\MultiCurrency\Extensions;

/*
 * Stores Currency and Domain details into an object
 */

use Silvershop\MultiCurrency\Helper;

class OrderExtension extends \DataExtension
{
    private static $db = [
        'StoredCurrency'   => 'Varchar(3)', // cant be "Currency" because of core method
        'Domain'   => 'Varchar(100)',
    ];

    public function onBeforeWrite()
    {
        $currentDomain = \Director::protocolAndHost();
        // Protection agains multi-domain orders
        $domain = $this->owner->getField('Domain');
        if ($domain && $domain !== $currentDomain) {
            return user_error('ERR: You unable to order products from different domain.');
        }

        $this->owner->setField('Domain', $currentDomain);

        $this->owner->setField('StoredCurrency', Helper::get_current_currency());
    }

    public function updateCMSFields(\FieldList $fields)
    {
        $data = [
            'Domain'   => $this->owner->getField('Domain'),
            'Currency'   => $this->owner->getField('StoredCurrency'),
        ];

        $f = \LiteralField::create('Localization', $this->owner->customise($data)->renderWith('Order_Localization'));

        $fields->insertAfter('Addresses', $f);
    }

    /**
     * @return string
     */
    public function getStoredCurrency()
    {
        $currency = $this->owner->getField("StoredCurrency");

        if(!$currency){
            $currency = \ProductMultiCurrency::defaultCurrencyObject()->getCurrency();
        }

        return $currency;
    }
}
