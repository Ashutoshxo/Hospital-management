<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAppointmentRequest;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    // GET - Browser mein test karne ke liye
    public function index(): JsonResponse
    {
        try {
            $appointments = Appointment::with('latestPayment')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Appointments retrieved successfully',
                'count' => $appointments->count(),
                'data' => $appointments
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to fetch appointments', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appointments'
            ], 500);
        }
    }

    // GET - Single appointment details
    public function show(int $id): JsonResponse
    {
        try {
            $appointment = Appointment::with('payments')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Appointment found',
                'data' => $appointment
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }
    }

    // POST - Create appointment
    public function store(CreateAppointmentRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $appointment = Appointment::create($request->validated());

            DB::commit();

            Log::info('Appointment created successfully', [
                'appointment_id' => $appointment->id,
                'patient_email' => $appointment->patient_email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment created successfully',
                'data' => $appointment
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create appointment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create appointment',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}