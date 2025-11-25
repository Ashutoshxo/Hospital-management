<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment;
use App\Services\CCAvenue\CCAvenueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    private CCAvenueService $ccavenueService;

    public function __construct(CCAvenueService $ccavenueService)
    {
        $this->ccavenueService = $ccavenueService;
    }

    public function initiatePayment(int $id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);

            if (!$appointment->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already initiated or appointment not in pending status'
                ], 400);
            }

            DB::beginTransaction();

            $payment = Payment::create([
                'appointment_id' => $appointment->id,
                'amount' => $appointment->consultation_fee,
                'payment_status' => 'initiated'
            ]);

            $paymentData = $this->ccavenueService->generatePaymentRequest([
                'order_id' => 'APT_' . $appointment->id,
                'amount' => $appointment->consultation_fee,
                'currency' => 'INR',
                'billing_name' => $appointment->patient_name,
                'billing_email' => $appointment->patient_email,
                'billing_tel' => $appointment->patient_phone,
                'redirect_url' => url('/api/payments/callback'),
                'cancel_url' => url('/api/payments/cancel')
            ]);

            DB::commit();

            Log::info('Payment initiated', [
                'appointment_id' => $appointment->id,
                'payment_id' => $payment->id,
                'amount' => $appointment->consultation_fee
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'payment_url' => $paymentData['payment_url'],
                    'encrypted_data' => $paymentData['encrypted_data'],
                    'access_code' => $paymentData['access_code']
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Payment initiation failed', [
                'appointment_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment'
            ], 500);
        }
    }

    public function handleCallback(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|string',
                'tracking_id' => 'required|string',
                'order_status' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid callback data'
                ], 400);
            }

            DB::beginTransaction();

            $orderId = $request->input('order_id');
            $appointmentId = (int) str_replace('APT_', '', $orderId);
            
            $appointment = Appointment::findOrFail($appointmentId);
            $payment = $appointment->latestPayment;

            Log::info('Payment callback received', [
                'appointment_id' => $appointmentId,
                'transaction_id' => $request->input('tracking_id'),
                'status' => $request->input('order_status')
            ]);

            if ($request->input('order_status') === 'Success') {
                $payment->markAsSuccess($request->input('tracking_id'));
                $appointment->markAsConfirmed();
                $message = 'Payment successful';
                $success = true;
            } else {
                $payment->markAsFailed($request->input('tracking_id'));
                $message = 'Payment failed';
                $success = false;
            }

            DB::commit();

            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => [
                    'appointment_id' => $appointment->id,
                    'payment_status' => $payment->payment_status,
                    'transaction_id' => $payment->ccavenue_transaction_id
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Payment callback failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process callback'
            ], 500);
        }
    }
}