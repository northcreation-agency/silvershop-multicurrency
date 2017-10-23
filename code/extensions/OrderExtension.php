<?php

namespace SilverShop\MultiCurrency;

/*
 * Stores Currency and Domain details into an object
 */

class OrderExtension extends \DataExtension
{
    private static $db = [
        'Currency' => 'Varchar(3)',
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

        $this->owner->setField('Locale', \Fluent::current_locale());
        $this->owner->setField('Domain', $currentDomain);
        $this->owner->setField('Currency', ProductMultiCurrency::get_current_currency());
    }

    public function updateCMSFields(\FieldList $fields)
    {
        $domain = $this->owner->getField('Domain');
        $currency = $this->owner->getField('Currency');

        $data = [
            'Domain'   => $domain,
            'Currency' => $currency
        ];

        $f = \LiteralField::create('Localization', $this->owner->customise($data)->renderWith('Order_Localization'));

        $fields->insertAfter('Addresses', $f);
    }
}
