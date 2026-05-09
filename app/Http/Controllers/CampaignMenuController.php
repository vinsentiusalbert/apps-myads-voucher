<?php

namespace App\Http\Controllers;

use App\Models\CampaignTemplate;
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
                $campaignRow = $rows->firstWhere('id', (string) $request->query('id')) ?? $rows->first();
                abort_unless(is_array($campaignRow), 404);

                return view('campaign-lba.show', [
                    'channel' => $channel,
                    'menu' => $menu,
                    'page' => $page,
                    'campaignRow' => $campaignRow,
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
                $campaignRow = $rows->firstWhere('id', (string) $request->query('id')) ?? $rows->first();
                abort_unless(is_array($campaignRow), 404);

                return view('campaign-targeted.show', [
                    'channel' => $channel,
                    'menu' => $menu,
                    'page' => $page,
                    'campaignRow' => $campaignRow,
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
        $templates = CampaignTemplate::query()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = $request->string('q')->toString();

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), function ($query) use ($request): void {
                $query->where('category', $request->string('category')->toString());
            })
            ->when($request->filled('start_date'), function ($query) use ($request): void {
                $query->whereDate('created_at', '>=', $request->date('start_date'));
            })
            ->when($request->filled('end_date'), function ($query) use ($request): void {
                $query->whereDate('created_at', '<=', $request->date('end_date'));
            })
            ->latest()
            ->get();

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
            'templateCount' => CampaignTemplate::count(),
            'approvedTemplateCount' => CampaignTemplate::where('status', 'APPROVED')->count(),
        ]);
    }

    public function storeCampaignTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:campaign_templates,name'],
            'template_type' => ['required', 'string', 'max:50'],
            'category' => ['required', 'string', 'max:100'],
            'language' => ['required', 'string', 'max:100'],
            'header_type' => ['required', 'string', 'max:50'],
            'asset' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,mp4', 'max:10240'],
            'body' => ['required', 'string', 'max:1024'],
            'footer' => ['nullable', 'string', 'max:60'],
        ]);

        $assetPath = null;
        if ($request->hasFile('asset')) {
            $assetPath = $request->file('asset')->store('campaign-templates', 'public');
        }

        CampaignTemplate::create([
            'name' => $validated['name'],
            'template_type' => $validated['template_type'],
            'category' => $validated['category'],
            'language' => $validated['language'],
            'header_type' => $validated['header_type'],
            'asset_path' => $assetPath,
            'body' => $validated['body'],
            'footer' => $validated['footer'] ?? null,
            'buttons' => [],
            'status' => 'PENDING',
        ]);

        return redirect()
            ->route('campaign-template.index')
            ->with('status', 'Template campaign berhasil dibuat.');
    }

    public function showCampaignTemplate(CampaignTemplate $template): View
    {
        return view('campaign-wa-template.show', [
            'channel' => 'wa-business',
            'menu' => 'campaign-template',
            'page' => $this->pages()['wa-business']['campaign-template'],
            'templateRow' => $template,
        ]);
    }

    private function resolveWaTemplates()
    {
        try {
            return CampaignTemplate::query()
                ->latest()
                ->get()
                ->map(function (CampaignTemplate $template): array {
                    return $this->mapWaTemplate($template);
                })
                ->values();
        } catch (Throwable) {
            return collect();
        }
    }

    private function mapWaTemplate(CampaignTemplate $template): array
    {
        $body = trim((string) $template->body);
        $lines = preg_split('/\r\n|\r|\n/', $body) ?: [];
        $headline = trim((string) ($lines[0] ?? 'Template pesan WA Business'));
        $message = trim(implode("\n\n", array_slice($lines, 1))) ?: $body;
        $assetUrl = $template->asset_path ? asset('storage/' . $template->asset_path) : asset('assets/logo.png');
        $isCarousel = $template->template_type === 'carousel_cards' || str_contains(strtolower((string) $template->category), 'carousel');

        return [
            'id' => (string) $template->id,
            'name' => $template->name,
            'account_name' => 'Telkomsel Promo & Rewards',
            'template_kind' => ucfirst(str_replace('_', ' ', (string) $template->template_type)),
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
            'cta' => data_get($template->buttons, '0.label', 'Coba Sekarang'),
            'preview_name' => 'Name',
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

    private function campaignRowsFromApi(Request $request, string $channel, string $menu): array
    {
        $gatewayToken = (string) $request->session()->get('myads.gw_token', '');
        if ($gatewayToken === '') {
            return [];
        }

        try {
            /** @var MyadsApiClient $client */
            $client = app(MyadsApiClient::class);
            $listResp = $client->campaignList($gatewayToken, ['campaignId' => null]);

            $items = data_get($listResp, 'data.data.list')
                ?? data_get($listResp, 'data.data.campaigns')
                ?? data_get($listResp, 'data.data.items')
                ?? data_get($listResp, 'data.data')
                ?? [];

            if (! is_array($items)) {
                return [];
            }

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

            return array_map(function ($row) {
                $id = (string) (data_get($row, 'id') ?? data_get($row, 'campaignId') ?? '');
                $title = (string) (data_get($row, 'campaignName') ?? data_get($row, 'title') ?? data_get($row, 'name') ?? '');

                $startDate = (string) (data_get($row, 'startDate') ?? '');
                $endDate = (string) (data_get($row, 'endDate') ?? '');
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

                $bucketMain = data_get($row, 'bucketMain');
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
                ];
            }, $filtered);
        } catch (Throwable) {
            return [];
        }
    }

    private function resolveUnitPrice(Request $request, string $channel, string $menu): int
    {
        // Only SMS create flows need this for now.
        if ($channel !== 'sms') {
            return 0;
        }

        $gatewayToken = (string) $request->session()->get('myads.gw_token', '');
        if ($gatewayToken === '') {
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
        $resp = $client->campaignPriceView($gatewayToken, ['campaignType' => 'ALL']);

        $value = data_get($resp, 'data.data.' . $priceKey);
        if (is_numeric($value)) {
            return (int) $value;
        }

        return 0;
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
                        'Menunggu Persetujuan',
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
                        'Menunggu Persetujuan',
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
