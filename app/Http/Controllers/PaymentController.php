<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePaymentRequest;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\PaymentMethods\PaymentMethodFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use PlacetoPay\PSE\PSE as WcPse;

class PaymentController extends Controller
{
    private $login;
    private $secretKey;

    public function __construct()
    {
        $this->login = config('services.placetopay.login');
        $this->secretKey = config('services.placetopay.secret_key');
    }

    public function index(): View
    {
        return view('payments.index', [
            'payments' => Payment::with('paymentMethod')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        $pse = new WcPse($this->login, $this->secretKey);
        $bankList = $pse->getBankList()->toArray(); // Chachear

        return view('payments.create', [
            'paymentMethods' => PaymentMethod::all(),
            'bankList' => $bankList,
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
            $currentPaymentStatus = $paymentMethod->status((string)$payment->reference);
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
        if ($payment->status === Payment::STATUSES['IN PROCESS']) {
            return $paymentMethod->retryPayment($payment);
        }

        return $paymentMethod->createPayment($payment);
    }
}
