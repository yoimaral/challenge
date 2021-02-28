<?php

namespace App\PaymentMethods;

use Illuminate\Validation\ValidationException;

class PaymentMethodFactory
{
    protected const WEBCHECKOUT = 1;
    protected const PSE = 2;

    public static function create(int $paymentMethodId): PaymentMethodInterface
    {
        switch ($paymentMethodId) {
            case self::WEBCHECKOUT:
                return new Webcheckout();
            default:
                throw ValidationException::withMessages(['La plataforma selecionada no está en la configuración']);
        }
    }
}
