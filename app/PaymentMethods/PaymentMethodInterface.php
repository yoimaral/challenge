<?php

namespace App\PaymentMethods;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;

interface PaymentMethodInterface
{
    public function createPayment(Payment $payment): RedirectResponse;

    public function retryPayment(Payment $payment): RedirectResponse;

    public function status(string $reference): string;
}
