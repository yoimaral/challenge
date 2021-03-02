<?php

namespace App\PaymentMethods;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;

class Webcheckout implements PaymentMethodInterface
{
    private $endpointBase;
    private $login;
    private $secretKey;
    private const WEBCHECKOUT_APPROVED = '00';
    private const WEBCHECKOUT_REJECTED = '?C';
    private const WEBCHECKOUT_EXPIRED = 'EX';
    private const WEBCHECKOUT_PENDING = 'PT';

    public function __construct()
    {
        $this->endpointBase = config('services.placetopay.endpoint_base.webcheckout');
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
        $webcheckoutResponse = $this->createRequest($reference, $description, $total);
        $payment->reference = $webcheckoutResponse['requestId'];
        $payment->process_url = $webcheckoutResponse['processUrl'];
        $payment->save();

        return redirect($payment->process_url);
    }

    public function retryPayment(Payment $payment): RedirectResponse
    {
        return redirect($payment->process_url);
    }

    private function createRequest(string $reference, string $description, int $total): array
    {
        $response = Http::post($this->endpointBase . '/api/session', [
            'auth' => $this->getCredentials(),
            'payment' => [
                'reference' => $reference,
                'description' => $description,
                'amount' => [
                    'currency' => 'COP',
                    'total' => $total,
                ],
            ],
            'expiration' => date('c', strtotime('+7 minute')),
            'returnUrl' => route('payments.show', $reference),
            'ipAddress' => request()->ip(),
            'userAgent' => request()->header('User-agent'),
        ]);

        return $response->json();
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
            case self::WEBCHECKOUT_APPROVED:
                return Payment::STATUSES['APPROVED'];
            case self::WEBCHECKOUT_REJECTED:
            case self::WEBCHECKOUT_EXPIRED:
                return Payment::STATUSES['REJECTED'];
            case self::WEBCHECKOUT_PENDING:
                return Payment::STATUSES['PENDING'];
            default:
                return Payment::STATUSES['IN PROCESS'];
        }
    }
}
