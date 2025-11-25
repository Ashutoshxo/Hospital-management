<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class CreateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'patient_email' => 'required|email|max:255',
            'patient_phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'consultant_type' => 'required|string|max:100',
            'appointment_date' => 'required|date|after:now|before:' . now()->addMonths(3)->toDateString(),
            'consultation_fee' => 'required|numeric|min:100|max:50000'
        ];
    }

    public function messages(): array
    {
        return [
            'patient_name.required' => 'Patient name is required.',
            'patient_name.regex' => 'Patient name should only contain letters and spaces.',
            'patient_email.required' => 'Email address is required.',
            'patient_email.email' => 'Please provide a valid email address.',
            'patient_phone.required' => 'Phone number is required.',
            'patient_phone.regex' => 'Invalid phone number format.',
            'consultant_type.required' => 'Consultant type is required.',
            'appointment_date.required' => 'Appointment date is required.',
            'appointment_date.after' => 'Appointment date must be in the future.',
            'appointment_date.before' => 'Appointment cannot be scheduled more than 3 months in advance.',
            'consultation_fee.required' => 'Consultation fee is required.',
            'consultation_fee.min' => 'Consultation fee must be at least ₹100.',
            'consultation_fee.max' => 'Consultation fee cannot exceed ₹50,000.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::warning('Invalid appointment data submitted', [
            'errors' => $validator->errors()->toArray(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent()
        ]);

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}