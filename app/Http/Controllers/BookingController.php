<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    private const TZ = 'Europe/London';

    private const SERVICES = [
        'discovery' => ['name' => 'Free Cybersecurity Discovery Call', 'duration' => 30, 'description' => 'A focused conversation about your business, current IT setup and cybersecurity priorities.'],
        'consultation' => ['name' => 'Cybersecurity Consultation', 'duration' => 60, 'description' => 'Discuss Microsoft 365, email security, Cyber Essentials, penetration testing or managed IT requirements.'],
        'support' => ['name' => 'Existing Client Support', 'duration' => 30, 'description' => 'A support session for an existing AceGuard client.'],
        'meeting' => ['name' => 'Business / BNI Meeting', 'duration' => 30, 'description' => 'Book a one-to-one business or referral meeting with Ray.'],
    ];

    public function index()
    {
        return view('booking.index', ['services' => self::SERVICES]);
    }

    public function slots(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'service' => ['required', 'in:'.implode(',', array_keys(self::SERVICES))],
        ]);

        $date = Carbon::createFromFormat('Y-m-d', $data['date'], self::TZ)->startOfDay();
        $duration = self::SERVICES[$data['service']]['duration'];

        if ($date->isPast() && !$date->isToday()) {
            return response()->json(['slots' => []]);
        }

        // Sunday unavailable. Friday is reserved for BNI until 13:00.
        if ($date->isSunday()) {
            return response()->json(['slots' => []]);
        }

        $startHour = $date->isFriday() ? 13 : 9;
        $dayStart = $date->copy()->setTime($startHour, 0);
        $dayEnd = $date->copy()->setTime(18, 0);
        $now = now(self::TZ)->addHours(2);

        $bookings = Booking::where('status', 'confirmed')
            ->whereDate('appointment_at', $date->toDateString())
            ->get(['appointment_at', 'ends_at']);

        $slots = [];
        for ($slot = $dayStart->copy(); $slot->copy()->addMinutes($duration)->lte($dayEnd); $slot->addMinutes(30)) {
            $end = $slot->copy()->addMinutes($duration);
            if ($slot->lt($now)) {
                continue;
            }

            $conflict = $bookings->contains(function ($booking) use ($slot, $end) {
                return $slot->lt($booking->ends_at) && $end->gt($booking->appointment_at);
            });

            if (!$conflict) {
                $slots[] = ['value' => $slot->format('H:i'), 'label' => $slot->format('g:i A')];
            }
        }

        return response()->json(['slots' => $slots]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service' => ['required', 'in:'.implode(',', array_keys(self::SERVICES))],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $service = self::SERVICES[$data['service']];
        $start = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time'], self::TZ);
        $end = $start->copy()->addMinutes($service['duration']);

        if ($start->lt(now(self::TZ)->addHours(2)) || $start->isSunday() || $start->minute % 30 !== 0 || $start->hour < ($start->isFriday() ? 13 : 9) || $end->gt($start->copy()->setTime(18, 0))) {
            throw ValidationException::withMessages(['time' => 'That appointment time is not available. Please choose another slot.']);
        }

        $booking = DB::transaction(function () use ($data, $service, $start, $end) {
            $conflict = Booking::where('status', 'confirmed')
                ->where('appointment_at', '<', $end)
                ->where('ends_at', '>', $start)
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages(['time' => 'That slot has just been booked. Please choose another time.']);
            }

            return Booking::create([
                'reference' => 'AG-'.strtoupper(Str::random(8)),
                'service' => $service['name'],
                'duration_minutes' => $service['duration'],
                'appointment_at' => $start,
                'ends_at' => $end,
                'timezone' => self::TZ,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'confirmed',
            ]);
        });

        return redirect()->route('booking.confirmed', $booking->reference);
    }

    public function confirmed(string $reference)
    {
        $booking = Booking::where('reference', $reference)->firstOrFail();
        return view('booking.confirmed', compact('booking'));
    }
}
