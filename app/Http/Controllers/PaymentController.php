<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePaymentRequest;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\PaymentMethods\PaymentMethodFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function index(): View
    {
        return view('payments.index', [
            'payments' => Payment::with('paymentMethod')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('payments.create', [
            'paymentMethods' => PaymentMethod::all()
        ]);
    }

    public function store(SavePaymentRequest $request): RedirectResponse
    {
        $payment = Payment::create($request->validated());
        $paymentMethod = PaymentMethodFactory::create((int)$request->payment_method_id);

        return $paymentMethod->createPayment($payment);
    }

    public function show(Payment $payment)
    {
        $this->updatePaymentStatus($payment);

        return view('payments.show', [
            'payment' => $payment
        ]);
    }

    private function updatePaymentStatus(Payment $payment): void
    {
        $previousPaymentStatus = $payment->status;
        if (($previousPaymentStatus === Payment::STATUSES['IN PROCESS'])
            || ($previousPaymentStatus === Payment::STATUSES['PENDING'])
        ) {
            $paymentMethod = PaymentMethodFactory::create($payment->payment_method_id);
            $currentPaymentStatus = $paymentMethod->status($payment->reference);
            $this->comparePaymentStatuses($payment, $previousPaymentStatus, $currentPaymentStatus);
        }
    }

    private function comparePaymentStatuses(
        Payment $payment,
        string $previousPaymentStatus,
        string $currentPaymentStatus
    ): void {
        if ($previousPaymentStatus !== $currentPaymentStatus) {
            $payment->status = $currentPaymentStatus;
            $payment->save();
        }
    }

    public function retry(Payment $payment): RedirectResponse
    {
        $paymentMethod = PaymentMethodFactory::create((int)$payment->payment_method_id);
        dd($paymentMethod);
        if ($payment->status === Payment::STATUSES['IN PROCESS']) {
            return $paymentMethod->retryPayment($payment);
        }

        return $paymentMethod->createPayment($payment);
    }
}
