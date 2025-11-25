<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    public function uploadDocument(Request $request, int $appointmentId): JsonResponse
    {
        try {
            $request->validate([
                'document' => 'required|file|mimes:pdf,jpg,png|max:5120'
            ]);

            $appointment = Appointment::findOrFail($appointmentId);
            
            // Upload to S3 (or local storage in development)
            $path = $request->file('document')->store(
                "appointments/{$appointmentId}/documents",
                config('filesystems.default')
            );
            
            // Generate URL
            $url = Storage::url($path);
            
            Log::info('Document uploaded', [
                'appointment_id' => $appointmentId,
                'path' => $path
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => [
                    'path' => $path,
                    'url' => $url
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Document upload failed', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document'
            ], 500);
        }
    }
}