<?php

namespace App\Http\Controllers\APIGW;

use App\Http\Controllers\Controller;
use App\Services\MyadsApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

class ApiGatewayController extends Controller
{
	public function targetedAdd(Request $request, MyadsApiClient $client, string $channel): RedirectResponse
	{
		abort_unless($channel === 'sms', 404);

		$validated = $request->validate([
			'campaign_name' => ['required', 'string', 'max:255'],
			'sender_name' => ['required', 'string', 'max:255'],
			'message' => ['required', 'string', 'max:1024'],
			'recipient_total' => ['required', 'integer', 'min:1', 'max:100000'],
			'delivery_date' => ['nullable', 'date_format:Y-m-d'],
			'delivery_time' => ['nullable', 'date_format:H:i'],
			'control_number' => ['required', 'string', 'max:32'],
			'mms_file' => ['nullable', 'string', 'max:5000'],
		]);

		try {
			$gwToken = $this->requireGatewayToken($request);

			$campaignName = trim((string) ($validated['campaign_name'] ?? ''));
			$senderName = trim((string) ($validated['sender_name'] ?? ''));
			$message = trim((string) ($validated['message'] ?? ''));
			$recipientTotal = (int) ($validated['recipient_total'] ?? 0);

			$deliveryDate = $this->formatDateYmd($validated['delivery_date'] ?? null);
			$deliveryTime = $this->formatTimeHiToHmm($validated['delivery_time'] ?? null);

			$controlNumber = $this->normalizeMsisdn((string) ($validated['control_number'] ?? ''));

			$mmsFile = trim((string) ($validated['mms_file'] ?? ''));

			if ($campaignName === '' || $senderName === '' || $message === '' || $recipientTotal <= 0 || $controlNumber === null || $deliveryDate === null || $deliveryTime === null) {
				throw new RuntimeException('Data belum lengkap. Mohon lengkapi judul, pengirim, isi pesan, jumlah penerima, jadwal kirim, dan nomor kontrol.');
			}

			$payload = [
				'channel' => $mmsFile !== '' ? 'MMS' : 'SMS',
				'campaignName' => $campaignName,
				'message' => $message,
				'senderName' => $senderName,
				'recepientTotal' => $recipientTotal,
				'deliveries' => [
					[
						'deliveryDate' => $deliveryDate,
						'deliveryTime' => $deliveryTime,
					],
				],
				'controlNumbers' => [$controlNumber],
			];

			if ($mmsFile !== '') {
				$payload['mmsFile'] = $mmsFile;
				$payload['mmsSubject'] = 'MMS Campaign';
				$payload['profileGender'] = 'M';
				$payload['profileOs'] = 'Android';
			}

			$resp = $client->campaignTargetedAdd($gwToken, $payload);
			$campaignId = (string) (data_get($resp, 'data.data.campaignId')
				?? data_get($resp, 'data.data.data.campaignId')
				?? data_get($resp, 'data.campaignId')
				?? data_get($resp, 'data.data.campaign_id')
				?? '');

			$suffix = $campaignId !== '' ? " (campaignId: {$campaignId})" : '';

			return redirect()
				->route('campaign.menu', ['channel' => 'sms', 'menu' => 'targeted'])
				->with('status', 'Campaign Targeted berhasil dibuat.' . $suffix);
		} catch (Throwable $e) {
			return back()->withErrors([
				'gw' => $e->getMessage(),
			])->withInput();
		}
	}

