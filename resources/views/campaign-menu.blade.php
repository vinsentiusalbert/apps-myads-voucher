<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $page['title'] }} | {{ config('app.name', 'MyAds') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/logo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,700|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/auth-portal.css') }}">
    </head>
    <body>
        @php
            $isWaTemplate = $channel === 'wa-business' && $menu === 'campaign-template';
            $isWaTemplateCreate = $isWaTemplate && request()->query('view') === 'create';
            $isWaTemplateRead = $isWaTemplate && isset($templateRow);
            $isWaTemplateForm = $isWaTemplateCreate || $isWaTemplateRead;
            $templateRows = $templateRows ?? collect();
            $templateCount = $templateCount ?? $templateRows->count();
            $approvedTemplateCount = $approvedTemplateCount ?? $templateRows->where('status', 'APPROVED')->count();
            $templateValue = fn (string $field, mixed $default = null) => old($field, $isWaTemplateRead ? $templateRow->{$field} : $default);
        @endphp
        <main class="portal-shell">
            <button type="button" class="portal-overlay" data-menu-close aria-label="Tutup menu"></button>

            <aside class="portal-sidebar" id="mobile-menu">
                <div class="portal-sidebar__bg"></div>

                <div class="portal-sidebar__inner">
                    <div class="portal-sidebar__mobile-head">
                        <p class="portal-sidebar__mobile-title">Menu</p>
                        <button type="button" class="portal-sidebar__close" data-menu-close aria-label="Tutup menu">
                            <span></span>
                            <span></span>
                        </button>
                    </div>

                    <div class="portal-brand">
                        <img src="{{ asset('assets/logo.png') }}" alt="MyAds Logo" class="portal-brand__logo">
                        <p class="portal-brand__text">MyAds</p>
                    </div>

                    <a href="{{ route('dashboard') }}" class="portal-create-btn portal-create-btn--link">Dashboard Utama</a>

                    <nav class="portal-nav" aria-label="Main navigation">
                        <div class="portal-nav__item">
                            <a href="{{ route('dashboard') }}" class="portal-nav__head">
                                <span class="portal-nav__icon-wrap">
                                    <svg class="portal-nav__svg" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4 4h7v7H4zM13 4h7v4h-7zM13 10h7v10h-7zM4 13h7v7H4z" fill="currentColor"/>
                                    </svg>
                                </span>
                                <span>Dashboard</span>
                            </a>
                        </div>

                        @php
                            $menus = [
                                'sms' => ['label' => 'SMS', 'items' => ['location-based-area' => 'Location Based Area', 'targeted' => 'Targeted']],
                                'wa-business' => ['label' => 'WA Business', 'items' => ['location-based-area' => 'Location Based Area', 'campaign-template' => 'Campaign Template', 'targeted' => 'Targeted']],
                            ];
                        @endphp

                        @foreach ($menus as $navChannel => $navData)
                            <div class="portal-nav__item portal-nav__item--dropdown {{ $channel === $navChannel ? 'portal-nav__item--active portal-nav__item--open' : '' }}" data-nav-group>
                                <button type="button" class="portal-nav__head portal-nav__toggle" data-nav-toggle aria-expanded="{{ $channel === $navChannel ? 'true' : 'false' }}">
                                    <span class="portal-nav__icon-wrap">
                                        @if ($navChannel === 'sms')
                                            <svg class="portal-nav__svg" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v7A2.5 2.5 0 0 1 17.5 16h-7.2L6 19.5V16.3A2.5 2.5 0 0 1 4 13.8z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                                <path d="M7.5 8.5h9M7.5 11.5h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                        @else
                                            <svg class="portal-nav__svg" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 4a8 8 0 0 0-6.93 12l-.87 4 4.1-.8A8 8 0 1 0 12 4Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                                <path d="M9 9.7c.2-.5.4-.5.7-.5h.6c.2 0 .4 0 .6.5.2.6.8 2 .8 2.2s.1.3 0 .5c-.1.2-.2.3-.4.5l-.3.3c-.1.1-.3.3-.1.6.2.3.8 1.3 2 2 .8.5 1.4.7 1.7.8.3.1.5.1.7-.1l.9-1.1c.2-.2.4-.2.7-.1l1.8.8c.3.1.5.2.5.4 0 .2-.1 1-.6 1.4-.5.4-1.2.5-1.5.5-.4 0-1-.1-1.8-.5a10 10 0 0 1-3-2 10.6 10.6 0 0 1-2.1-2.8c-.4-.8-.6-1.5-.6-2 0-.5.2-1 .5-1.3.3-.3.8-.6 1-.6Z" fill="currentColor"/>
                                            </svg>
                                        @endif
                                    </span>
                                    <span>{{ $navData['label'] }}</span>
                                    <span class="portal-nav__caret" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M7 10l5 5 5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </button>
                                <div class="portal-subnav">
                                    @foreach ($navData['items'] as $navMenu => $navLabel)
                                        <a href="{{ $navChannel === 'wa-business' && $navMenu === 'campaign-template' ? route('campaign-template.index') : route('campaign.menu', ['channel' => $navChannel, 'menu' => $navMenu]) }}" class="portal-subnav__item {{ $channel === $navChannel && $menu === $navMenu ? 'portal-subnav__item--active' : '' }}">
                                            <span class="portal-subnav__icon">◎</span>
                                            <span>{{ $navLabel }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>

                    <div class="portal-mobile-actions">
                        <form method="POST" action="{{ route('logout') }}" class="portal-mobile-actions__form">
                            @csrf
                            <button type="submit" class="portal-logout portal-logout--menu">
                                <span class="portal-logout__icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M14 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M10 17l5-5-5-5M15 12H4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <section class="portal-main">
                <header class="portal-topbar">
                    <div class="portal-topbar__left">
                        <button type="button" class="portal-burger" data-menu-toggle aria-label="Buka menu" aria-controls="mobile-menu" aria-expanded="false">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                        <div class="portal-topbar__spacer"></div>
                    </div>
                    <div class="portal-topbar__actions">
                        <div class="portal-user">
                            <span class="portal-user__avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="portal-user__name">{{ auth()->user()->name }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="portal-logout">
                                <span class="portal-logout__icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M14 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M10 17l5-5-5-5M15 12H4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </header>

                <div class="portal-content {{ $isWaTemplate ? 'portal-content--template-library' : '' }}">
                    @if ($isWaTemplate)
                    @if ($isWaTemplateForm)
                    <section class="template-builder-hero">
                        <h1 class="template-builder-hero__title">{{ $isWaTemplateRead ? 'Read Campaign Template' : 'Create Campaign Template' }}</h1>
                    </section>

                    @if ($errors->any())
                        <section class="template-library-card">
                            <strong>Template belum bisa disimpan.</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    <form method="POST" action="{{ route('campaign-template.store') }}" enctype="multipart/form-data" class="template-builder-layout">
                        @csrf
                        <div class="template-builder-main">
                            <article class="template-builder-card">
                                <h2>Template Preview</h2>
                                <p>Choose the category that best describes your message template. Then, select the type of message that you want to send.</p>

                                <label class="template-builder-choice template-builder-choice--active">
                                    <input type="radio" name="template_type" value="simple_message" @checked($templateValue('template_type', 'simple_message') === 'simple_message') @disabled($isWaTemplateRead)>
                                    <span class="template-builder-choice__dot"></span>
                                    <span>
                                        <strong>Simple Message</strong>
                                        <small>Custom send promotions with only have 1 upload image Capability</small>
                                    </span>
                                </label>

                                <label class="template-builder-choice">
                                    <input type="radio" name="template_type" value="carousel_cards" @checked($templateValue('template_type') === 'carousel_cards') @disabled($isWaTemplateRead)>
                                    <span class="template-builder-choice__dot"></span>
                                    <span>
                                        <strong>Carousel Cards</strong>
                                        <small>Send promotion with custom multiple image using carousel component</small>
                                    </span>
                                </label>
                            </article>

                            <article class="template-builder-card">
                                <h2>Template name and language</h2>
                                <div class="template-builder-field">
                                    <label>Name your template</label>
                                    <input type="text" name="name" value="{{ $templateValue('name', 'campaign_whatsapp') }}" required @readonly($isWaTemplateRead)>
                                </div>

                                <div class="template-builder-field">
                                    <label>Category Template</label>
                                    <select name="category" required @disabled($isWaTemplateRead)>
                                        <option value="Single Banner" @selected($templateValue('category', 'Single Banner') === 'Single Banner')>Single Banner</option>
                                        <option value="Carousel" @selected($templateValue('category') === 'Carousel')>Carousel</option>
                                    </select>
                                </div>

                                <div class="template-builder-field">
                                    <label>Language</label>
                                    <select name="language" required @disabled($isWaTemplateRead)>
                                        <option value="Indonesia" @selected($templateValue('language', 'Indonesia') === 'Indonesia')>Indonesia</option>
                                        <option value="English" @selected($templateValue('language') === 'English')>English</option>
                                    </select>
                                </div>
                            </article>

                            <article class="template-builder-card">
                                <h2>Content</h2>
                                <p>Fill in the header, body and footer section of your template</p>

                                <div class="template-builder-field">
                                    <label>Header (optional)</label>
                                    <select name="header_type" @disabled($isWaTemplateRead)>
                                        <option value="none" @selected($templateValue('header_type', 'none') === 'none')>None</option>
                                        <option value="image" @selected($templateValue('header_type') === 'image')>Image</option>
                                        <option value="video" @selected($templateValue('header_type') === 'video')>Video</option>
                                        <option value="document" @selected($templateValue('header_type') === 'document')>Document</option>
                                    </select>
                                </div>

                                <div class="template-builder-field">
                                    <label>Asset</label>
                                    <p class="template-builder-field__hint">You can upload videos, images, PDFs, or locations.</p>
                                    <label class="template-builder-upload" for="templateAssetInput">
                                        <input type="file" id="templateAssetInput" name="asset" class="template-builder-upload__input" accept="image/*,application/pdf,video/mp4" @disabled($isWaTemplateRead)>
                                        <span class="template-builder-upload__icon">☁</span>
                                        <strong id="templateAssetLabel">{{ $isWaTemplateRead && $templateRow->asset_path ? basename($templateRow->asset_path) : 'Upload an asset' }}</strong>
                                        <small>(Ensure your asset meets the format and size requirement)</small>
                                        <span class="template-builder-upload__link">Learn more about supported format assets</span>
                                    </label>
                                </div>

                                <div class="template-builder-editor">
                                    <div class="template-builder-editor__toolbar">
                                        <select>
                                            <option>Paragraph</option>
                                        </select>
                                        <button type="button">B</button>
                                        <button type="button"><i>I</i></button>
                                        <button type="button"><u>U</u></button>
                                        <button type="button">S</button>
                                        <button type="button">•</button>
                                        <button type="button">1.</button>
                                    </div>
                                    <textarea id="templateBodyInput" name="body" maxlength="1024" placeholder="Write something awesome..." required @readonly($isWaTemplateRead)>{{ $templateValue('body') }}</textarea>
                                    <span class="template-builder-editor__count" id="templateBodyCount">0 / 1024</span>
                                </div>

                                <div class="template-builder-inline-actions">
                                    <button type="button" class="template-builder-ai">Creating body content with AI <span>Token quota: 0</span></button>
                                    <button type="button" class="template-builder-secondary">Add Body Variable</button>
                                </div>

                                <div class="template-builder-field">
                                    <label>Footer (optional)</label>
                                    <input type="text" name="footer" value="{{ $templateValue('footer') }}" maxlength="60" @readonly($isWaTemplateRead)>
                                </div>
                            </article>

                            <article class="template-builder-card">
                                <h2>Buttons</h2>
                                <button type="button" class="template-builder-add-button" id="templateAddButton" @disabled($isWaTemplateRead)>
                                    <span>+</span>
                                    <span>Add Button</span>
                                </button>
                                <p class="template-builder-button-warning" id="templateButtonWarning" hidden>Button hanya bisa 2.</p>

                                <div class="template-builder-button-list" id="templateButtonList"></div>

                                <div class="template-builder-tip">
                                    <strong>We recomend adding the marketing op-out button</strong>
                                    <p>Allow customers to request to opout all marketing messages. this can help reduce blocks from customers and increase your quality rating</p>
                                </div>

                                <div class="template-builder-inline-actions">
                                    <a href="{{ route('campaign-template.index') }}" class="template-builder-secondary">Cancel</a>
                                    @if (! $isWaTemplateRead)
                                    <button type="submit" class="template-builder-add-button">
                                        <span>Save Template</span>
                                    </button>
                                    @endif
                                </div>
                            </article>
                        </div>

                        <aside class="template-builder-preview">
                            <article class="template-builder-preview__card">
                                <h2>Template Preview</h2>
                                <div class="template-builder-phone">
                                    <div class="template-builder-phone__message">
                                        <img src="{{ $isWaTemplateRead && $templateRow->asset_path ? asset('storage/' . $templateRow->asset_path) : asset('assets/logo.png') }}" alt="Preview asset" class="template-builder-phone__image" id="templatePreviewImage">
                                        <div class="template-builder-phone__body" id="templatePreviewBody">
                                            Halo Pelanggan Setia ComboFit, kamu mendapatkan akses Exercise Plan GRATIS selama 30 hari 🔥

                                            ✅ Akses ke program latihan fisik oleh Coach profesional
                                            ✅ Panduan video latihan yang mudah diikuti
                                            ✅ Tips dan trik kebugaran

                                            Aktifkan akunmu di link ini
                                            https://bit.ly/Fita_Combofit

                                            #SehatMakinNikmat bersama Fita!
                                        </div>
                                        <div class="template-builder-phone__actions" id="templatePreviewActions">
                                            <div class="template-builder-phone__cta">Coba Sekarang</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="template-builder-preview__foot">
                                    <strong>This template is good for:</strong>
                                    <p>Welcome messages, promotions, offers, coupons, newsletters, announcements</p>
                                </div>
                            </article>
                        </aside>
                    </form>
                    @else
                    <section class="template-library-hero">
                        <div>
                            <h1 class="template-library-hero__title">Template Message</h1>
                        </div>
                        <a href="{{ route('campaign-template.index', ['view' => 'create']) }}" class="template-library-hero__cta">
                            <span>+</span>
                            <span>Create Template</span>
                        </a>
                    </section>

                    @if (session('status'))
                        <section class="template-library-card">
                            <strong>{{ session('status') }}</strong>
                        </section>
                    @endif

                    <section class="template-library-card">
                        <div class="template-library-tabs">
                            <button type="button" class="template-library-tab template-library-tab--active">
                                <span>All</span>
                                <strong>{{ $templateCount }}</strong>
                            </button>
                            <button type="button" class="template-library-tab">
                                <span>Approved</span>
                                <strong>{{ $approvedTemplateCount }}</strong>
                            </button>
                        </div>

                        <form method="GET" action="{{ route('campaign-template.index') }}" class="template-library-filters">
                            <label class="template-library-search">
                                <span class="template-library-search__icon">⌕</span>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search template message">
                            </label>

                            <label class="template-library-filter">
                                <span>Category Template</span>
                                <select name="category">
                                    <option value="">All Category</option>
                                    <option value="Single Banner" @selected(request('category') === 'Single Banner')>Single Banner</option>
                                    <option value="Carousel" @selected(request('category') === 'Carousel')>Carousel</option>
                                </select>
                            </label>

                            <label class="template-library-filter">
                                <span>Start date</span>
                                <input type="date" name="start_date" value="{{ request('start_date') }}">
                            </label>

                            <label class="template-library-filter">
                                <span>End date</span>
                                <input type="date" name="end_date" value="{{ request('end_date') }}">
                            </label>
                            <button type="submit" class="template-builder-secondary">Filter</button>
                        </form>

                        <div class="template-library-table-wrap">
                            <table class="template-library-table">
                                <thead>
                                    <tr>
                                        <th>Template Name</th>
                                        <th>Time Added</th>
                                        <th>Category</th>
                                        <th>Language</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($templateRows as $row)
                                        <tr>
                                            <td>
                                                <a href="{{ route('campaign-template.show', $row) }}">{{ $row->name }}</a>
                                            </td>
                                            <td>
                                                <strong>{{ $row->created_at->format('d M Y') }}</strong>
                                                <span>{{ $row->created_at->format('H:i') }} WIB</span>
                                            </td>
                                            <td>{{ $row->category }}</td>
                                            <td>
                                                <strong>{{ $row->language }}</strong>
                                                <span>{{ \Illuminate\Support\Str::limit($row->body ?? '-', 80) }}</span>
                                            </td>
                                            <td>
                                                <span class="template-library-status">{{ $row->status }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">Belum ada template campaign.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                    @endif
                    @else
                    <section class="flow-hero">
                        <div class="flow-hero__copy">
                            <p class="flow-badge">{{ $page['badge'] }}</p>
                            <h1 class="flow-title">{{ $page['title'] }}</h1>
                            <p class="flow-description">{{ $page['description'] }}</p>
                        </div>

                        <div class="flow-summary-card">
                            <p class="flow-summary-card__label">Sumber Referensi</p>
                            <p class="flow-summary-card__title">SMS.pptx</p>
                            <p class="flow-summary-card__copy">{{ $page['headline'] }}</p>
                        </div>
                    </section>

                    <section class="flow-card">
                        <div class="flow-card__header">
                            <h2>Urutan Flow</h2>
                            <a href="{{ route('dashboard') }}">Kembali ke dashboard</a>
                        </div>

                        <div class="flow-steps">
                            @foreach ($page['steps'] as $index => $step)
                                <div class="flow-step">
                                    <span class="flow-step__number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="flow-step__text">{{ $step }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="flow-section">
                        <h2 class="portal-section__title">Screen Mapping dari PPT</h2>
                        <div class="flow-gallery">
                            @foreach ($page['screenshots'] as $image => $caption)
                                <article class="flow-shot">
                                    <img src="{{ asset('assets/ppt/' . $image) }}" alt="{{ $caption }}" class="flow-shot__image">
                                    <div class="flow-shot__body">
                                        <p class="flow-shot__name">{{ basename($image, '.png') }}</p>
                                        <p class="flow-shot__caption">{{ $caption }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                    @endif
                </div>
            </section>
        </main>

        <script>
            (() => {
                const root = document.documentElement;
                const toggleButtons = document.querySelectorAll('[data-menu-toggle]');
                const closeButtons = document.querySelectorAll('[data-menu-close]');

                const setMenuState = (isOpen) => {
                    root.classList.toggle('portal-menu-open', isOpen);
                    toggleButtons.forEach((button) => {
                        button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    });
                };

                setMenuState(false);

                toggleButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const nextState = !root.classList.contains('portal-menu-open');
                        setMenuState(nextState);
                    });
                });

                closeButtons.forEach((button) => {
                    button.addEventListener('click', () => setMenuState(false));
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        setMenuState(false);
                    }
                });

                window.addEventListener('resize', () => {
                    if (window.innerWidth > 1080) {
                        setMenuState(false);
                    }
                });

                document.querySelectorAll('[data-nav-toggle]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const parent = button.closest('[data-nav-group]');
                        const isOpen = parent?.classList.toggle('portal-nav__item--open');
                        button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    });
                });

                const templateBodyInput = document.getElementById('templateBodyInput');
                const templateBodyCount = document.getElementById('templateBodyCount');
                const templatePreviewBody = document.getElementById('templatePreviewBody');
                const templateAssetInput = document.getElementById('templateAssetInput');
                const templatePreviewImage = document.getElementById('templatePreviewImage');
                const templateAssetLabel = document.getElementById('templateAssetLabel');
                const templateAddButton = document.getElementById('templateAddButton');
                const templateButtonList = document.getElementById('templateButtonList');
                const templatePreviewActions = document.getElementById('templatePreviewActions');
                const templateButtonWarning = document.getElementById('templateButtonWarning');
                const isTemplateRead = @json($isWaTemplateRead);
                let templateButtonIndex = 0;

                const syncTemplateBodyPreview = () => {
                    if (!templateBodyInput || !templatePreviewBody || !templateBodyCount) {
                        return;
                    }

                    const value = templateBodyInput.value.trim();
                    templatePreviewBody.textContent = value || `Halo Pelanggan Setia ComboFit, kamu mendapatkan akses Exercise Plan GRATIS selama 30 hari 🔥

✅ Akses ke program latihan fisik oleh Coach profesional
✅ Panduan video latihan yang mudah diikuti
✅ Tips dan trik kebugaran

Aktifkan akunmu di link ini
https://bit.ly/Fita_Combofit

#SehatMakinNikmat bersama Fita!`;
                    templateBodyCount.textContent = `${templateBodyInput.value.length} / 1024`;
                };

                const syncTemplateAssetPreview = () => {
                    if (!templateAssetInput || !templatePreviewImage || !templateAssetLabel) {
                        return;
                    }

                    const [file] = templateAssetInput.files || [];
                    if (!file) {
                        templatePreviewImage.src = "{{ asset('assets/logo.png') }}";
                        templateAssetLabel.textContent = 'Upload an asset';
                        return;
                    }

                    templateAssetLabel.textContent = file.name;
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        if (typeof event.target?.result === 'string') {
                            templatePreviewImage.src = event.target.result;
                        }
                    };
                    reader.readAsDataURL(file);
                };

                syncTemplateBodyPreview();
                templateBodyInput?.addEventListener('input', syncTemplateBodyPreview);
                if (!isTemplateRead) {
                    syncTemplateAssetPreview();
                    templateAssetInput?.addEventListener('change', syncTemplateAssetPreview);
                }

                const syncTemplateButtons = () => {
                    if (!templateButtonList || !templatePreviewActions || !templateAddButton || !templateButtonWarning) {
                        return;
                    }

                    const cards = Array.from(templateButtonList.querySelectorAll('[data-template-button-card]'));
                    templateButtonWarning.hidden = cards.length < 2;
                    templateAddButton.disabled = cards.length >= 2;

                    if (!cards.length) {
                        templatePreviewActions.innerHTML = '<div class="template-builder-phone__cta">Coba Sekarang</div>';
                        return;
                    }

                    templatePreviewActions.innerHTML = cards.map((card) => {
                        const label = card.querySelector('[data-template-button-text]')?.value?.trim() || 'Visit website';
                        return `<div class="template-builder-phone__cta">${label}</div>`;
                    }).join('');
                };

                const bindTemplateButtonCard = (card) => {
                    const textInput = card.querySelector('[data-template-button-text]');
                    const urlInput = card.querySelector('[data-template-button-url]');
                    const removeButton = card.querySelector('[data-template-button-remove]');
                    const textMeta = card.querySelector('[data-template-button-meta]');
                    const urlMeta = card.querySelector('[data-template-url-meta]');

                    const syncCard = () => {
                        if (textMeta && textInput) {
                            textMeta.textContent = `${textInput.value.length} / 25`;
                        }
                        if (urlMeta && urlInput) {
                            urlMeta.textContent = `${urlInput.value.length} / 2000`;
                        }
                        syncTemplateButtons();
                    };

                    textInput?.addEventListener('input', syncCard);
                    urlInput?.addEventListener('input', syncCard);
                    removeButton?.addEventListener('click', () => {
                        card.remove();
                        syncTemplateButtons();
                    });

                    syncCard();
                };

                templateAddButton?.addEventListener('click', () => {
                    if (!templateButtonList || templateButtonList.querySelectorAll('[data-template-button-card]').length >= 2) {
                        syncTemplateButtons();
                        return;
                    }

                    templateButtonIndex += 1;
                    const card = document.createElement('article');
                    card.className = 'template-builder-button-card';
                    card.dataset.templateButtonCard = String(templateButtonIndex);
                    card.innerHTML = `
                        <div class="template-builder-button-card__head">
                            <h3>Call to Action</h3>
                        </div>
                        <div class="template-builder-button-card__grid">
                            <div class="template-builder-button-card__field">
                                <label>Type of action</label>
                                <select>
                                    <option>Visit website</option>
                                </select>
                            </div>
                            <div class="template-builder-button-card__field">
                                <label>Button Text</label>
                                <input type="text" value="Visit website" maxlength="25" data-template-button-text>
                                <span class="template-builder-button-card__meta" data-template-button-meta>13 / 25</span>
                            </div>
                            <div class="template-builder-button-card__field">
                                <label>Url Type</label>
                                <select>
                                    <option>static</option>
                                </select>
                            </div>
                            <div class="template-builder-button-card__field">
                                <label>Website URL</label>
                                <input type="text" value="" maxlength="2000" placeholder="Website URL" data-template-button-url>
                                <span class="template-builder-button-card__meta" data-template-url-meta>0 / 2000</span>
                            </div>
                            <button type="button" class="template-builder-button-card__remove" data-template-button-remove aria-label="Hapus tombol">×</button>
                        </div>
                        <div class="template-builder-button-card__alert">
                            <span>⚠</span>
                            <p>Direct WhatsApp links are not allowed in CTA buttons as per Meta's policy. Please place the link (e.g. wa.me/6281xxx) in the message body instead</p>
                        </div>
                    `;

                    templateButtonList.appendChild(card);
                    bindTemplateButtonCard(card);
                    syncTemplateButtons();
                });

                syncTemplateButtons();
            })();
        </script>
    </body>
</html>
