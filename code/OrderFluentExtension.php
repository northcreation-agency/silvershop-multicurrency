<?php

/*
 * Stores Locale, Currency and Domain details into an object
 */

class OrderFluentExtension extends DataExtension
{
    private static $db = [
        'Locale' => 'Varchar(5)',
        'Currency' => 'Varchar(3)',
        'Domain' => 'Varchar(100)',
    ];

    public function onBeforeWrite()
    {
        $currentDomain = Director::protocolAndHost();
        // Protection agains multi-domain orders
        $domain = $this->owner->getField('Domain');
        if ($domain && $domain !== $currentDomain) {
            return user_error('ERR: You unable to order products from different domain.');
        }

        $this->owner->setField('Locale', Fluent::current_locale());
        $this->owner->setField('Domain', $currentDomain);
        $this->owner->setField('Currency', ProductMultiCurrency::get_current_currency());
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->insertAfter(
            LiteralField::create(
                'Localization',
                '<div class="field"><table class="ss-gridfield-table fluent">'
                .'<thead><tr class="title"><th colspan="2"><h2>Localization</h2></th></tr></thead>'
                .'<tbody>'
                .'<tr class="ss-gridfield-item"><td>Locale</td><td>'.$this->owner->getField('Locale').'</td></tr>'
                .'<tr class="ss-gridfield-item"><td>Currency</td><td>'.$this->owner->getField('Currency').'</td></tr>'
                .'<tr class="ss-gridfield-item"><td>Domain</td><td>'.$this->owner->getField('Domain').'</td></tr>'
                .'</tbody>'
                .'<tfoot><tr><td class="bottom-all" colspan="5"></td></tr></tfoot>'
                .'</table></div>'
            ),
            'Status'
        );
    }
}
