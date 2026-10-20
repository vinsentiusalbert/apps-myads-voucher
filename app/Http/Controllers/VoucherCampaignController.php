<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class VoucherCampaignController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $code = (string) $request->session()->get('voucher.code', '');
        $package = config('vouchers.'.$code);
        abort_unless(is_array($package) && ($package['campaign_type'] ?? '') === 'sms_lba', 403);
        abort_if($package['report_demo'] ?? false, 403, 'Campaign voucher ini sudah selesai. Anda dapat melihat report campaign.');

        $validated = $request->validate([
            'campaign_name' => ['required', 'string', 'max:100'],
            'sender_name' => ['required', 'string', 'max:11', 'regex:/\A[A-Za-z0-9 ]+\z/'],
            'message' => ['required', 'string', 'max:160'],
            'location_name' => ['required', 'string', 'max:255'],
            'location_lat' => ['required', 'numeric', 'between:-90,90'],
            'location_lng' => ['required', 'numeric', 'between:-180,180'],
            'location_radius' => ['required', 'integer', 'between:1,50000'],
            'delivery_start_date' => ['required', 'date_format:Y-m-d'],
            'delivery_end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:delivery_start_date'],
            'delivery_start_time' => ['required', 'date_format:H:i'],
            'delivery_end_time' => ['required', 'date_format:H:i'],
            'control_numbers' => ['required', 'array', 'min:1'],
            'control_numbers.*' => ['required', 'string', 'regex:/\A(?:0|\+?62)?8[0-9]{8,11}\z/'],
        ], [
            'required' => ':attribute wajib diisi.',
            'message.max' => 'Pesan SMS maksimal 160 karakter.',
            'sender_name.max' => 'Nama pengirim maksimal 11 karakter.',
            'sender_name.regex' => 'Nama pengirim hanya boleh berisi huruf, angka, dan spasi.',
            'location_lat.between' => 'Latitude harus antara -90 dan 90.',
            'location_lng.between' => 'Longitude harus antara -180 dan 180.',
            'location_radius.between' => 'Radius harus antara 1 dan 50.000 meter.',
            'date_format' => 'Format :attribute tidak valid.',
            'delivery_end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'control_numbers.array' => 'Nomor testing harus berupa daftar nomor.',
            'control_numbers.*.regex' => 'Masukkan nomor testing mulai dari 8. Awalan 62 sudah disediakan.',
        ], [
            'campaign_name' => 'Nama campaign', 'sender_name' => 'Nama pengirim',
            'message' => 'Pesan SMS',
            'location_name' => 'Nama lokasi', 'location_lat' => 'Latitude',
            'location_lng' => 'Longitude', 'location_radius' => 'Radius',
            'delivery_start_date' => 'Tanggal mulai', 'delivery_end_date' => 'Tanggal selesai',
            'delivery_start_time' => 'Jam mulai', 'delivery_end_time' => 'Jam selesai',
            'control_numbers' => 'Nomor testing', 'control_numbers.*' => 'Nomor testing ke-:position',
        ]);

        $scheduleErrors = [];
        foreach (['delivery_start_time', 'delivery_end_time'] as $field) {
            if ($validated[$field] < '06:00' || $validated[$field] > '20:00') {
                $scheduleErrors[$field] = 'Jam pengiriman hanya tersedia pukul 06.00-20.00 WIB.';
            }
        }

        $startAt = Carbon::createFromFormat('!Y-m-d H:i', $validated['delivery_start_date'].' '.$validated['delivery_start_time'], 'Asia/Jakarta');
        if ($startAt->lt(now('Asia/Jakarta')->addHours(4))) {
            $scheduleErrors['delivery_start_time'] = 'Jadwal mulai minimal 4 jam dari waktu sekarang (WIB).';
        }

        [$startHour, $startMinute] = array_map('intval', explode(':', $validated['delivery_start_time']));
        [$endHour, $endMinute] = array_map('intval', explode(':', $validated['delivery_end_time']));
        if (($endHour * 60 + $endMinute) - ($startHour * 60 + $startMinute) < 240) {
            $scheduleErrors['delivery_end_time'] = 'Rentang jam pengiriman minimal 4 jam per hari.';
        }
        if ($scheduleErrors !== []) {
            throw ValidationException::withMessages($scheduleErrors);
        }

        $validated['control_numbers'] = array_values(array_unique(array_map(
            fn (string $number): string => '62'.preg_replace('/\A(?:\+?62|0)/', '', $number),
            $validated['control_numbers'],
        )));

        $request->session()->put('voucher_campaign', array_merge($validated, [
            'voucher_code' => $code,
            'campaign_type' => $package['campaign_type'],
            'report_days' => $package['report_days'],
            'recipient_total' => $package['sms_quota'],
            'report_email' => $request->session()->get('voucher_campaign.report_email'),
            'status' => 'draft',
        ]));

        return redirect()->route('dashboard')
            ->with('status', 'Setup campaign dummy berhasil disimpan. Belum ada SMS yang dikirim.')
            ->with('prompt_report_email', ! $request->session()->get('voucher_campaign.report_email'));
    }

    public function saveReportEmail(Request $request): RedirectResponse
    {
        abort_unless(
            $request->session()->get('voucher_campaign.status') === 'draft'
            && $request->session()->get('voucher.code') === $request->session()->get('voucher_campaign.voucher_code'),
            403,
        );

        $validated = $request->validateWithBag('reportEmail', [
            'report_email' => ['nullable', 'string', 'email', 'max:254'],
        ], [
            'report_email.email' => 'Masukkan alamat email yang valid.',
            'report_email.string' => 'Masukkan alamat email yang valid.',
            'report_email.max' => 'Alamat email maksimal 254 karakter.',
        ]);

        $email = $validated['report_email'] ?? null;
        if ($email) {
            $request->session()->put('voucher_campaign.report_email', $email);
        }

        return redirect()->route('dashboard')->with('status', $email
            ? 'Email report berhasil disimpan. Mode dummy belum mengirim report ke email.'
            : 'Langkah email dilewati. Campaign tetap tersimpan.');
    }
}