	public function lbaAdd(Request $request, MyadsApiClient $client, string $channel): RedirectResponse
	{
		abort_unless($channel === 'sms', 404);

		$validated = $request->validate([
			'campaign_name' => ['required', 'string', 'max:255'],
			'sender_name' => ['required', 'string', 'max:255'],
			'message' => ['required', 'string', 'max:1024'],
			'content_name' => ['required', 'string', 'max:255'],
			'recipient_total' => ['required', 'integer', 'min:1', 'max:100000'],
			'delivery_start_date' => ['nullable', 'date_format:Y-m-d'],
			'delivery_end_date' => ['nullable', 'date_format:Y-m-d'],
			'delivery_start_time' => ['nullable', 'date_format:H:i'],
			'delivery_end_time' => ['nullable', 'date_format:H:i'],
			'control_number' => ['required', 'string', 'max:32'],
			'location_name' => ['required', 'string', 'max:255'],
			'location_lat' => ['required', 'numeric'],
			'location_lng' => ['required', 'numeric'],
			'location_radius' => ['required', 'integer', 'min:1', 'max:50000'],
			'location_type' => ['required', 'string', 'in:ALL,HOME,MALL'],
		]);

		try {
			$gwToken = $this->requireGatewayToken($request);

			$campaignName = trim((string) ($validated['campaign_name'] ?? ''));
			$senderName = trim((string) ($validated['sender_name'] ?? ''));
			$message = trim((string) ($validated['message'] ?? ''));
			$contentName = trim((string) ($validated['content_name'] ?? ''));
			$recipientTotal = (int) ($validated['recipient_total'] ?? 0);

			$startDate = $this->formatDateYmd($validated['delivery_start_date'] ?? null);
			$endDate = $this->formatDateYmd($validated['delivery_end_date'] ?? null);
			$startTime = $this->formatTimeHiToHmm($validated['delivery_start_time'] ?? null);
			$endTime = $this->formatTimeHiToHmm($validated['delivery_end_time'] ?? null);

			$controlNumber = $this->normalizeMsisdn((string) ($validated['control_number'] ?? ''));

			$locationName = trim((string) ($validated['location_name'] ?? ''));
			$lat = (float) ($validated['location_lat'] ?? 0);
			$lng = (float) ($validated['location_lng'] ?? 0);
			$radius = (int) ($validated['location_radius'] ?? 0);
			$locationType = trim((string) ($validated['location_type'] ?? ''));

			if ($campaignName === '' || $senderName === '' || $message === '' || $contentName === '' || $recipientTotal <= 0 || $controlNumber === null || $startDate === null || $endDate === null || $startTime === null || $endTime === null || $locationName === '' || $radius <= 0 || $locationType === '') {
				throw new RuntimeException('Data belum lengkap. Mohon lengkapi semua field sebelum mengirim campaign.');
			}

			$payload = [
				'channel' => 'SMS',
				'campaignName' => $campaignName,
				'message' => $message,
				'senderName' => $senderName,
				'recepientTotal' => $recipientTotal,
				'deliveries' => [
					[
						'deliveryStartDate' => $startDate,
						'deliveryStartTime' => $startTime,
						'deliveryEndDate' => $endDate,
						'deliveryEndTime' => $endTime,
					],
				],
				'controlNumbers' => [$controlNumber],
				'contents' => [
					'contentName' => $contentName,
					'message' => $message,
					'locations' => [
						'address' => $locationName,
						'latitude' => $lat,
						'longitude' => $lng,
						'locationNameMap' => $locationName,
						'locationNameUser' => $locationName,
						'radius' => $radius,
						'locationType' => $locationType,
					],
				],
			];

			$resp = $client->campaignLbaAdd($gwToken, $payload);
			$campaignId = (string) (data_get($resp, 'data.data.campaignId')
				?? data_get($resp, 'data.data.data.campaignId')
				?? data_get($resp, 'data.campaignId')
				?? '');

			$suffix = $campaignId !== '' ? " (campaignId: {$campaignId})" : '';

			return redirect()
				->route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area'])
				->with('status', 'Campaign LBA berhasil dibuat.' . $suffix);
		} catch (Throwable $e) {
			return back()->withErrors([
				'gw' => $e->getMessage(),
			])->withInput();
		}
	}

	private function requireGatewayToken(Request $request): string
	{
		$gwToken = (string) $request->session()->get('myads.gw_token', '');
		if (trim($gwToken) === '') {
			throw new RuntimeException('Token gateway tidak ditemukan. Silakan login ulang.');
		}

		return $gwToken;
	}

	private function formatDateYmd(?string $value): ?string
	{
		$value = trim((string) $value);
		if ($value === '') {
			return null;
		}

		try {
			return Carbon::createFromFormat('Y-m-d', $value)->format('Ymd');
		} catch (Throwable) {
			return null;
		}
	}

	private function formatTimeHiToHmm(?string $value): ?string
	{
		$value = trim((string) $value);
		if ($value === '') {
			return null;
		}

		// value from UI is like 09:30
		if (!preg_match('/^(\d{2}):(\d{2})$/', $value, $m)) {
			return null;
		}

		return $m[1] . $m[2];
	}

	private function normalizeMsisdn(string $value): ?string
	{
		$raw = preg_replace('/\s+/', '', $value);
		$raw = str_replace(['-', '(', ')', '.'], '', (string) $raw);

		if ($raw === '') {
			return null;
		}

		// Accept already normalized "62..."
		if (str_starts_with($raw, '62')) {
			return $raw;
		}

		// Convert leading 0 to 62
		if (str_starts_with($raw, '0')) {
			return '62' . substr($raw, 1);
		}

		// Fallback: keep as-is
		return $raw;
	}
}

