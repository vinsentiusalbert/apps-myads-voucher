<?php

namespace App\Http\Controllers\APIGW;

use App\Http\Controllers\Controller;
use App\Models\SmsLbaCampaign;
use App\Models\SmsTargetedCampaign;
use App\Services\MyadsApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

class ApiGatewayController extends Controller
{
	public function paymentInitiate(Request $request, MyadsApiClient $client): JsonResponse
	{
		$validated = $request->validate([
			'transaction_amount' => ['required', 'numeric', 'min:1'],
			'customer_phone' => ['required', 'string', 'max:32'],
			'product_category' => ['nullable', 'string', 'max:255'],
			'product_type' => ['nullable', 'string', 'max:255'],
			'product_detail' => ['nullable', 'string', 'max:500'],
		]);

		try {
			$accessToken = $this->requireAccessToken($request);
			$user = $request->user();
			$email = is_object($user) ? (string) ($user->email ?? '') : '';
			$name = is_object($user) ? (string) ($user->name ?? '') : '';

			$phone = $this->normalizeMsisdn((string) ($validated['customer_phone'] ?? ''));
			if ($phone === null) {
				throw new RuntimeException('Nomor telepon tidak valid.');
			}

			if (trim($email) === '') {
				throw new RuntimeException('Email user tidak tersedia. Silakan login ulang.');
			}

			$payload = [
				'customer_phone' => $phone,
				'customer_email' => $email,
				'customer_name' => trim($name) !== '' ? $name : $email,
				'transaction_amount' => (float) $validated['transaction_amount'],
				'product_category' => $validated['product_category'] ?? 'MYADS',
				'product_type' => $validated['product_type'] ?? 'ADVERTISEMENT',
				'product_detail' => $validated['product_detail'] ?? 'Advertisement Payment',
			];

			$resp = $client->paymentInitiate($accessToken, $payload);
			return response()->json($resp);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 500);
		}
	}

	public function paymentStatus(Request $request, MyadsApiClient $client, string $transactionId): JsonResponse
	{
		try {
			$accessToken = $this->requireAccessToken($request);
			$resp = $client->paymentStatus($accessToken, $transactionId);
			return response()->json($resp);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 500);
		}
	}

	public function priceView(Request $request, MyadsApiClient $client): JsonResponse
	{
		try {
			return response()->json([
				'success' => true,
				'prices' => $this->campaignPrices($client, $this->requireGatewayToken($request), $this->gatewayRefreshCallback($request, $client)),
			]);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
				'prices' => [
					'sms_lba' => null,
					'sms_targeted' => null,
					'wa_lba' => null,
					'wa_targeted' => null,
				],
			], 500);
		}
	}

	public function profileDropdownList(Request $request, MyadsApiClient $client, string $channel): JsonResponse
	{
		abort_unless($channel === 'sms', 404);

		try {
			$resp = $client->profileDropdownList($this->requireGatewayToken($request), [
				'profile' => 'ALL',
			], $this->gatewayRefreshCallback($request, $client));

			return response()->json($resp);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 500);
		}
	}

	public function campaignReport(Request $request, MyadsApiClient $client, string $channel): JsonResponse
	{
		abort_unless($channel === 'sms', 404);

		$campaignId = trim((string) ($request->query('campaign_id')
			?? $request->query('campaignId')
			?? $request->input('campaign_id')
			?? $request->input('campaignId')
			?? ''));
		if ($campaignId === '' || ! ctype_digit($campaignId)) {
			return response()->json([
				'success' => false,
				'message' => 'Campaign ID wajib diisi.',
			], 422);
		}

		try {
			return response()->json($client->campaignReport($this->requireGatewayToken($request), [
				'campaignId' => (int) $campaignId,
			], $this->gatewayRefreshCallback($request, $client)));
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 500);
		}
	}

	public function wabaTemplateList(Request $request, MyadsApiClient $client): JsonResponse
	{
		try {
			return response()->json($client->wabaTemplateList(
				$this->requireAccessToken($request),
				[],
				$this->requireGatewayToken($request),
				$this->gatewayRefreshCallback($request, $client)
			));
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 500);
		}
	}

	public function wabaDisplayNameList(Request $request, MyadsApiClient $client): JsonResponse
	{
		try {
			$payload = $client->wabaDisplayNameList(
				$this->requireAccessToken($request),
				[],
				$this->requireGatewayToken($request),
				$this->gatewayRefreshCallback($request, $client)
			);
			$list = data_get($payload, 'data.data.list')
				?? data_get($payload, 'data.list')
				?? data_get($payload, 'data.data.items')
				?? data_get($payload, 'data.items')
				?? [];

			return response()->json([
				'success' => true,
				'list' => is_array($list) ? array_values(array_filter($list, fn ($row) => is_array($row))) : [],
				'raw' => $payload,
			]);
		} catch (Throwable $e) {
			return response()->json([
				'success' => false,
				'message' => $e->getMessage(),
			], 500);
		}
	}

	public function targetedAdd(Request $request, MyadsApiClient $client, string $channel): RedirectResponse
	{
		abort_unless($channel === 'sms', 404);

		$validated = $request->validate([
			'payment_transaction_id' => ['required', 'string', 'max:128'],
			'campaign_name' => ['nullable', 'string', 'max:255'],
			'sender_name' => ['required', 'string', 'max:255'],
			'message' => ['required', 'string', 'max:1024'],
			'recipient_total' => ['required', 'integer', 'min:1', 'max:100000'],
			'delivery_date' => ['nullable', 'date_format:Y-m-d'],
			'delivery_time' => ['nullable', 'date_format:H:i'],
			'control_number' => ['required', 'string', 'max:32'],
			'mms_file' => ['nullable', 'string', 'max:5000'],
			'profile_gender' => ['nullable', 'array'],
			'profile_gender.*' => ['nullable', 'string', 'max:255'],
			'profile_age' => ['nullable', 'array'],
			'profile_age.*' => ['nullable', 'string', 'max:255'],
			'profile_os' => ['nullable', 'array'],
			'profile_os.*' => ['nullable', 'string', 'max:255'],
			'profile_data_package' => ['nullable', 'array'],
			'profile_data_package.*' => ['nullable', 'string', 'max:255'],
			'profile_household_spend' => ['nullable', 'array'],
			'profile_household_spend.*' => ['nullable', 'string', 'max:255'],
			'profile_marital_status' => ['nullable', 'array'],
			'profile_marital_status.*' => ['nullable', 'string', 'max:255'],
			'profile_lifestyle' => ['nullable', 'array'],
			'profile_lifestyle.*' => ['nullable', 'string', 'max:255'],
			'profile_religion' => ['nullable', 'array'],
			'profile_religion.*' => ['nullable', 'string', 'max:255'],
			'profile_card_type' => ['nullable', 'array'],
			'profile_card_type.*' => ['nullable', 'string', 'max:255'],
			'profile_hometown' => ['nullable', 'array'],
			'profile_hometown.*' => ['nullable', 'string', 'max:255'],
			'profile_work' => ['nullable', 'array'],
			'profile_work.*' => ['nullable', 'string', 'max:255'],
		]);

		$smsTargetedCampaign = null;

		try {
			$paymentTransactionId = trim((string) ($validated['payment_transaction_id'] ?? ''));
			if ($paymentTransactionId === '') {
				throw new RuntimeException('Pembayaran belum diinisiasi.');
			}
			$paymentResp = $client->paymentStatus($this->requireAccessToken($request), $paymentTransactionId);
			$paymentStatus = strtoupper(trim((string) data_get($paymentResp, 'data.status')));
			if ($paymentStatus !== 'SUCCESS') {
				$label = $paymentStatus !== '' ? $paymentStatus : 'PENDING';
				throw new RuntimeException('Pembayaran belum berhasil. Status: ' . $label);
			}

			$gwToken = $this->requireGatewayToken($request);

			$campaignName = $this->generateSmsTargetedCampaignName();
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
			}

			$payload = array_merge($payload, [
				'profileGender' => $this->pipeProfileValue($validated['profile_gender'] ?? [], [
					'male' => 'M',
					'female' => 'F',
					'all' => '',
				]),
				'profileAge' => $this->pipeProfileValue($validated['profile_age'] ?? []),
				'profileMobileDataSubs' => $this->pipeProfileValue($validated['profile_data_package'] ?? [], [
					'active' => 'Y',
					'inactive' => 'N',
					'all' => '',
				]),
				'profileArpu' => $this->pipeProfileValue($validated['profile_household_spend'] ?? []),
				'profileOs' => $this->pipeProfileValue($validated['profile_os'] ?? [], [
					'android' => 'Android',
					'ios' => 'iOS',
					'all' => '',
				]),
				'profileMaritalStatus' => $this->pipeProfileValue($validated['profile_marital_status'] ?? []),
				'profileLifestyleSegment' => $this->pipeProfileValue($validated['profile_lifestyle'] ?? []),
				'profileReligion' => $this->pipeProfileValue($validated['profile_religion'] ?? [], [], true),
				'profileCardType' => $this->pipeProfileValue($validated['profile_card_type'] ?? []),
				'profileHometown' => $this->pipeProfileValue($validated['profile_hometown'] ?? []),
				'profileWork' => $this->pipeProfileValue($validated['profile_work'] ?? []),
			]);

			$smsTargetedCampaign = SmsTargetedCampaign::create([
				'campaign_name' => $campaignName,
				'sender_name' => $senderName,
				'message' => $message,
				'recipient_total' => $recipientTotal,
				'delivery_date' => $deliveryDate,
				'delivery_time' => $deliveryTime,
				'control_number' => $controlNumber,
				'channel' => $payload['channel'],
				'mms_file' => $mmsFile !== '' ? $mmsFile : null,
				'gw_payload' => $payload,
				'status' => 'pending',
			]);

			$resp = $client->campaignTargetedAdd($gwToken, $payload, $this->gatewayRefreshCallback($request, $client));
			$campaignId = (string) (data_get($resp, 'data.data.campaignId')
				?? data_get($resp, 'data.data.data.campaignId')
				?? data_get($resp, 'data.campaignId')
				?? data_get($resp, 'data.data.campaign_id')
				?? '');

			$smsTargetedCampaign->update([
				'gw_response' => $resp,
				'gw_campaign_id' => $campaignId !== '' ? $campaignId : null,
				'status' => 'sent',
			]);

			$suffix = $campaignId !== '' ? " (campaignId: {$campaignId})" : '';

			return redirect()
				->route('campaign.menu', ['channel' => 'sms', 'menu' => 'targeted'])
				->with('status', 'Campaign Targeted berhasil dibuat.' . $suffix);
		} catch (Throwable $e) {
			if ($smsTargetedCampaign !== null) {
				$smsTargetedCampaign->update([
					'status' => 'failed',
					'error_message' => $e->getMessage(),
				]);
			}

			return back()->withErrors([
				'gw' => $e->getMessage(),
			])->withInput();
		}
	}

	public function lbaAdd(Request $request, MyadsApiClient $client, string $channel): RedirectResponse
	{
		abort_unless($channel === 'sms', 404);

		$validated = $request->validate([
			'payment_transaction_id' => ['required', 'string', 'max:128'],
			'campaign_name' => ['nullable', 'string', 'max:255'],
			'sender_name' => ['required', 'string', 'max:255'],
			'message' => ['required', 'string', 'max:1024'],
			'content_name' => ['nullable', 'string', 'max:255'],
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
			'location_type' => ['nullable', 'string', 'in:ALL,HOME,MALL'],
		]);

		$smsLbaCampaign = null;

		try {
			$paymentTransactionId = trim((string) ($validated['payment_transaction_id'] ?? ''));
			if ($paymentTransactionId === '') {
				throw new RuntimeException('Pembayaran belum diinisiasi.');
			}
			$paymentResp = $client->paymentStatus($this->requireAccessToken($request), $paymentTransactionId);
			$paymentStatus = strtoupper(trim((string) data_get($paymentResp, 'data.status')));
			if ($paymentStatus !== 'SUCCESS') {
				$label = $paymentStatus !== '' ? $paymentStatus : 'PENDING';
				throw new RuntimeException('Pembayaran belum berhasil. Status: ' . $label);
			}

			$gwToken = $this->requireGatewayToken($request);

			$campaignName = $this->generateSmsLbaCampaignName();
			$senderName = trim((string) ($validated['sender_name'] ?? ''));
			$message = trim((string) ($validated['message'] ?? ''));
			$contentName = trim((string) ($validated['content_name'] ?? ''));
			if ($contentName === '') {
				$contentName = $campaignName;
			}
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
			$locationType = trim((string) ($validated['location_type'] ?? 'ALL'));
			if ($locationType === '') {
				$locationType = 'ALL';
			}

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

			$smsLbaCampaign = SmsLbaCampaign::create([
				'campaign_name' => $campaignName,
				'sender_name' => $senderName,
				'message' => $message,
				'content_name' => $contentName,
				'recipient_total' => $recipientTotal,
				'delivery_start_date' => $startDate,
				'delivery_end_date' => $endDate,
				'delivery_start_time' => $startTime,
				'delivery_end_time' => $endTime,
				'control_number' => $controlNumber,
				'location_name' => $locationName,
				'location_lat' => $lat,
				'location_lng' => $lng,
				'location_radius' => $radius,
				'location_type' => $locationType,
				'gw_payload' => $payload,
				'status' => 'pending',
			]);

			$resp = $client->campaignLbaAdd($gwToken, $payload, $this->gatewayRefreshCallback($request, $client));
			$campaignId = (string) (data_get($resp, 'data.data.campaignId')
				?? data_get($resp, 'data.data.data.campaignId')
				?? data_get($resp, 'data.campaignId')
				?? '');

			$smsLbaCampaign->update([
				'gw_response' => $resp,
				'gw_campaign_id' => $campaignId !== '' ? $campaignId : null,
				'status' => 'sent',
			]);

			$suffix = $campaignId !== '' ? " (campaignId: {$campaignId})" : '';

			return redirect()
				->route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area'])
				->with('status', 'Campaign LBA berhasil dibuat.' . $suffix);
		} catch (Throwable $e) {
			if ($smsLbaCampaign !== null) {
				$smsLbaCampaign->update([
					'status' => 'failed',
					'error_message' => $e->getMessage(),
				]);
			}

			return back()->withErrors([
				'gw' => $e->getMessage(),
			])->withInput();
		}
	}

	private function generateSmsLbaCampaignName(): string
	{
		return 'gotoads_sms_lba_' . now()->format('YmdHis');
	}

	private function generateSmsTargetedCampaignName(): string
	{
		return 'gotoads_sms_targeted_' . now()->format('YmdHis');
	}

	private function campaignPrices(MyadsApiClient $client, string $gatewayToken, ?callable $refreshGatewayToken = null): array
	{
		$priceData = [];

		foreach (['ALL', 'SMS', 'WABA'] as $campaignType) {
			try {
				$resp = $client->campaignPriceView($gatewayToken, ['campaignType' => $campaignType], $refreshGatewayToken);
				$data = data_get($resp, 'data.data') ?? data_get($resp, 'data') ?? [];
				if (is_array($data)) {
					$priceData = array_merge($priceData, $this->flattenPriceData($data));
				}
			} catch (Throwable) {
				// keep trying other campaign types
			}
		}

		return [
			'sms_lba' => $this->findPriceValue($priceData, ['LBA_SMS', 'SMS_LBA', 'SMS_LOCATION_BASED_AREA', 'LOCATION_BASED_AREA_SMS']),
			'sms_targeted' => $this->findPriceValue($priceData, ['TARGETED_SMS', 'SMS_TARGETED']),
			'wa_lba' => $this->findPriceValue($priceData, ['WABA_LBA', 'WA_LBA', 'WHATSAPP_LBA', 'LBA_WABA']),
			'wa_targeted' => $this->findPriceValue($priceData, ['WABA_TARGETED', 'WA_TARGETED', 'WHATSAPP_TARGETED', 'TARGETED_WABA']),
		];
	}

	private function flattenPriceData(array $data, string $prefix = ''): array
	{
		$result = [];

		foreach ($data as $key => $value) {
			$flatKey = $prefix === '' ? (string) $key : $prefix . '_' . (string) $key;
			if (is_array($value)) {
				$result = array_merge($result, $this->flattenPriceData($value, $flatKey));
				continue;
			}

			$result[strtoupper($flatKey)] = $value;
		}

		return $result;
	}

	private function findPriceValue(array $priceData, array $keys): ?float
	{
		foreach ($keys as $key) {
			$value = $priceData[strtoupper($key)] ?? null;
			if (is_numeric($value)) {
				return (float) $value;
			}
		}

		foreach ($priceData as $key => $value) {
			if (! is_numeric($value)) {
				continue;
			}

			foreach ($keys as $candidate) {
				if (str_contains((string) $key, strtoupper($candidate))) {
					return (float) $value;
				}
			}
		}

		return null;
	}

	private function requireGatewayToken(Request $request): string
	{
		$gwToken = (string) $request->session()->get('myads.gw_token', '');
		if (trim($gwToken) === '') {
			/** @var MyadsApiClient $client */
			$client = app(MyadsApiClient::class);
			$gwToken = ($this->gatewayRefreshCallback($request, $client))();
		}

		return $gwToken;
	}

	private function requireAccessToken(Request $request): string
	{
		$accessToken = (string) $request->session()->get('myads.access_token', '');
		if (trim($accessToken) === '') {
			throw new RuntimeException('Token akses tidak ditemukan. Silakan login ulang.');
		}

		return $accessToken;
	}

	private function gatewayRefreshCallback(Request $request, MyadsApiClient $client): callable
	{
		return function () use ($request, $client): string {
			$gwResp = $client->getGatewayToken((string) $request->session()->get('myads.access_token', ''));
			$gwToken = $client->extractGatewayToken($gwResp);

			if ($gwToken === '') {
				throw new RuntimeException('Gagal ambil token gateway (format response tidak sesuai).');
			}

			$request->session()->put('myads.gw_token', $gwToken);

			return $gwToken;
		};
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

	private function pipeProfileValue(array $values, array $map = [], bool $uppercase = false): ?string
	{
		$cleaned = array_values(array_unique(array_filter(array_map(static function ($value) use ($map, $uppercase): string {
			$value = trim((string) $value);
			$mapped = $map[$value] ?? $map[strtolower($value)] ?? $value;
			$mapped = trim((string) $mapped);

			return $uppercase ? strtoupper($mapped) : $mapped;
		}, $values), static fn (string $value): bool => $value !== '')));

		return $cleaned === [] ? null : implode('|', $cleaned);
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
