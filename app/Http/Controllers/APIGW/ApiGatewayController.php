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
			'campaign_name' => ['nullable', 'string', 'max:255'],
			'sender_name' => ['nullable', 'string', 'max:255'],
			'message' => ['nullable', 'string', 'max:1024'],
			'recipient_total' => ['nullable', 'integer', 'min:1', 'max:100000'],
			'delivery_date' => ['nullable', 'date_format:Y-m-d'],
			'delivery_time' => ['nullable', 'date_format:H:i'],
			'control_number' => ['nullable', 'string', 'max:32'],
			'mms_file' => ['nullable', 'string', 'max:5000'],
		]);

		try {
			$gwToken = $this->requireGatewayToken($request);

			$campaignName = trim((string) ($validated['campaign_name'] ?? ''));
			if ($campaignName === '') {
				$campaignName = 'UAT SMS Targeted ' . now()->format('YmdHis');
			}

			$senderName = trim((string) ($validated['sender_name'] ?? ''));
			if ($senderName === '') {
				$senderName = 'MyAds';
			}

			$message = trim((string) ($validated['message'] ?? ''));
			if ($message === '') {
				$message = 'testing SIT';
			}

			$recipientTotal = (int) ($validated['recipient_total'] ?? 10);
			if ($recipientTotal <= 0) {
				$recipientTotal = 10;
			}

			$deliveryDate = $this->formatDateYmd($validated['delivery_date'] ?? null) ?? now()->format('Ymd');
			$deliveryTime = $this->formatTimeHiToHmm($validated['delivery_time'] ?? null) ?? '0800';

			$controlNumber = $this->normalizeMsisdn((string) ($validated['control_number'] ?? ''));
			if ($controlNumber === null) {
				$controlNumber = '6282381600327';
			}

			$mmsFile = trim((string) ($validated['mms_file'] ?? ''));
			if ($mmsFile === '') {
				$mmsFile = trim((string) config('myads.dummy_mms_file', ''));
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
			'campaign_name' => ['nullable', 'string', 'max:255'],
			'sender_name' => ['nullable', 'string', 'max:255'],
			'message' => ['nullable', 'string', 'max:1024'],
			'recipient_total' => ['nullable', 'integer', 'min:1', 'max:100000'],
			'delivery_start_date' => ['nullable', 'date_format:Y-m-d'],
			'delivery_end_date' => ['nullable', 'date_format:Y-m-d'],
			'delivery_start_time' => ['nullable', 'date_format:H:i'],
			'delivery_end_time' => ['nullable', 'date_format:H:i'],
			'control_number' => ['nullable', 'string', 'max:32'],
			'location_name' => ['nullable', 'string', 'max:255'],
			'location_lat' => ['nullable', 'numeric'],
			'location_lng' => ['nullable', 'numeric'],
			'location_radius' => ['nullable', 'integer', 'min:1', 'max:50000'],
		]);

		try {
			$gwToken = $this->requireGatewayToken($request);

			$campaignName = trim((string) ($validated['campaign_name'] ?? ''));
			if ($campaignName === '') {
				$campaignName = 'UAT SMS LBA ' . now()->format('YmdHis');
			}

			$senderName = trim((string) ($validated['sender_name'] ?? ''));
			if ($senderName === '') {
				$senderName = 'MyAds';
			}

			$message = trim((string) ($validated['message'] ?? ''));
			if ($message === '') {
				$message = 'description campaign add';
			}

			$recipientTotal = (int) ($validated['recipient_total'] ?? 10);
			if ($recipientTotal <= 0) {
				$recipientTotal = 10;
			}

			$startDate = $this->formatDateYmd($validated['delivery_start_date'] ?? null) ?? now()->format('Ymd');
			$endDate = $this->formatDateYmd($validated['delivery_end_date'] ?? null) ?? $startDate;
			$startTime = $this->formatTimeHiToHmm($validated['delivery_start_time'] ?? null) ?? '0800';
			$endTime = $this->formatTimeHiToHmm($validated['delivery_end_time'] ?? null) ?? '1344';

			$controlNumber = $this->normalizeMsisdn((string) ($validated['control_number'] ?? ''));
			if ($controlNumber === null) {
				$controlNumber = '6282381600326';
			}

			$locationName = trim((string) ($validated['location_name'] ?? ''));
			if ($locationName === '') {
				$locationName = 'Bundaran HI, Jakarta';
			}

			$lat = (float) ($validated['location_lat'] ?? -6.1944491);
			$lng = (float) ($validated['location_lng'] ?? 106.8229198);
			$radius = (int) ($validated['location_radius'] ?? 500);
			if ($radius <= 0) {
				$radius = 500;
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
					'contentName' => 'test',
					'message' => $message,
					'locations' => [
						'address' => $locationName,
						'latitude' => $lat,
						'longitude' => $lng,
						'locationNameMap' => $locationName,
						'locationNameUser' => $locationName,
						'radius' => $radius,
						'locationType' => 'ALL',
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
