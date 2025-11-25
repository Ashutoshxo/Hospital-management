<?php

namespace App\Services\CCAvenue;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class CCAvenueService
{
    private string $merchantId;
    private string $accessCode;
    private string $workingKey;
    private string $paymentUrl;
 
    private int $timeout = 30;
    private int $retryTimes = 3;
    private int $retryDelay = 1000;

    public function __construct()
    {
        $this->merchantId = config('services.ccavenue.merchant_id');
        $this->accessCode = config('services.ccavenue.access_code');
        $this->workingKey = config('services.ccavenue.working_key');
        $this->paymentUrl = config('services.ccavenue.payment_url');
    }

    public function generatePaymentRequest(array $data): array
    {
        try {
            $merchantData = [
                'merchant_id' => $this->merchantId,
                'order_id' => $data['order_id'],
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'INR',
                'redirect_url' => $data['redirect_url'],
                'cancel_url' => $data['cancel_url'],
                'language' => 'EN',
                'billing_name' => $data['billing_name'],
                'billing_email' => $data['billing_email'],
                'billing_tel' => $data['billing_tel']
            ];

            $merchantDataString = http_build_query($merchantData);
            $encryptedData = $this->encrypt($merchantDataString);

      
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retryDelay, function ($exception) {
                    return $exception instanceof ConnectionException;
                })
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ])
                ->asForm()
                ->post($this->paymentUrl, [
                    'encRequest' => $encryptedData,
                    'access_code' => $this->accessCode
                ]);

            if ($response->failed()) {
                throw new \Exception('CCAvenue API error: ' . $response->status());
            }

            Log::info('CCAvenue payment request successful', [
                'order_id' => $data['order_id'],
                'response_time' => $response->transferStats?->getTransferTime() ?? 'N/A'
            ]);

            return [
                'payment_url' => $this->paymentUrl,
                'encrypted_data' => $encryptedData,
                'access_code' => $this->accessCode
            ];

        } catch (ConnectionException $e) {
            Log::error('CCAvenue connection timeout', [
                'order_id' => $data['order_id'] ?? null,
                'error' => $e->getMessage(),
                'timeout' => $this->timeout,
                'retry_attempts' => $this->retryTimes
            ]);

            throw new \Exception('Payment gateway temporarily unavailable. Please try again later.');

        } catch (RequestException $e) {
            Log::error('CCAvenue request failed', [
                'order_id' => $data['order_id'] ?? null,
                'status_code' => $e->response?->status(),
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Payment processing error. Please contact support.');

        } catch (\Exception $e) {
            Log::error('CCAvenue payment failed', [
                'order_id' => $data['order_id'] ?? null,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    private function encrypt(string $plainText): string
    {
        $key = $this->hexToKey($this->workingKey);
        $initVector = pack('C*', 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        
        $encrypted = openssl_encrypt($plainText, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $initVector);
        
        return bin2hex($encrypted);
    }

    private function hexToKey(string $hex): string
    {
        $key = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $key .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $key;
    }
}