<?php

namespace App\Services;

class DummyVoucherReport
{
    public function create(string $code, array $package): array
    {
        $start = now('Asia/Jakarta')->subDays(2)->startOfDay();
        $end = $start->copy()->addDay();
        $sent = $package['sms_quota'];
        $firstDaySent = intdiv($sent, 2);
        $secondDaySent = $sent - $firstDaySent;
        $firstDayDelivered = (int) round($firstDaySent * 0.94);
        $secondDayDelivered = (int) round($secondDaySent * 0.90);
        $delivered = $firstDayDelivered + $secondDayDelivered;

        return [
            'campaign' => [
                'campaign_name' => 'Promo Akhir Pekan Toko Kita',
                'campaign_type' => 'sms_lba',
                'voucher_code' => $code,
                'sender_name' => 'TOKOKITA',
                'message' => 'Promo spesial untuk Anda! Dapatkan diskon 20% di Toko Kita. Kunjungi toko kami hari ini.',
                'location_name' => 'Area toko Jakarta Selatan',
                'location_lat' => -6.2,
                'location_lng' => 106.816666,
                'location_radius' => 1000,
                'delivery_start_date' => $start->format('Y-m-d'),
                'delivery_end_date' => $end->format('Y-m-d'),
                'delivery_start_time' => '06:00',
                'delivery_end_time' => '10:00',
                'recipient_total' => $package['sms_quota'],
                'report_days' => $package['report_days'],
                'status' => 'completed',
            ],
            'report' => [
                'sent' => $sent,
                'delivered' => $delivered,
                'failed' => $sent - $delivered,
                'updated_at' => now('Asia/Jakarta')->format('d/m/Y H:i'),
                'available_until' => $start->copy()->addDays($package['report_days'])->format('d/m/Y'),
                'daily' => [
                    ['date' => $start->format('d/m/Y'), 'sent' => $firstDaySent, 'delivered' => $firstDayDelivered, 'failed' => $firstDaySent - $firstDayDelivered],
                    ['date' => $end->format('d/m/Y'), 'sent' => $secondDaySent, 'delivered' => $secondDayDelivered, 'failed' => $secondDaySent - $secondDayDelivered],
                ],
            ],
        ];
    }
}
