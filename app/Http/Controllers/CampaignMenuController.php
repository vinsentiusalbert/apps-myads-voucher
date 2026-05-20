<?php

namespace App\Http\Controllers;

use App\Services\MyadsApiClient;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CampaignMenuController extends Controller
{
    public function show(Request $request, string $channel, string $menu): View
    {
        $pages = $this->pages();

        abort_unless(isset($pages[$channel][$menu]), 404);

        $page = $pages[$channel][$menu];

        if ($channel === 'wa-business' && $menu === 'campaign-template') {
            return $this->listCampaignTemplates($request);
        }

        if ($channel === 'wa-business' && $menu === 'location-based-area') {
            $rows = collect($this->campaignRowsFromApi($request, $channel, $menu));
            $view = $request->query('view');

            if ($view === 'create') {
                return view('campaign-wa-lba.create', [
                    'channel' => $channel,
                    'menu' => $menu,
                    'page' => $page,
                    'waTemplates' => $this->resolveWaTemplates(),
                ]);
            }

            if ($view === 'show') {
                $campaignRow = $rows->firstWhere('id', (string) $request->query('id')) ?? $rows->first();
                abort_unless(is_array($campaignRow), 404);

                return view('campaign-wa-lba.show', [
                    'channel' => $channel,
                    'menu' => $menu,
                    'page' => $page,
                    'campaignRow' => $campaignRow,
                    'waTemplates' => $this->resolveWaTemplates(),
                ]);
            }

            return view('campaign-wa-lba.index', [
                'channel' => $channel,
                'menu' => $menu,
                'page' => $page,
                'campaignRows' => $rows,
            ]);
        }

        if ($channel === 'wa-business' && $menu === 'targeted') {
            $rows = collect($this->campaignRowsFromApi($request, $channel, $menu));
            $view = $request->query('view');

            if ($view === 'create') {
                return view('campaign-wa-targeted.create', [
                    'channel' => $channel,
                    'menu' => $menu,
                    'page' => $page,
                    'waTemplates' => $this->resolveWaTemplates(),
                ]);
            }

            if ($view === 'show') {
                $campaignRow = $rows->firstWhere('id', (string) $request->query('id')) ?? $rows->first();
                abort_unless(is_array($campaignRow), 404);

                return view('campaign-wa-targeted.show', [
                    'channel' => $channel,
                    'menu' => $menu,
                    'page' => $page,
                    'campaignRow' => $campaignRow,
                    'waTemplates' => $this->resolveWaTemplates(),
                ]);
            }

            return view('campaign-wa-targeted.index', [
                'channel' => $channel,
                'menu' => $menu,
                'page' => $page,
                'campaignRows' => $rows,
            ]);
        }

        if ($menu === 'location-based-area') {
            $rows = collect($this->campaignRowsFromApi($request, $channel, $menu));
            $view = $request->query('view');

            if ($view === 'create') {
                $unitPrice = $this->resolveUnitPrice($request, $channel, $menu);
                return view('campaign-lba.create', [
                    'channel' => $channel,
                    'menu' => $menu,
                    'page' => $page,
                    'unitPrice' => $unitPrice,
                ]);
            }

            if ($view === 'show') {
                $campaignRow = $rows->firstWhere('id', (string) $request->query('id'))
                    ?? $rows->first()
                    ?? $this->fallbackCampaignRow($request, $channel, $menu, $page);

                return view('campaign-lba.show', [
                    'channel' => $channel,
                    'menu' => $menu,
                    'page' => $page,
                    'campaignRow' => $campaignRow,
                    'campaignReport' => $this->campaignReportFromApi($request, (string) ($campaignRow['id'] ?? '')),
                ]);
            }

            return view('campaign-lba.index', [
                'channel' => $channel,
                'menu' => $menu,
                'page' => $page,
                'campaignRows' => $rows,
            ]);
        }

        if ($menu === 'targeted') {
            $rows = collect($this->campaignRowsFromApi($request, $channel, $menu));
            $view = $request->query('view');

            if ($view === 'create') {
                $unitPrice = $this->resolveUnitPrice($request, $channel, $menu);
                return view('campaign-targeted.create', [
                    'channel' => $channel,
                    'menu' => $menu,
                    'page' => $page,
                    'unitPrice' => $unitPrice,
                ]);
            }

            if ($view === 'show') {
                $campaignRow = $rows->firstWhere('id', (string) $request->query('id'))
                    ?? $rows->first()
                    ?? $this->fallbackCampaignRow($request, $channel, $menu, $page);

                return view('campaign-targeted.show', [
                    'channel' => $channel,
                    'menu' => $menu,
                    'page' => $page,
                    'campaignRow' => $campaignRow,
                    'campaignReport' => $this->campaignReportFromApi($request, (string) ($campaignRow['id'] ?? '')),
                ]);
            }

            return view('campaign-targeted.index', [
                'channel' => $channel,
                'menu' => $menu,
                'page' => $page,
                'campaignRows' => $rows,
            ]);
        }

        return view('campaign-menu', [
            'channel' => $channel,
            'menu' => $menu,
            'page' => $page,
        ]);
    }

    public function listCampaignTemplates(Request $request): View
    {
        $page = $this->pages()['wa-business']['campaign-template'];
        $templates = collect($this->wabaTemplateRowsFromApi($request))
            ->when($request->filled('q'), function ($rows) use ($request) {
                $search = strtolower($request->string('q')->toString());

                return $rows->filter(function (array $row) use ($search): bool {
                    return str_contains(strtolower((string) ($row['name'] ?? '')), $search)
                        || str_contains(strtolower((string) ($row['body'] ?? '')), $search);
                });
            })
            ->when($request->filled('category'), function ($rows) use ($request) {
                $category = $request->string('category')->toString();

                return $rows->filter(fn (array $row): bool => (string) ($row['category'] ?? '') === $category);
            })
            ->when($request->filled('start_date'), function ($rows) use ($request) {
                $startDate = $request->date('start_date');
                if (! $startDate) {
                    return $rows;
                }

                return $rows->filter(fn (array $row): bool => isset($row['created_at']) && $row['created_at'] >= $startDate->startOfDay());
            })
            ->when($request->filled('end_date'), function ($rows) use ($request) {
                $endDate = $request->date('end_date');
                if (! $endDate) {
                    return $rows;
                }

                return $rows->filter(fn (array $row): bool => isset($row['created_at']) && $row['created_at'] <= $endDate->endOfDay());
            })
            ->sortByDesc(fn (array $row) => $row['created_at'] ?? now())
            ->values();

        if ($request->query('view') === 'create') {
            return view('campaign-wa-template.create', [
                'channel' => 'wa-business',
                'menu' => 'campaign-template',
                'page' => $page,
            ]);
        }

        return view('campaign-wa-template.index', [
            'channel' => 'wa-business',
            'menu' => 'campaign-template',
            'page' => $page,
            'templateRows' => $templates,
            'templateCount' => $templates->count(),
            'approvedTemplateCount' => $templates->filter(fn (array $row): bool => strtoupper((string) ($row['status'] ?? '')) === 'APPROVED')->count(),
        ]);
    }

    public function storeCampaignTemplate(Request $request, MyadsApiClient $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'template_type' => ['required', 'string', 'max:50'],
            'category' => ['required', 'string', 'max:100'],
            'language' => ['required', 'string', 'max:100'],
            'header_type' => ['required', 'string', 'max:50'],
            'header_label' => ['nullable', 'required_if:header_type,TEXT', 'string', 'max:60'],
            'asset' => ['nullable', 'required_if:header_type,IMAGE', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'body' => ['required', 'string', 'max:550'],
            'footer' => ['nullable', 'string', 'max:60'],
            'display_name' => ['required', 'string', 'max:255'],
            'waba_account_id' => ['nullable', 'string', 'max:32'],
            'phone_number_id' => ['nullable', 'string', 'max:32'],
            'buttons' => ['nullable', 'array'],
            'buttons.*.label' => ['nullable', 'string', 'max:255'],
            'buttons.*.action' => ['nullable', 'string', 'max:50'],
            'buttons.*.show_modal_short_url' => ['nullable', 'boolean'],
            'buttons.*.action_replay' => ['nullable', 'string', 'max:255'],
            'buttons.*.action_number' => ['nullable', 'string', 'max:20'],
            'buttons.*.action_url_target' => ['nullable', 'string', 'max:255'],
            'buttons.*.short_url' => ['nullable', 'string', 'max:255'],
            'buttons.*.action_voucher_code' => ['nullable', 'string', 'max:50'],
        ], [
            'header_label.required_if' => 'Header Text wajib diisi jika Header Type adalah Teks.',
            'asset.required_if' => 'Upload gambar wajib diisi jika Header Type adalah Gambar.',
            'asset.mimes' => 'Format gambar header harus jpg, jpeg, png, atau webp.',
            'asset.max' => 'Ukuran gambar header maksimal 5MB.',
        ]);

        try {
            $accessToken = $this->requireAccessToken($request);
            $headerPathFile = '';
            $gwToken = $this->requireGatewayToken($request);
            $accountIds = $this->resolveWabaAccountIds($request, $client, $gwToken, $validated);

            if ($request->hasFile('asset') && $this->mapWabaHeaderType($validated['header_type']) === 'IMAGE') {
                $uploadResp = $client->contentUpload($gwToken, $request->file('asset'), $accountIds['phoneNumberId'], $this->gatewayRefreshCallback($request, $client));
                $headerPathFile = $this->extractUploadedFileValue($uploadResp);

                if ($headerPathFile === '') {
                    throw new RuntimeException('Upload asset berhasil dipanggil, tetapi pathFile tidak ditemukan dari response API GW.');
                }
            }

            $payload = $this->buildWabaTemplatePayload(
                $validated,
                $headerPathFile,
                $accountIds,
            );

            $resp = $client->wabaTemplateAdd($accessToken, $payload, $gwToken, $this->gatewayRefreshCallback($request, $client));
        } catch (Throwable $e) {
            return back()->withErrors([
                'gw' => $e->getMessage(),
            ])->withInput();
        }

        return redirect()
            ->route('campaign-template.index')
            ->with('status', 'Template WABA berhasil dikirim ke API GW.');
    }

    public function showCampaignTemplate(Request $request, string $template): View
    {
        $request->query->set('template_id', $template);

        return $this->showCampaignTemplateFromApi($request);
    }

    public function showCampaignTemplateFromApi(Request $request): View
    {
        $templateId = trim((string) $request->query('template_id', ''));
        $name = trim((string) $request->query('name', ''));

        abort_if($templateId === '' && $name === '', 404);

        $row = collect($this->wabaTemplateRowsFromApi($request))
            ->first(function (array $row) use ($templateId, $name): bool {
                $rowTemplateId = trim((string) ($row['gw_template_id'] ?? ''));
                $rowName = trim((string) ($row['name'] ?? ''));

                return ($templateId !== '' && $rowTemplateId === $templateId)
                    || ($name !== '' && $rowName === $name);
            });

        abort_unless(is_array($row), 404);

        return view('campaign-wa-template.show', [
            'channel' => 'wa-business',
            'menu' => 'campaign-template',
            'page' => $this->pages()['wa-business']['campaign-template'],
            'templateRow' => $this->apiTemplateRowForShow($row),
        ]);
    }

    private function resolveWaTemplates()
    {
        $request = request();

        if (! $request instanceof Request) {
            return collect();
        }

        return collect($this->wabaTemplateRowsFromApi($request))
            ->filter(fn (array $row): bool => trim((string) ($row['gw_template_id'] ?? $row['id'] ?? '')) !== '')
            ->map(fn (array $row): array => $this->mapApiWaTemplateForSelect($row))
            ->values();
    }

    private function buildWabaTemplatePayload(array $validated, string $headerPathFile, array $accountIds): array
    {
        $headerType = $this->mapWabaHeaderType((string) $validated['header_type']);
        $buttons = $this->mapWabaTemplateButtons($validated['buttons'] ?? []);

        return [
            'templateName' => trim((string) $validated['name']),
            'language' => $this->mapWabaTemplateLanguage((string) $validated['language']),
            ...($headerType !== 'NONE' ? [
                'headerType' => $headerType,
                'headerLabel' => $headerType === 'TEXT' ? trim((string) ($validated['header_label'] ?? '')) : '',
                'headerPathFile' => $headerType === 'IMAGE' ? $headerPathFile : '',
            ] : []),
            'bodyMessage' => $validated['body'],
            'bodyType' => 'STATIC',
            'bodyPathFile' => '',
            'useButton' => $buttons === [] ? 0 : 1,
            'buttons' => $buttons,
            'wabaAccountId' => $accountIds['wabaAccountId'],
            'phoneNumberId' => $accountIds['phoneNumberId'],
        ];
    }

    private function mapWabaTemplateButtons(array $buttons): array
    {
        return collect($buttons)
            ->filter(fn ($button): bool => is_array($button) && trim((string) ($button['label'] ?? '')) !== '')
            ->map(function (array $button): array {
                $action = strtoupper((string) ($button['action'] ?? 'URL'));
                if (! in_array($action, ['QUICK_REPLY', 'URL', 'PHONE_NUMBER'], true)) {
                    $action = 'URL';
                }

                $label = trim((string) ($button['label'] ?? ''));
                $isUrlAction = $action === 'URL';

                return [
                    'showModalShortUrl' => $isUrlAction ? (bool) ($button['show_modal_short_url'] ?? false) : false,
                    'label' => $label,
                    'action' => $action,
                    'actionReplay' => trim((string) ($button['action_replay'] ?? '')),
                    'actionNumber' => trim((string) ($button['action_number'] ?? '')),
                    'actionUrlTarget' => $isUrlAction ? trim((string) ($button['action_url_target'] ?? '')) : '',
                    'shortUrl' => $isUrlAction ? ($button['short_url'] ?? null) : null,
                    'actionVoucherCode' => trim((string) ($button['action_voucher_code'] ?? '')),
                ];
            })
            ->values()
            ->all();
    }

    private function mapWabaTemplateLanguage(string $language): string
    {
        $language = strtolower(trim($language));

        return match ($language) {
            'english', 'en' => 'ENGLISH',
            default => 'INDONESIA',
        };
    }

    private function mapWabaHeaderType(string $headerType): string
    {
        $headerType = strtolower(trim($headerType));

        return match ($headerType) {
            'none' => 'NONE',
            'image' => 'IMAGE',
            'text' => 'TEXT',
            default => 'TEXT',
        };
    }

    /**
     * @return array{wabaAccountId:int|string, phoneNumberId:int|string}
     */
    private function resolveWabaAccountIds(Request $request, MyadsApiClient $client, string $gatewayToken, array $validated = []): array
    {
        $wabaAccountId = trim((string) ($validated['waba_account_id'] ?? ''));
        $phoneNumberId = trim((string) ($validated['phone_number_id'] ?? ''));

        if ($wabaAccountId !== '' && $phoneNumberId !== '') {
            return [
                'wabaAccountId' => $this->normalizeNumericId($wabaAccountId),
                'phoneNumberId' => $this->normalizeNumericId($phoneNumberId),
            ];
        }

        $displayName = trim((string) ($validated['display_name'] ?? ''));
        if ($displayName === '') {
            throw new RuntimeException('Display name wajib dipilih dari list API.');
        }

        $resp = $client->wabaDisplayNameList($this->requireAccessToken($request), [], $gatewayToken, $this->gatewayRefreshCallback($request, $client));
        $items = data_get($resp, 'data.data.list')
            ?? data_get($resp, 'data.data.items')
            ?? data_get($resp, 'data.list')
            ?? data_get($resp, 'data.items')
            ?? data_get($resp, 'data.data')
            ?? data_get($resp, 'data')
            ?? [];

        if (is_array($items) && array_is_list($items)) {
            $selected = collect($items)->first(function ($row) use ($displayName): bool {
                if (! is_array($row)) {
                    return false;
                }

                $apiDisplayName = trim((string) (data_get($row, 'verifiedName')
                    ?? data_get($row, 'verified_name')
                    ?? data_get($row, 'displayName')
                    ?? data_get($row, 'display_name')
                    ?? data_get($row, 'name')
                    ?? ''));

                return $apiDisplayName !== '' && hash_equals($displayName, $apiDisplayName);
            });

            if (is_array($selected)) {
                $wabaAccountId = (string) (data_get($selected, 'wabaAccountId') ?? '');
                $phoneNumberId = (string) (data_get($selected, 'phoneNumberId') ?? data_get($selected, 'phoneNumberiId') ?? '');
            }
        }

        if ($wabaAccountId === '' || $phoneNumberId === '') {
            throw new RuntimeException('WABA Account ID dan Phone Number ID belum tersedia untuk display name yang dipilih dari API.');
        }

        return [
            'wabaAccountId' => $this->normalizeNumericId($wabaAccountId),
            'phoneNumberId' => $this->normalizeNumericId($phoneNumberId),
        ];
    }

    private function normalizeNumericId(string $value): int|string
    {
        $value = trim($value);

        if ($value !== '' && ctype_digit($value) && strlen($value) < strlen((string) PHP_INT_MAX)) {
            return (int) $value;
        }

        if ($value !== '' && ctype_digit($value) && strlen($value) === strlen((string) PHP_INT_MAX) && strcmp($value, (string) PHP_INT_MAX) <= 0) {
            return (int) $value;
        }

        return $value;
    }

    private function extractUploadedFileValue(array $uploadResp): string
    {
        $value = data_get($uploadResp, 'data.data.pathFile')
            ?? data_get($uploadResp, 'data.data.filePath')
            ?? data_get($uploadResp, 'data.data.path')
            ?? data_get($uploadResp, 'data.data.file')
            ?? data_get($uploadResp, 'data.pathFile')
            ?? data_get($uploadResp, 'data.filePath')
            ?? data_get($uploadResp, 'data.path')
            ?? data_get($uploadResp, 'data.file')
            ?? '';

        return is_string($value) ? $value : '';
    }

    private function mapApiWaTemplateForSelect(array $row): array
    {
        $raw = is_array($row['raw'] ?? null) ? $row['raw'] : [];
        $body = trim((string) ($row['body'] ?? ''));
        $lines = preg_split('/\r\n|\r|\n/', $body) ?: [];
        $headline = trim((string) ($lines[0] ?? 'Template pesan WA Business'));
        $message = trim(implode("\n\n", array_slice($lines, 1))) ?: $body;
        $buttons = data_get($raw, 'buttons')
            ?? data_get($raw, 'buttonList')
            ?? data_get($raw, 'button_list')
            ?? data_get($raw, 'components.buttons')
            ?? [];
        if (! is_array($buttons)) {
            $buttons = [];
        }

        $headerPathFile = (string) (data_get($raw, 'headerPathFile') ?? data_get($raw, 'header_path_file') ?? '');
        $assetUrl = $headerPathFile !== '' ? $headerPathFile : asset('assets/logo.png');
        $category = strtolower((string) ($row['category'] ?? ''));
        $isCarousel = str_contains($category, 'carousel');

        return [
            'id' => (string) ($row['gw_template_id'] ?? ''),
            'local_id' => '',
            'gw_template_id' => (string) ($row['gw_template_id'] ?? ''),
            'name' => (string) ($row['name'] ?? '-'),
            'account_name' => (string) (data_get($raw, 'verifiedName') ?? data_get($raw, 'displayName') ?? 'Telkomsel Promo & Rewards'),
            'template_kind' => (string) ($row['category'] ?? '-'),
            'channel_kind' => $isCarousel ? 'Carousel' : 'Basic',
            'bubble_headline' => $headline,
            'bubble_message' => $message,
            'asset_url' => $assetUrl,
            'cards' => $isCarousel
                ? [
                    ['title' => 'Card 1', 'caption' => 'Slide utama campaign WA Business'],
                    ['title' => 'Card 2', 'caption' => 'Slide lanjutan untuk menampilkan informasi tambahan'],
                ]
                : [],
            'price_note' => 'Template yang dipilih menggunakan Display Name Default, sehingga akan dikenakan biaya sebesar Rp 1.100 per pesan.',
            'cta' => trim((string) data_get($buttons, '0.label')) ?: 'Coba Sekarang',
            'preview_name' => (string) (data_get($raw, 'verifiedName') ?? data_get($raw, 'displayName') ?? 'Name'),
        ];
    }

    private function apiTemplateRowForShow(array $row): object
    {
        $raw = is_array($row['raw'] ?? null) ? $row['raw'] : [];
        $buttons = data_get($raw, 'buttons')
            ?? data_get($raw, 'buttonList')
            ?? data_get($raw, 'button_list')
            ?? data_get($raw, 'components.buttons')
            ?? [];
        if (! is_array($buttons)) {
            $buttons = [];
        }

        return (object) [
            'id' => null,
            'gw_template_id' => (string) ($row['gw_template_id'] ?? ''),
            'name' => (string) ($row['name'] ?? '-'),
            'template_type' => 'simple_message',
            'category' => (string) ($row['category'] ?? 'Single Banner'),
            'language' => (string) ($row['language'] ?? 'INDONESIA'),
            'header_type' => (string) (data_get($raw, 'headerType') ?? data_get($raw, 'header_type') ?? 'TEXT'),
            'asset_path' => null,
            'body' => (string) (data_get($raw, 'bodyMessage') ?? data_get($raw, 'body_message') ?? data_get($raw, 'body') ?? data_get($raw, 'message') ?? data_get($raw, 'templateBody') ?? data_get($raw, 'template_body') ?? ($row['body'] ?? '')),
            'footer' => (string) (data_get($raw, 'footer') ?? ''),
            'buttons' => $buttons,
            'status' => (string) ($row['status'] ?? '-'),
            'gw_payload' => [
                'verifiedName' => data_get($raw, 'verifiedName') ?? data_get($raw, 'displayName') ?? '',
                'displayName' => data_get($raw, 'displayName') ?? data_get($raw, 'verifiedName') ?? '',
                'headerLabel' => data_get($raw, 'headerLabel') ?? data_get($raw, 'header_label') ?? '',
                'headerPathFile' => data_get($raw, 'headerPathFile') ?? data_get($raw, 'header_path_file') ?? '',
                'buttons' => $buttons,
            ],
            'created_at' => $row['created_at'] ?? now(),
        ];
    }

    private function fallbackWaTemplates(): array
    {
        return [
            [
                'id' => 'fallback-1',
                'name' => 'sehatdanbugarmyads',
                'account_name' => 'Telkomsel Promo & Rewards',
                'template_kind' => 'Basic',
                'channel_kind' => 'Carousel',
                'bubble_headline' => 'Semangat pagi, sobat sehat! 👋 ☀️',
                'bubble_message' => 'Di tengah ramainya suasana CFD, jangan lewatkan kesempatan untuk menjangkau pelanggan yang tepat untuk bisnis Anda.',
                'asset_url' => asset('assets/logo.png'),
                'cards' => [
                    ['title' => 'Card 1', 'caption' => 'Slide utama campaign WA Business'],
                    ['title' => 'Card 2', 'caption' => 'Slide lanjutan untuk menampilkan informasi tambahan'],
                ],
                'price_note' => 'Template yang dipilih menggunakan Display Name Default, sehingga akan dikenakan biaya sebesar Rp 1.100 per pesan.',
                'cta' => 'Coba Sekarang',
                'preview_name' => 'Name',
            ],
            [
                'id' => 'fallback-2',
                'name' => 'promolokalmingguan',
                'account_name' => 'Telkomsel Promo & Rewards',
                'template_kind' => 'Simple message',
                'channel_kind' => 'Basic',
                'bubble_headline' => 'Promo spesial akhir pekan untuk pelanggan sekitar toko!',
                'bubble_message' => 'Jangkau pelanggan di sekitar area bisnis Anda dengan penawaran yang relevan dan langsung mengundang aksi.',
                'asset_url' => asset('assets/logo.png'),
                'cards' => [],
                'price_note' => 'Template yang dipilih menggunakan Display Name Default, sehingga akan dikenakan biaya sebesar Rp 1.100 per pesan.',
                'cta' => 'Lihat Promo',
                'preview_name' => 'Name',
            ],
        ];
    }

    private function wabaTemplateRowsFromApi(Request $request): array
    {
        try {
            /** @var MyadsApiClient $client */
            $client = app(MyadsApiClient::class);
            $resp = $client->wabaTemplateList($this->requireAccessToken($request), [], $this->requireGatewayToken($request), $this->gatewayRefreshCallback($request, $client));

            $items = data_get($resp, 'data.data.list')
                ?? data_get($resp, 'data.data.templates')
                ?? data_get($resp, 'data.data.items')
                ?? data_get($resp, 'data.data')
                ?? data_get($resp, 'data')
                ?? [];

            if (! is_array($items)) {
                return [];
            }

            return collect($items)
                ->filter(fn ($row): bool => is_array($row))
                ->map(function (array $row): array {
                    $templateId = (string) (data_get($row, 'templateId')
                        ?? data_get($row, 'id')
                        ?? data_get($row, 'template_id')
                        ?? '');
                    $name = (string) (data_get($row, 'templateName')
                        ?? data_get($row, 'name')
                        ?? data_get($row, 'template_name')
                        ?? '');
                    $createdAt = data_get($row, 'createdAt')
                        ?? data_get($row, 'created_at')
                        ?? data_get($row, 'timeAdded')
                        ?? null;

                    try {
                        $createdAt = $createdAt ? \Carbon\Carbon::parse($createdAt) : now();
                    } catch (Throwable) {
                        $createdAt = now();
                    }

                    return [
                        'id' => null,
                        'gw_template_id' => $templateId,
                        'name' => $name !== '' ? $name : '-',
                        'category' => (string) (data_get($row, 'category') ?? '-'),
                        'language' => (string) (data_get($row, 'language') ?? '-'),
                        'body' => (string) (data_get($row, 'bodyMessage') ?? data_get($row, 'body_message') ?? data_get($row, 'body') ?? data_get($row, 'message') ?? data_get($row, 'templateBody') ?? data_get($row, 'template_body') ?? ''),
                        'status' => (string) (data_get($row, 'activationStatus') ?? data_get($row, 'activation_status') ?? data_get($row, 'status') ?? '-'),
                        'created_at' => $createdAt,
                        'raw' => $row,
                    ];
                })
                ->values()
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    private function campaignRowsFromApi(Request $request, string $channel, string $menu): array
    {
        try {
            /** @var MyadsApiClient $client */
            $client = app(MyadsApiClient::class);
            $gatewayToken = $this->requireGatewayToken($request);
            $listResp = $client->campaignList($gatewayToken, ['campaignId' => null], $this->gatewayRefreshCallback($request, $client));

            $items = $this->extractCampaignListItems($listResp);

            $wantedChannel = $channel === 'sms' ? 'SMS' : null;
            if ($channel === 'wa-business') {
                // Common values seen in APIs: WABA, WA, WHATSAPP
                $wantedChannel = 'WA';
            }

            $wantedType = match ($menu) {
                'location-based-area' => 'LBA',
                'targeted' => 'TARGETED',
                default => null,
            };

            $filtered = array_values(array_filter($items, function ($row) use ($wantedChannel, $wantedType) {
                if (! is_array($row)) return false;

                // Remove BROADCAST completely (product not used)
                $typeValue = strtoupper((string) (data_get($row, 'campaignType') ?? data_get($row, 'campaign_type') ?? data_get($row, 'type') ?? ''));
                if ($typeValue === 'BROADCAST' || str_contains($typeValue, 'BROADCAST')) {
                    return false;
                }

                if ($wantedChannel) {
                    $channelValue = strtoupper((string) (data_get($row, 'inventoryType') ?? data_get($row, 'inventory_type') ?? data_get($row, 'channel') ?? data_get($row, 'channel_type') ?? ''));

                    // For SMS dashboard, inventoryType can be SMS or MMS.
                    if ($wantedChannel === 'SMS' && ! in_array($channelValue, ['SMS', 'MMS'], true)) {
                        return false;
                    }

                    if ($wantedChannel === 'WA') {
                        $isWa = in_array($channelValue, ['WABA', 'WA', 'WA-BUSINESS', 'WHATSAPP'], true);
                        if (! $isWa) return false;
                    }
                }

                if ($wantedType) {
                    if ($typeValue !== '' && ! str_contains($typeValue, $wantedType)) {
                        return false;
                    }
                }

                return true;
            }));

            return array_map(fn ($row): array => $this->mapCampaignListRow($row), $filtered);
        } catch (Throwable) {
            return [];
        }
    }

    private function extractCampaignListItems(array $response): array
    {
        $items = data_get($response, 'data.data.list')
            ?? data_get($response, 'data.data.campaigns')
            ?? data_get($response, 'data.data.items')
            ?? data_get($response, 'data.data.campaign')
            ?? data_get($response, 'data.data.detail')
            ?? data_get($response, 'data.data.data')
            ?? data_get($response, 'data.data')
            ?? data_get($response, 'data.list')
            ?? data_get($response, 'data.campaigns')
            ?? data_get($response, 'data.items')
            ?? data_get($response, 'data.campaign')
            ?? data_get($response, 'data.detail')
            ?? [];

        if (! is_array($items)) {
            return [];
        }

        if (! array_is_list($items)) {
            return [$items];
        }

        return array_values(array_filter($items, fn ($row): bool => is_array($row)));
    }

    private function mapCampaignListRow(array $row): array
    {
        $id = (string) (data_get($row, 'id') ?? data_get($row, 'campaignId') ?? '');
        $title = (string) (data_get($row, 'campaignName') ?? data_get($row, 'title') ?? data_get($row, 'name') ?? '');

        $startDate = (string) (data_get($row, 'startDate') ?? data_get($row, 'deliveryStartDate') ?? data_get($row, 'deliveryDate') ?? '');
        $endDate = (string) (data_get($row, 'endDate') ?? data_get($row, 'deliveryEndDate') ?? '');
        $dateLabel = '';
        if ($startDate !== '') {
            try {
                $startLabel = \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y');
                $endLabel = $endDate !== ''
                    ? \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y')
                    : '';
                $dateLabel = ($endLabel !== '' && $endLabel !== $startLabel)
                    ? $startLabel . ' - ' . $endLabel
                    : $startLabel;
            } catch (Throwable) {
                $dateLabel = $startDate . ($endDate !== '' ? (' - ' . $endDate) : '');
            }
        }

        $channelType = (string) (data_get($row, 'inventoryType') ?? data_get($row, 'channel') ?? data_get($row, 'channel_type') ?? '');
        $category = (string) (data_get($row, 'campaignType') ?? data_get($row, 'category') ?? '');
        $operator = (string) (data_get($row, 'operator') ?? '-');

        $status = (string) (data_get($row, 'status') ?? '');
        $reason = (string) (data_get($row, 'reason') ?? '');
        $statusDetail = trim($status . ($reason !== '' ? (': ' . $reason) : ''));

        $bucketMain = data_get($row, 'bucketMain') ?? data_get($row, 'totalPrice') ?? data_get($row, 'totalCost');
        $totalPriceLabel = is_numeric($bucketMain)
            ? 'Rp ' . number_format((float) $bucketMain, 0, ',', '.')
            : '-';

        return [
            'id' => $id,
            'date' => $dateLabel,
            'title' => $title,
            'operator' => $operator,
            'category' => $category,
            'channel_type' => $channelType,
            'status_detail' => $statusDetail,
            'total_price' => $totalPriceLabel,
            'report_detail' => $this->campaignReportDetailFromListRow($row),
            'raw' => $row,
        ];
    }

    private function campaignReportDetailFromListRow(array $row): array
    {
        $row = $this->decodeNestedJsonValues($row);

        $deliveries = $this->findNestedValue($row, ['deliveries', 'delivery', 'schedules', 'schedule']);
        if (is_array($deliveries) && ! array_is_list($deliveries)) {
            $deliveries = [$deliveries];
        }

        $controlNumbers = $this->findNestedValue($row, ['controlNumbers', 'controlNumber', 'testNumbers', 'testNumber']);
        if (is_string($controlNumbers)) {
            $controlNumbers = array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $controlNumbers))));
        } elseif (! is_array($controlNumbers)) {
            $controlNumbers = [];
        }

        $location = $this->findNestedValue($row, ['locations', 'location']);
        if (is_array($location) && array_is_list($location)) {
            $location = is_array($location[0] ?? null) ? $location[0] : [];
        }
        if (! is_array($location)) {
            $location = [];
        }

        return [
            'sender_name' => $this->findNestedValue($row, ['senderName', 'sender_name', 'sender', 'from']),
            'message' => $this->findNestedValue($row, ['message', 'messageText', 'smsMessage', 'bodyMessage', 'description']),
            'recipient_total' => $this->findNestedValue($row, ['recepientTotal', 'recipientTotal', 'totalRecipient', 'totalRecepient', 'totalMessage']),
            'deliveries' => is_array($deliveries) ? $deliveries : [],
            'control_numbers' => $controlNumbers,
            'location' => $location,
            'profile_gender' => $this->findNestedValue($row, ['profileGender', 'gender']),
            'profile_age' => $this->findNestedValue($row, ['profileAge', 'age']),
            'profile_religion' => $this->findNestedValue($row, ['profileReligion', 'religion']),
            'profile_os' => $this->findNestedValue($row, ['profileOs', 'os', 'device']),
            'unit_price' => $this->findNestedValue($row, ['unitPrice', 'pricePerMessage', 'price']),
            'grand_total' => $this->findNestedValue($row, ['grandTotal', 'totalPrice', 'totalCost', 'bucketMain']),
            'short_url' => $this->findNestedValue($row, ['shortUrl', 'short_url']),
        ];
    }

    private function decodeNestedJsonValues(mixed $value): mixed
    {
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed !== '' && in_array($trimmed[0], ['{', '['], true)) {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $this->decodeNestedJsonValues($decoded);
                }
            }

            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->decodeNestedJsonValues($item);
            }
        }

        return $value;
    }

    private function findNestedValue(array $source, array $keys): mixed
    {
        $targets = array_map(fn (string $key): string => $this->normalizePayloadKey($key), $keys);

        foreach ($source as $key => $value) {
            if (in_array($this->normalizePayloadKey((string) $key), $targets, true) && $value !== null && $value !== '') {
                return $value;
            }

            if (is_array($value)) {
                $nested = $this->findNestedValue($value, $keys);
                if ($nested !== null && $nested !== '') {
                    return $nested;
                }
            }
        }

        return null;
    }

    private function normalizePayloadKey(string $key): string
    {
        if (str_contains($key, '.')) {
            $key = substr(strrchr($key, '.'), 1);
        }

        return strtolower((string) preg_replace('/[^a-z0-9]/i', '', $key));
    }

    private function campaignReportFromApi(Request $request, string $campaignId): array
    {
        $campaignId = trim($campaignId);

        if ($campaignId === '' || ! ctype_digit($campaignId)) {
            return [];
        }

        try {
            /** @var MyadsApiClient $client */
            $client = app(MyadsApiClient::class);
            $gatewayToken = $this->requireGatewayToken($request);

            return $client->campaignReport($gatewayToken, [
                'campaignId' => (int) $campaignId,
            ], $this->gatewayRefreshCallback($request, $client));
        } catch (Throwable) {
            return [];
        }
    }

    private function fallbackCampaignRow(Request $request, string $channel, string $menu, array $page): array
    {
        $campaignId = trim((string) $request->query('id', ''));
        $isTargeted = $menu === 'targeted';

        return [
            'id' => $campaignId !== '' ? $campaignId : '-',
            'date' => '-',
            'title' => (string) ($page['title'] ?? ($isTargeted ? 'SMS Targeted' : 'SMS Location Based Area')),
            'operator' => '-',
            'category' => $isTargeted ? 'TARGETED' : 'LBA',
            'channel_type' => $channel === 'sms' ? 'SMS' : strtoupper($channel),
            'status_detail' => '-',
            'total_price' => '-',
            'raw' => [],
        ];
    }

    private function resolveUnitPrice(Request $request, string $channel, string $menu): int
    {
        // Only SMS create flows need this for now.
        if ($channel !== 'sms') {
            return 0;
        }

        $priceKey = match ($menu) {
            'location-based-area' => 'LBA_SMS',
            'targeted' => 'TARGETED_SMS',
            default => null,
        };

        if ($priceKey === null) {
            return 0;
        }

        /** @var MyadsApiClient $client */
        $client = app(MyadsApiClient::class);
        $gatewayToken = $this->requireGatewayToken($request);
        $resp = $client->campaignPriceView($gatewayToken, ['campaignType' => 'ALL'], $this->gatewayRefreshCallback($request, $client));

        $value = data_get($resp, 'data.data.' . $priceKey);
        if (is_numeric($value)) {
            return (int) $value;
        }

        return 0;
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

    private function pages(): array
    {
        $lbaShots = [
            'image15.png' => 'Pilih kategori iklan untuk masuk ke Location Based Advertising.',
            'image16.png' => 'Form pembuatan iklan LBA dan pengisian detail awal campaign.',
            'image17.png' => 'Pencarian lokasi pada peta untuk menentukan area target.',
            'image18.png' => 'Atur radius dan tipe lokasi untuk menghitung penerima potensial.',
            'image19.png' => 'Review titik lokasi dan simpan pengaturan area kampanye.',
            'image20.png' => 'Ringkasan pengaturan lokasi sebelum melanjutkan proses.',
            'image21.png' => 'Susun konten pesan dan informasi pengirim untuk iklan berbasis lokasi.',
            'image22.png' => 'Lihat preview pesan dan detail segmentasi penerima.',
            'image23.png' => 'Review biaya dan detail campaign sebelum pembayaran.',
            'image24.png' => 'Halaman menunggu persetujuan setelah campaign LBA dikirim.',
        ];

        $broadcastShots = [
            'image25.png' => 'Mulai dari form pembuatan iklan broadcast SMS.',
            'image26.png' => 'Atur konten pesan, pengirim, dan profil penerima.',
            'image27.png' => 'Tentukan jadwal pengiriman dan konfigurasi tambahan.',
            'image28.png' => 'Preview isi pesan broadcast yang akan dikirim.',
            'image29.png' => 'Review keseluruhan campaign broadcast sebelum proses bayar.',
            'image30.png' => 'Rincian biaya campaign untuk kanal broadcast.',
            'image31.png' => 'Status menunggu persetujuan setelah iklan broadcast dikirim.',
        ];

        $targetedShots = [
            'image32.png' => 'Buka flow targeted SMS dan beri judul campaign.',
            'image33.png' => 'Lengkapi konten pesan iklan targeted.',
            'image34.png' => 'Pilih kategori audience dan segmentasi penerima.',
            'image35.png' => 'Atur filter profil untuk audience targeted.',
            'image36.png' => 'Tentukan waktu pengiriman untuk audience pilihan.',
            'image37.png' => 'Preview pesan serta ringkasan target audience.',
            'image38.png' => 'Review biaya dan detail pembayaran targeted SMS.',
            'image39.png' => 'Halaman approval setelah campaign targeted diajukan.',
        ];

        return [
            'sms' => [
                'location-based-area' => [
                    'title' => 'SMS Location Based Area',
                    'headline' => 'Mulai campaign SMS Location Based Area dari judul iklan',
                    'description' => 'Flow ini dimulai dari modal judul iklan, lalu dilanjutkan ke tahap buat konten iklan dengan preview pesan dan stepper campaign.',
                    'badge' => 'SMS LBA',
                    'steps' => [
                        'Buat Konten Iklan',
                        'Atur Pengiriman',
                        'Review & Pembayaran',
                        'Menunggu Persetujuan',
                    ],
                ],
                'targeted' => [
                    'title' => 'SMS Targeted',
                    'headline' => 'Flow campaign targeted SMS berbasis audience',
                    'description' => 'Halaman ini memetakan step pada deck targeted SMS: judul iklan, isi pesan, filter audience, jadwal kirim, review biaya, dan approval.',
                    'badge' => 'SMS Targeted',
                    'steps' => [
                        'Buat Konten Iklan',
                        'Atur Profil Penerima',
                        'Atur Pengiriman',
                        'Review & Pembayaran',
                    ],
                    'screenshots' => $targetedShots,
                ],
            ],
            'wa-business' => [
                'location-based-area' => [
                    'title' => 'WA Business Location Based Area',
                    'headline' => 'Flow WA Business LBA dimulai dari pemilihan template pesan',
                    'description' => 'Tahap awal WA Business LBA dimulai dari memilih template pesan, lalu dilanjutkan ke target penerima, pengiriman, dan review pembayaran.',
                    'badge' => 'WA LBA',
                    'steps' => [
                        'Pilih Template Pesan',
                        'Atur Target Penerima',
                        'Atur Pengiriman',
                        'Review & Pembayaran',
                    ],
                ],
                'broadcast' => [
                    'title' => 'WA Business Broadcast',
                    'headline' => 'Template flow broadcast WA Business',
                    'description' => 'Halaman ini memakai struktur visual broadcast dari deck agar setiap menu sudah terpasang rapi dengan template yang sama.',
                    'badge' => 'WA Broadcast',
                    'steps' => [
                        'Buat konten broadcast',
                        'Atur pengiriman',
                        'Preview pesan',
                        'Review pembayaran',
                        'Menunggu persetujuan',
                    ],
                    'screenshots' => $broadcastShots,
                ],
                'targeted' => [
                    'title' => 'WA Business Targeted',
                    'headline' => 'Template flow targeted WA Business',
                    'description' => 'Tampilan mengikuti alur targeted dari deck sebagai baseline, lalu dipasang ke menu WA Business agar pengalaman antar menu tetap konsisten.',
                    'badge' => 'WA Targeted',
                    'steps' => [
                        'Buat Konten Iklan',
                        'Atur Profil Penerima',
                        'Atur Pengiriman',
                        'Review & Pembayaran',
                    ],
                    'screenshots' => $targetedShots,
                ],
                'campaign-template' => [
                    'title' => 'WA Business Campaign Template',
                    'headline' => 'Placeholder campaign template untuk WA Business',
                    'description' => 'Menu ini disiapkan sebagai tempat template campaign WA Business. Untuk saat ini halamannya masih placeholder dan siap diisi pada tahap berikutnya.',
                    'badge' => 'WA Template',
                    'steps' => [
                        'Pilih template',
                        'Atur isi campaign',
                        'Review template',
                    ],
                ],
            ],
        ];
    }
}
