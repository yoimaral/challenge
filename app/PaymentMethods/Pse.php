<?php

namespace App\PaymentMethods;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use PlacetoPay\PSE\PSE as WcPse;
use PlacetoPay\PSE\Struct\Bank;

class Pse implements PaymentMethodInterface
{
    private $endpointBase;
    private $login;
    private $secretKey;
    private const PSE_APPROVED = 'OK';
    private const PSE_REJECTED = 'NOT_AUTHORIZED';
    private const PSE_EXPIRED = 'FAILED';
    private const PSE_PENDING = 'PENDING';

    public function __construct()
    {
        $this->endpointBase = config('services.placetopay.endpoint_base.pse');
        $this->login = config('services.placetopay.login');
        $this->secretKey = config('services.placetopay.secret_key');
    }

    public function createPayment(Payment $payment): RedirectResponse
    {
        $reference = $payment->id;
        $description = $payment->description;
        $totalAmount = (float)$payment->amount;
        $pseResponse = $this->createTransaction($reference, $description, $totalAmount);

        $payment->reference = $pseResponse['transactionID'];
        $payment->process_url = $pseResponse['bankURL'];
        $payment->save();

        return redirect($payment->process_url);
    }

    private function createTransaction(string $reference, string $description, float $totalAmount)
    {
        $pse = new WcPse($this->login, $this->secretKey);
        $transaction = $pse->createTransaction();

        $transaction->setBankCode(request()->get('bank_code'));
        $transaction->setBankInterface(Bank::PERSONAL_INTERFACE);
        $transaction->setReturnURL(route('payments.show', $reference));
        $transaction->setReference($reference);
        $transaction->setDescription($description);
        $transaction->setLanguage('ES');
        $transaction->setCurrency('COP');
        $transaction->setTotalAmount($totalAmount);
        $transaction->setTaxAmount(0.0);
        $transaction->setDevolutionBase(0.0);
        $transaction->setTipAmount(0.0);
        $transaction->setPayer(array(
            'documentType' => 'CC',
            'document' => '123456789',
            'firstName' => 'Yoimar',
            'lastName' => 'Lozano',
            'company' => 'Evertec',
            'emailAddress' => 'yoimar.lozano@evertec.com',
            'address' => 'Carrera 50 No. 100 – 112',
            'city' => 'Medellín',
            'province' => 'Antioquia',
            'country' => 'CO',
            'phone' => '+57 (4) 222 2222',
            'mobile' => '+57 (4) 300 555 5555',
        ));
        $transaction->setIpAddress(request()->ip());
        $transaction->addAdditionalData('name', 'value');

        return $transaction->send()->toArray();
    }

    public function retryPayment(Payment $payment): RedirectResponse
    {
        return redirect($payment->process_url);
    }

    private function getPaymentInformation(string $reference)
    {
        $pse = new WcPse($this->login, $this->secretKey);

        return $pse->getTransactionInformation($reference);
    }

    public function status(string $reference): string
    {
        $response = $this->getPaymentInformation($reference)->toArray();

        switch ($response['transactionState']) {
            case self::PSE_APPROVED:
                return Payment::STATUSES['APPROVED'];
            case self::PSE_REJECTED:
            case self::PSE_EXPIRED:
                return Payment::STATUSES['REJECTED'];
            case self::PSE_PENDING:
                return Payment::STATUSES['PENDING'];
            default:
                return Payment::STATUSES['IN PROCESS'];
        }
    }
}
