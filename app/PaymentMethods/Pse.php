<?php

namespace App\PaymentMethods;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;

class Pse implements PaymentMethodInterface
{
    private $endpointBase;
    private $login;
    private $secretKey;
    private const PSE_APPROVED = '00';
    private const PSE_REJECTED = '?C';
    private const PSE_EXPIRED = 'EX';
    private const PSE_PENDING = 'PT';

    public function __construct()
    {
        $this->endpointBase = config('services.placetopay.endpoint_base.pse');
        $this->login = config('services.placetopay.login');
        $this->secretKey = config('services.placetopay.secret_key');
    }

    private function getCredentials(): array
    {
        if (function_exists('random_bytes')) {
            $nonce = bin2hex(random_bytes(16));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $nonce = bin2hex(openssl_random_pseudo_bytes(16));
        } else {
            $nonce = mt_rand();
        }

        $nonceBase64 = base64_encode($nonce);
        $seed = date('c');
        $secretKey = $this->secretKey;
        $tranKey = base64_encode(sha1($nonce . $seed . $secretKey, true));

        return [
            'login' => $this->login,
            'tranKey' => $tranKey,
            'nonce' => $nonceBase64,
            'seed' => $seed,
        ];
    }

    public function createPayment(Payment $payment): RedirectResponse
    {
        $reference = $payment->id;
        $description = $payment->description;
        $total = $payment->amount;
        $pseResponse = $this->createRequest($reference, $description, $total);
        dd($pseResponse);
        $payment->reference = $pseResponse['requestId'];
        $payment->process_url = $pseResponse['processUrl'];
        $payment->save();

        return redirect($payment->process_url);
    }

    private function createRequest(string $reference, string $description, int $total): array
    {
        $response = Http::post($this->endpointBase, [

            'auth' => $this->getCredentials(),
            'transaction' => [
                'bankCode' => '1007',
                'bankInterface' => '0',
                'returnUrl' => route('payments.show', $reference),
                'reference' => $reference,
                'description' => $description,
                'language' => 'ES',
                'currency' => 'COP',
                'totalAmount' => $total,
                'taxAmount' => '0.32',
                'devolutionBase' => '0',
                'tipAmount' => '0',

                'payer' => [
                    'documentType' => 'CC',
                    'document' => '1234567890',
                    'firstName' => 'Juan',
                    'lastName' => 'Higuita',
                    'company' => 'S4',
                    'emailAddress' => 'andres@gmail.com',
                    'address' => 'Carrera 77',
                    'city' => 'Medellin',
                    'province' => 'Antioquia',
                    'country' => 'CO',
                    'phone' => '4623409',
                    'mobile' => '3138990987',
                    'postalCode' => '080001',
                ],

                'buyer' => [
                    'documentType' => 'CC',
                    'document' => '1029003999',
                    'firstName' => 'andres',
                    'lastName' => 'Higuita',
                    'company' => 'S.A.S',
                    'emailAddress' => 'andresh@gmail.com',
                    'address' => 'Calle 18',
                    'city' => 'Medellin',
                    'province' => 'Antioquia',
                    'country' => 'CO',
                    'phone' => '5772687',
                    'mobile' => '311733657',
                    'postalCode' => '123900',
                ],

                'shipping' => [
                    'documentType' => 'CC',
                    'document' => '12322222',
                    'firstName' => 'andres',
                    'lastName' => 'Ruiz',
                    'company' => 'Inde',
                    'emailAddress' => 'andres@hotmail.com',
                    'address' => 'calle 10',
                    'city' => 'Medellin',
                    'province' => 'Antioquia',
                    'country' => 'CO',
                    'phone' => '32445211',
                    'mobile' => '1329877777',
                    'postalCode' => '12343',
                ],
                'ipAddress' => request()->ip(),
                'userAgent' => request()->header('User-agent'),
                'additionalData' => '',

            ],
            ///'expiration' => date('c', strtotime('+7 minute')),
        ]);
        dd($response);
        return $response->json();
    }

    public function retryPayment(Payment $payment): RedirectResponse
    {
        return $payment->process_url;
    }

    private function getPaymentInformation(string $reference): array
    {
        $getResponse = Http::post($this->endpointBase . '/api/session/' . $reference, [
            'auth' => $this->getCredentials()
        ]);

        return $getResponse->json();
    }

    public function status(string $reference): string
    {
        $response = $this->getPaymentInformation($reference);

        switch ($response['status']['reason']) {
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
