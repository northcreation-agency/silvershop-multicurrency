<?php

namespace SilverShop\MultiCurrency\Checkout;

use SilverStripe\Omnipay\GatewayInfo;

/**
 * Created by PhpStorm.
 * User: sanderhagenaars
 * Date: 25/10/2017
 * Time: 21.52
 */
class OrderProcessor extends \OrderProcessor
{
    /**
     * Create a new payment for an order
     */
    public function createPayment($gateway)
    {
        if (!GatewayInfo::isSupported($gateway)) {
            $this->error(
                _t(
                    "PaymentProcessor.InvalidGateway",
                    "`{gateway}` isn't a valid payment gateway.",
                    'gateway is the name of the payment gateway',
                    array('gateway' => $gateway)
                )
            );
            return false;
        }
        if (!$this->order->canPay(\Member::currentUser())) {
            $this->error(_t("PaymentProcessor.CantPay", "Order can't be paid for."));
            return false;
        }

        $payment = \Payment::create()
            ->init($gateway, $this->order->TotalOutstanding(true), $this->order->getStoredCurrency());
        $this->order->Payments()->add($payment);

        return $payment;
    }
}