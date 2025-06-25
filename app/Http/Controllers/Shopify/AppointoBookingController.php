<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Models\AppointoBooking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AppointoBookingController extends Controller {
    /**
     * Make Appointo HTTP client
     */
    private function appointo() {
        return Http::withHeaders([
            'APPOINTO-TOKEN' => config('services.appointo.token'),
        ]);
    }

    /**
     * Create a new Appointo booking
     */
    public function store(Request $request) {
        try {
           
            $data = $request->validate([
                'appointment_id' => 'required|integer',
                'email'          => 'required|email',
                'name'           => 'required|string',
                'phone'          => 'nullable|string',
                'quantity'       => 'nullable|integer',
            ]);

            $data['timestring'] = Carbon::now();
            $response = $this->appointo()
                ->post(config('services.appointo.base') . '/bookings', $data);

            if (!$response->successful()) {
                return response()->json([
                    'error' => $response->json(),
                    'message' => 'Booking failed at Appointo API'
                ], $response->status());
            }
            $users = User::all();
            // ✅ Save Appointo booking locally
            $appointoData = $response->json();
            $booking = AppointoBooking::create([
                'shop_id'             => $users[0]->id,
                'appointment_id'      => $data['appointment_id'],
                'timestring'          => $data['timestring'],
                'email'               => $data['email'],
                'name'                => $data['name'],
                'phone'               => $data['phone'] ?? null,
                'quantity'            => $data['quantity'] ?? null,
                'appointo_booking_id' => $appointoData['id'] ?? null,
            ]);

            // ✅ Sync to Mindbody
            $mindbodyAppointment = app(\App\Http\Controllers\Shopify\MindbodyController::class)
                ->bookFromAppointo([
                    'shop' => $shop,
                    'appointo_data' => $data,
                ]);

            // ✅ Create sync record
            \App\Models\AppointoMindbodySync::create([
                'shop_id' => $shop->id,
                'appointo_booking_id' => $booking->id,
                'mindbody_appointment_id' => $mindbodyAppointment->id,
            ]);

            return response()->json([
                'message'   => 'Booking created and synced successfully',
                'local'     => $booking,
                'appointo'  => $appointoData,
                'mindbody'  => $mindbodyAppointment,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to store: ' . $e->getMessage());
        }
    }

    /**
     * Reschedule an Appointo booking
     */
    public function reschedule(Request $request) {
        $payload = $request->validate([
            'booking_id' => 'required|string',
            'timestring' => 'required|string',
        ]);

        return $this->appointo()
            ->post(config('services.appointo.base') . '/bookings/reschedule', $payload)
            ->throw()
            ->json();
    }

    /**
     * Cancel an Appointo booking
     */
    public function cancel(Request $request) {
        $payload = $request->validate([
            'booking_id'   => 'required|string',
            'customer_ids' => 'required|array',
        ]);

        return $this->appointo()
            ->post(config('services.appointo.base') . '/bookings/cancel', $payload)
            ->throw()
            ->json();
    }

    /**
     * List all Appointo bookings for this store
     */
    public function index() {
        $shop = auth()->user();

        $bookings = AppointoBooking::where('shop_id', $shop->id)
            ->latest()
            ->get();

        return response()->json($bookings);
    }
}
