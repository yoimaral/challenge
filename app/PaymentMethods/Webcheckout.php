<?php

namespace App\PaymentMethods;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use App\Traits\ConsumeExternalServices;
use Illuminate\Validation\ValidationException;

class Webcheckout implements PaymentMethodInterface
{
    use ConsumeExternalServices;

    private $endpointBase;
    private $login;
    private $secretKey;
    private const WEBCHECKOUT_APPROVED = '00';
    private const WEBCHECKOUT_REJECTED = '?C';
    private const WEBCHECKOUT_EXPIRED = 'EX';
    private const WEBCHECKOUT_PENDING = 'PT';

    public function __construct()
    {
        $this->endpointBase = config('services.placetopay.endpoint_base');
        $this->login = config('services.placetopay.login');
        $this->secretKey = config('services.placetopay.secret_key');
    }

    public function resolveAuthorization(&$queryParameters): void
    {
        $credentials = $this->generateCredentials();
        $queryParameters['auth']['login'] = $this->login;
        $queryParameters['auth']['tranKey'] = $credentials['tranKey'];
        $queryParameters['auth']['nonce'] = $credentials['nonce'];
        $queryParameters['auth']['seed'] = $credentials['seed'];
    }

    public function generateCredentials(): array
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
            'tranKey' => $tranKey,
            'nonce' => $nonceBase64,
            'seed' => $seed,
        ];
    }

    public function decodeResponse($response)
    {
        return $response->json();
    }

    public function createPayment(Payment $payment): RedirectResponse
    {
        $reference = $payment->id;
        $description = $payment->description;
        $currency = 'COP';
        $total = $payment->amount;
        $p2pResponse = $this->createRequest($reference, $description, $currency, $total);

        if ($p2pResponse['status']['status'] != 'OK') {
            throw ValidationException::withMessages([
                'gateway' =>
                __('Order creation could not be completed')
            ]);
        }

        $payment->reference = $p2pResponse['requestId'];
        $payment->process_url = $p2pResponse['processUrl'];
        $payment->save();

        return redirect($payment->process_url);
    }

    public function retryPayment(Payment $payment): RedirectResponse
    {
        return redirect($payment->process_url);
    }

    public function createRequest(
        string $reference,
        string $description,
        string $currency,
        int $total
    ) {
        $queryParameters['payment']['reference'] = $reference;
        $queryParameters['payment']['description'] = $description;
        $queryParameters['payment']['amount']['currency'] = $currency;
        $queryParameters['payment']['amount']['total'] = $total;
        $queryParameters['expiration'] = date('c', strtotime('+7 minute'));
        $queryParameters['returnUrl'] = route('payments.show', $reference);
        $queryParameters['ipAddress'] = request()->ip();
        $queryParameters['userAgent'] = request()->header('User-agent');

        return $this->makeRequest(
            'post',
            '/api/session/',
            $queryParameters
        );
    }

    public function getPaymentInformation(string $reference): array
    {
        return $this->makeRequest(
            'post',
            '/api/session/' . $reference
        );
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
