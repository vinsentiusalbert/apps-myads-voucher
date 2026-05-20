<?php

namespace App\Http\Controllers\APIGW;

use App\Http\Controllers\Controller;
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
			$accessToken = $this->ensureValidAccessToken($request, $client);
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
			$accessToken = $this->ensureValidAccessToken($request, $client);
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
		abort_unless(in_array($channel, ['sms', 'wa-business'], true), 404);

		try {
			$profile = strtoupper(trim((string) $request->input('profile', 'ALL')));
			$payload = ['profile' => $profile !== '' ? $profile : 'ALL'];

			foreach (['province', 'kota', 'kecamatan'] as $field) {
				$value = trim((string) $request->input($field, ''));
				if ($value !== '') {
					$payload[$field] = $value;
				}
			}

			$resp = $client->profileDropdownList(
				$this->requireGatewayToken($request),
				$payload,
				$this->gatewayRefreshCallback($request, $client)
			);

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
			'sender_name' => ['required', 'string', 'max:11'],
			'message' => ['required', 'string', 'max:1024'],
			'recipient_total' => ['required', 'integer', 'min:1', 'max:100000'],
			'delivery_date' => ['nullable', 'date_format:Y-m-d'],
			'delivery_time' => ['nullable', 'date_format:H:i'],
			'control_number' => ['required', 'string', 'max:1000'],
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

		try {
			$paymentTransactionId = trim((string) ($validated['payment_transaction_id'] ?? ''));
			if ($paymentTransactionId === '') {
				throw new RuntimeException('Pembayaran belum diinisiasi.');
			}
			$paymentResp = $client->paymentStatus($this->ensureValidAccessToken($request, $client), $paymentTransactionId);
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

			$controlNumbers = $this->normalizeControlNumbers((string) ($validated['control_number'] ?? ''));

			$mmsFile = trim((string) ($validated['mms_file'] ?? ''));

			if ($campaignName === '' || $senderName === '' || $message === '' || $recipientTotal <= 0 || $controlNumbers === [] || $deliveryDate === null || $deliveryTime === null) {
				throw new RuntimeException('Data belum lengkap. Mohon lengkapi judul, pengirim, isi pesan, jumlah penerima, jadwal kirim, dan nomor kontrol.');
			}
			$this->ensureScheduleStartsAtLeastFourHoursFromNow($validated['delivery_date'] ?? null, $validated['delivery_time'] ?? null);

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
				'controlNumbers' => $controlNumbers,
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

			$resp = $client->campaignTargetedAdd($gwToken, $payload, $this->gatewayRefreshCallback($request, $client));
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
			'payment_transaction_id' => ['required', 'string', 'max:128'],
			'campaign_name' => ['nullable', 'string', 'max:255'],
			'sender_name' => ['required', 'string', 'max:11'],
			'message' => ['required', 'string', 'max:1024'],
			'content_name' => ['nullable', 'string', 'max:255'],
			'recipient_total' => ['required', 'integer', 'min:1', 'max:100000'],
			'delivery_start_date' => ['nullable', 'date_format:Y-m-d'],
			'delivery_end_date' => ['nullable', 'date_format:Y-m-d'],
			'delivery_start_time' => ['nullable', 'date_format:H:i'],
			'delivery_end_time' => ['nullable', 'date_format:H:i'],
			'control_number' => ['required', 'string', 'max:1000'],
			'location_name' => ['required', 'string', 'max:255'],
			'location_lat' => ['required', 'numeric'],
			'location_lng' => ['required', 'numeric'],
			'location_radius' => ['required', 'integer', 'min:1', 'max:50000'],
			'location_type' => ['nullable', 'string', 'in:ALL,HOME,MALL'],
		]);

		try {
			$paymentTransactionId = trim((string) ($validated['payment_transaction_id'] ?? ''));
			if ($paymentTransactionId === '') {
				throw new RuntimeException('Pembayaran belum diinisiasi.');
			}
			$paymentResp = $client->paymentStatus($this->ensureValidAccessToken($request, $client), $paymentTransactionId);
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

			$controlNumbers = $this->normalizeControlNumbers((string) ($validated['control_number'] ?? ''));

			$locationName = trim((string) ($validated['location_name'] ?? ''));
			$lat = (float) ($validated['location_lat'] ?? 0);
			$lng = (float) ($validated['location_lng'] ?? 0);
			$radius = (int) ($validated['location_radius'] ?? 0);
			$locationType = trim((string) ($validated['location_type'] ?? 'ALL'));
			if ($locationType === '') {
				$locationType = 'ALL';
			}

			if ($campaignName === '' || $senderName === '' || $message === '' || $contentName === '' || $recipientTotal <= 0 || $controlNumbers === [] || $startDate === null || $endDate === null || $startTime === null || $endTime === null || $locationName === '' || $radius <= 0 || $locationType === '') {
				throw new RuntimeException('Data belum lengkap. Mohon lengkapi semua field sebelum mengirim campaign.');
			}
			$this->ensureScheduleStartsAtLeastFourHoursFromNow($validated['delivery_start_date'] ?? null, $validated['delivery_start_time'] ?? null);

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
				'controlNumbers' => $controlNumbers,
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

			$resp = $client->campaignLbaAdd($gwToken, $payload, $this->gatewayRefreshCallback($request, $client));
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

	public function wabaLbaAdd(Request $request, MyadsApiClient $client): RedirectResponse
	{
		$validated = $request->validate([
			'payment_transaction_id' => ['required', 'string', 'max:128'],
			'campaign_name' => ['nullable', 'string', 'max:255'],
			'recipient_total' => ['required', 'integer', 'min:1', 'max:100000'],
			'waba_user_template_id' => ['required', 'integer', 'min:1'],
			'delivery_start_date' => ['required', 'date_format:Y-m-d'],
			'delivery_end_date' => ['required', 'date_format:Y-m-d'],
			'delivery_start_time' => ['required', 'date_format:H:i'],
			'delivery_end_time' => ['required', 'date_format:H:i'],
			'control_number' => ['required', 'string', 'max:1000'],
			'location_name' => ['required', 'string', 'max:255'],
			'location_lat' => ['required', 'numeric'],
			'location_lng' => ['required', 'numeric'],
			'location_radius' => ['required', 'integer', 'min:1', 'max:50000'],
			'location_type' => ['nullable', 'string', 'in:ALL,HOME,MALL'],
			'recipient_max' => ['nullable', 'string', 'max:64'],
			'profile_gender' => ['nullable', 'string', 'max:255'],
			'profile_age' => ['nullable', 'string', 'max:255'],
			'profile_religion' => ['nullable', 'string', 'max:255'],
		]);

		try {
			$paymentTransactionId = trim((string) ($validated['payment_transaction_id'] ?? ''));
			$paymentResp = $client->paymentStatus($this->requireAccessToken($request), $paymentTransactionId);
			$paymentStatus = strtoupper(trim((string) data_get($paymentResp, 'data.status')));
			if ($paymentStatus !== 'SUCCESS') {
				$label = $paymentStatus !== '' ? $paymentStatus : 'PENDING';
				throw new RuntimeException('Pembayaran belum berhasil. Status: ' . $label);
			}

			$campaignName = trim((string) ($validated['campaign_name'] ?? ''));
			if ($campaignName === '') {
				$campaignName = $this->generateWabaLbaCampaignName();
			}
			$recipientTotal = (int) ($validated['recipient_total'] ?? 0);
			$wabaUserTemplateId = (int) ($validated['waba_user_template_id'] ?? 0);
			$startDate = $this->formatDateYmd($validated['delivery_start_date'] ?? null);
			$endDate = $this->formatDateYmd($validated['delivery_end_date'] ?? null);
			$startTime = $this->formatTimeHiToHmm($validated['delivery_start_time'] ?? null);
			$endTime = $this->formatTimeHiToHmm($validated['delivery_end_time'] ?? null);
			$controlNumbers = $this->normalizeControlNumbers((string) ($validated['control_number'] ?? ''));
			$locationName = trim((string) ($validated['location_name'] ?? ''));
			$lat = (float) ($validated['location_lat'] ?? 0);
			$lng = (float) ($validated['location_lng'] ?? 0);
			$radius = (int) ($validated['location_radius'] ?? 0);
			$locationType = trim((string) ($validated['location_type'] ?? 'ALL'));
			$recipientMax = str_replace('.', '', trim((string) ($validated['recipient_max'] ?? '')));

			if ($locationType === '') {
				$locationType = 'ALL';
			}

			if ($campaignName === '' || $recipientTotal <= 0 || $wabaUserTemplateId <= 0 || $controlNumbers === [] || $startDate === null || $endDate === null || $startTime === null || $endTime === null || $locationName === '' || $radius <= 0 || $locationType === '') {
				throw new RuntimeException('Data belum lengkap. Mohon lengkapi semua field sebelum mengirim campaign WABA LBA.');
			}
			$this->ensureScheduleStartsAtLeastFourHoursFromNow($validated['delivery_start_date'] ?? null, $validated['delivery_start_time'] ?? null);

			$payload = [
				'campaignName' => $campaignName,
				'recepientTotal' => $recipientTotal,
				'wabaUserTemplateId' => $wabaUserTemplateId,
				'deliveries' => [
					'deliveryEndDate' => $endDate,
					'deliveryEndTime' => $endTime,
					'deliveryStartDate' => $startDate,
					'deliveryStartTime' => $startTime,
				],
				'controlNumbers' => $controlNumbers,
				'contents' => [
					'locations' => [
						'address' => $locationName,
						'latitude' => $lat,
						'longitude' => $lng,
						'locationNameMap' => $locationName,
						'locationNameUser' => $locationName,
						'radius' => $radius,
						'locationType' => $locationType,
						'recepientMax' => $recipientMax,
					],
					'profileGender' => trim((string) ($validated['profile_gender'] ?? '')),
					'profileAge' => trim((string) ($validated['profile_age'] ?? '')),
					'profileMobileDataSubs' => '',
					'profileArpu' => '0-10000000',
					'profileArpuMin' => '0',
					'profileArpuMax' => '10000000',
					'profileReligion' => trim((string) ($validated['profile_religion'] ?? '')),
					'profileOs' => '',
					'profileCardType' => '',
					'profileHometown' => '',
					'profileMaritalStatus' => '',
					'profileLifeStyleSegment' => '',
				],
			];

			$resp = $client->wabaLbaAdd($this->requireGatewayToken($request), $payload, $this->gatewayRefreshCallback($request, $client));
			$campaignId = (string) (data_get($resp, 'data.data.campaignId')
				?? data_get($resp, 'data.data.data.campaignId')
				?? data_get($resp, 'data.campaignId')
				?? '');
			$suffix = $campaignId !== '' ? " (campaignId: {$campaignId})" : '';

			return redirect()
				->route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'location-based-area'])
				->with('status', 'Campaign WABA LBA berhasil dibuat.' . $suffix);
		} catch (Throwable $e) {
			return back()->withErrors([
				'gw' => $e->getMessage(),
			])->withInput();
		}
	}

	public function wabaTargetedAdd(Request $request, MyadsApiClient $client): RedirectResponse
	{
		$validated = $request->validate([
			'payment_transaction_id' => ['required', 'string', 'max:128'],
			'campaign_name' => ['nullable', 'string', 'max:255'],
			'waba_user_template_id' => ['required', 'integer', 'min:1'],
			'recipient_total' => ['required', 'integer', 'min:1', 'max:100000'],
			'delivery_date' => ['required', 'date_format:Y-m-d'],
			'delivery_time' => ['required', 'date_format:H:i'],
			'control_number' => ['required', 'string', 'max:1000'],
			'profile_gender' => ['nullable', 'string', 'max:500'],
			'profile_age' => ['nullable', 'string', 'max:500'],
			'profile_mobile_data_subs' => ['nullable', 'string', 'max:500'],
			'profile_arpu' => ['nullable', 'string', 'max:500'],
			'profile_os' => ['nullable', 'string', 'max:500'],
			'profile_marital_status' => ['nullable', 'string', 'max:500'],
			'profile_lifestyle_segment' => ['nullable', 'string', 'max:500'],
			'profile_religion' => ['nullable', 'string', 'max:500'],
			'profile_card_type' => ['nullable', 'string', 'max:500'],
			'profile_hometown' => ['nullable', 'string', 'max:500'],
			'profile_work' => ['nullable', 'string', 'max:500'],
		]);

		try {
			$paymentTransactionId = trim((string) ($validated['payment_transaction_id'] ?? ''));
			$paymentResp = $client->paymentStatus($this->requireAccessToken($request), $paymentTransactionId);
			$paymentStatus = strtoupper(trim((string) data_get($paymentResp, 'data.status')));
			if ($paymentStatus !== 'SUCCESS') {
				$label = $paymentStatus !== '' ? $paymentStatus : 'PENDING';
				throw new RuntimeException('Pembayaran belum berhasil. Status: ' . $label);
			}

			$campaignName = trim((string) ($validated['campaign_name'] ?? ''));
			if ($campaignName === '') {
				$campaignName = $this->generateWabaTargetedCampaignName();
			}

			$deliveryDate = $this->formatDateYmd($validated['delivery_date'] ?? null);
			$deliveryTime = $this->formatTimeHiToHmm($validated['delivery_time'] ?? null);
			$controlNumbers = $this->normalizeControlNumbers((string) ($validated['control_number'] ?? ''));
			$recipientTotal = (int) ($validated['recipient_total'] ?? 0);
			$wabaUserTemplateId = (int) ($validated['waba_user_template_id'] ?? 0);

			if ($campaignName === '' || $wabaUserTemplateId <= 0 || $recipientTotal <= 0 || $deliveryDate === null || $deliveryTime === null || $controlNumbers === []) {
				throw new RuntimeException('Data belum lengkap. Mohon lengkapi semua field sebelum mengirim campaign WABA Targeted.');
			}
			$this->ensureScheduleStartsAtLeastFourHoursFromNow($validated['delivery_date'] ?? null, $validated['delivery_time'] ?? null);

			$emptyToNull = static function (?string $value): ?string {
				$value = trim((string) $value);
				return $value === '' ? null : $value;
			};

			$payload = [
				'wabaUserTemplateId' => $wabaUserTemplateId,
				'campaignName' => $campaignName,
				'profileGender' => $emptyToNull($validated['profile_gender'] ?? null),
				'profileAge' => $emptyToNull($validated['profile_age'] ?? null),
				'profileMobileDataSubs' => $emptyToNull($validated['profile_mobile_data_subs'] ?? null),
				'profileArpu' => $emptyToNull($validated['profile_arpu'] ?? null),
				'profileOs' => $emptyToNull($validated['profile_os'] ?? null),
				'profileMaritalStatus' => $emptyToNull($validated['profile_marital_status'] ?? null),
				'profileLifestyleSegment' => $emptyToNull($validated['profile_lifestyle_segment'] ?? null),
				'profileReligion' => $emptyToNull($validated['profile_religion'] ?? null),
				'profileCardType' => trim((string) ($validated['profile_card_type'] ?? '')),
				'profileHometown' => trim((string) ($validated['profile_hometown'] ?? '')),
				'profileWork' => $emptyToNull($validated['profile_work'] ?? null),
				'recepientTotal' => $recipientTotal,
				'deliveries' => [
					[
						'deliveryDate' => $deliveryDate,
						'deliveryTime' => $deliveryTime,
					],
				],
				'controlNumbers' => $controlNumbers,
			];

			$resp = $client->wabaTargetedAdd($this->requireGatewayToken($request), $payload, $this->gatewayRefreshCallback($request, $client));
			$campaignId = (string) (data_get($resp, 'data.data.campaignId')
				?? data_get($resp, 'data.data.data.campaignId')
				?? data_get($resp, 'data.campaignId')
				?? '');
			$suffix = $campaignId !== '' ? " (campaignId: {$campaignId})" : '';

			return redirect()
				->route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'targeted'])
				->with('status', 'Campaign WABA Targeted berhasil dibuat.' . $suffix);
		} catch (Throwable $e) {
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

	private function generateWabaLbaCampaignName(): string
	{
		return 'gotoads_waba_lba_' . now()->format('YmdHis');
	}

	private function generateWabaTargetedCampaignName(): string
	{
		return 'gotoads_waba_targeted_' . now()->format('YmdHis');
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

	/**
	 * Ensure access token is valid, auto-refresh jika sudah expired
	 * Throws exception jika refresh gagal
	 */
	private function ensureValidAccessToken(Request $request, MyadsApiClient $client): string
	{
		try {
			$accessToken = $this->requireAccessToken($request);
			return $accessToken;
		} catch (RuntimeException $e) {
			// Token kosong, coba refresh
			$refreshToken = (string) $request->session()->get('myads.refresh_token', '');
			if (trim($refreshToken) === '') {
				throw new RuntimeException('Session expired. Silakan login ulang.');
			}

			try {
				$resp = $client->refreshAccessToken($refreshToken);
				$newAccessToken = (string) data_get($resp, 'data.access_token', '');
				if (trim($newAccessToken) === '') {
					throw new RuntimeException('Token akses tidak valid dari server.');
				}

				// Update session dengan token baru
				$request->session()->put('myads.access_token', $newAccessToken);

				return $newAccessToken;
			} catch (Throwable $refreshError) {
				throw new RuntimeException('Token sudah kadaluarsa dan refresh gagal. Silakan login ulang.');
			}
		}
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

	private function ensureScheduleStartsAtLeastFourHoursFromNow(?string $date, ?string $time): void
	{
		$date = trim((string) $date);
		$time = trim((string) $time);
		if ($date === '' || $time === '') {
			throw new RuntimeException('Jadwal pengiriman belum lengkap.');
		}

		try {
			$startAt = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", config('app.timezone'));
		} catch (Throwable) {
			throw new RuntimeException('Format jadwal pengiriman tidak valid.');
		}

		if ($startAt->lt(Carbon::now(config('app.timezone'))->addHours(4))) {
			throw new RuntimeException('Jadwal pengiriman minimal 4 jam dari waktu sekarang.');
		}
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

	private function normalizeControlNumbers(string $value): array
	{
		$numbers = preg_split('/[,|]+/', $value) ?: [];

		return array_values(array_unique(array_filter(array_map(function ($number): ?string {
			return $this->normalizeMsisdn((string) $number);
		}, $numbers), static fn (?string $number): bool => $number !== null && $number !== '')));
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
