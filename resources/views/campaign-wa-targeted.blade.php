@php
    $title = $page['title'];
    $mainClass = 'campaign-main';
    $contentClass = 'campaign-content';
    $navMenus = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'href' => route('dashboard'),
        ],
        [
            'key' => 'sms',
            'label' => 'SMS',
            'icon' => 'sms',
            'children' => [
                [
                    'key' => 'sms-location-based-area',
                    'label' => 'Location Based Area',
                    'icon' => 'location',
                    'href' => route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area']),
                ],
                [
                    'key' => 'sms-targeted',
                    'label' => 'Targeted',
                    'icon' => 'targeted',
                    'href' => route('campaign.menu', ['channel' => 'sms', 'menu' => 'targeted']),
                ],
            ],
        ],
        [
            'key' => 'wa-business',
            'label' => 'WA Business',
            'icon' => 'wa',
            'children' => [
                [
                    'key' => 'wa-campaign-template',
                    'label' => 'Campaign Template',
                    'icon' => 'template',
                    'href' => route('campaign-template.index'),
                ],
                [
                    'key' => 'wa-location-based-area',
                    'label' => 'Location Based Area',
                    'icon' => 'location',
                    'href' => route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'location-based-area']),
                ],
                [
                    'key' => 'wa-targeted',
                    'label' => 'Targeted',
                    'icon' => 'targeted',
                    'href' => route('campaign.menu', ['channel' => 'wa-business', 'menu' => 'targeted']),
                ],
            ],
        ],
    ];
    $activeNav = 'wa-business';
    $activeSubnav = 'wa-targeted';
    $waTemplates = collect($waTemplates ?? []);
@endphp

@extends('layouts.portal')

@section('content')
    <section class="campaign-page-intro">
        <p class="campaign-page-intro__crumb">Dashboard / Buat Iklan WA Business Targeted</p>
        <h1 class="campaign-page-intro__title">Buat Iklan WA Business Targeted</h1>
    </section>

    <section class="campaign-stepper-card campaign-stepper-card--five">
        <div class="campaign-stepper campaign-stepper--five">
            @foreach ($page['steps'] as $index => $step)
                <div class="campaign-step {{ $index === 0 ? 'campaign-step--active' : '' }}">
                    <span class="campaign-step__dot">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="campaign-step__label">{{ $step }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="campaign-info-banner">
        <span class="campaign-info-banner__icon">i</span>
        <p>Pelajari cara membuat iklan Anda agar lebih menarik. <a href="#">Pelajari Selengkapnya.</a></p>
    </section>

    <section class="campaign-compose-card campaign-compose-card--single">
        <div class="campaign-compose-card__header campaign-compose-card__header--single">
            <div>
                <h2 class="campaign-compose-card__section-title">Template Pesan</h2>
            </div>
        </div>

        <div class="campaign-wa-lba-step" id="waTargetedStep">
            <div class="campaign-wa-lba-step__layout">
                <div class="campaign-wa-lba-step__main">
                    <div class="field-group campaign-wa-lba-step__field">
                        <label for="waTargetedTemplate" class="field-label sr-only">Template Pesan</label>
                        <select id="waTargetedTemplate" class="text-input text-input--select">
                            <option value="">Template Pesan</option>
                            @foreach ($waTemplates as $template)
                                <option value="{{ $template['id'] }}">{{ $template['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campaign-wa-lba-template" id="waTargetedTemplateDetails" hidden>
                        <p class="campaign-wa-lba-template__note" id="waTargetedPriceNote"></p>

                        <div class="campaign-wa-lba-template__meta">
                            <div>
                                <span>Akun WA Business</span>
                                <strong id="waTargetedAccountName">-</strong>
                            </div>
                            <div>
                                <span>Tipe Template</span>
                                <strong id="waTargetedTemplateKind">-</strong>
                            </div>
                            <div>
                                <span>Tipe Template</span>
                                <strong id="waTargetedChannelKind">-</strong>
                            </div>
                        </div>

                        <div class="campaign-wa-lba-template__body">
                            <span>Bubble Message</span>
                            <strong id="waTargetedBubbleHeadline">-</strong>
                            <p id="waTargetedBubbleMessage">-</p>
                        </div>

                        <div class="campaign-wa-lba-template__cards" id="waTargetedCardsBlock" hidden>
                            <span>Carousel Template</span>
                            <div class="campaign-wa-lba-template__accordion" id="waTargetedCardsAccordion"></div>
                        </div>
                    </div>
                </div>

                <aside class="campaign-wa-lba-preview" id="waTargetedPreview" hidden>
                    <div class="campaign-wa-lba-preview__phone" id="waTargetedPreviewPhone">
                        <div class="campaign-wa-lba-preview__topbar">
                            <span class="campaign-wa-lba-preview__back">←</span>
                            <div class="campaign-wa-lba-preview__profile">
                                <span class="campaign-wa-lba-preview__avatar"></span>
                                <div>
                                    <strong id="waTargetedPreviewName">Name</strong>
                                    <span id="waTargetedPreviewSubtitle">Business Account</span>
                                </div>
                            </div>
                            <span class="campaign-wa-lba-preview__menu">⋮</span>
                        </div>

                        <div class="campaign-wa-lba-preview__chat">
                            <div class="campaign-wa-lba-preview__bubble">
                                <strong id="waTargetedPreviewHeadline">-</strong>
                                <p id="waTargetedPreviewMessage">-</p>
                            </div>

                            <div class="campaign-wa-lba-preview__carousel">
                                <img src="{{ asset('assets/logo.png') }}" alt="Preview template" id="waTargetedPreviewAsset">
                                <div class="campaign-wa-lba-preview__carousel-copy">
                                    <span class="campaign-wa-lba-preview__brand">MyAds</span>
                                    <strong id="waTargetedPreviewCardTitle">Template Carousel</strong>
                                    <p id="waTargetedPreviewCardCaption">Detail card pertama akan tampil di sini.</p>
                                </div>
                            </div>

                            <div class="campaign-wa-lba-preview__action" id="waTargetedPreviewCta">Coba Sekarang</div>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="campaign-form-actions campaign-form-actions--wa-lba">
                <button type="button" class="campaign-draft-btn">
                    <span class="campaign-draft-btn__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M6 3.75h8.75L19.5 8.5v11a1.75 1.75 0 0 1-1.75 1.75H6A1.75 1.75 0 0 1 4.25 19.5V5.5A1.75 1.75 0 0 1 6 3.75Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M14.75 3.75V8.5h4.75" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M8 12h8M8 16h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span>Simpan Iklan Sebagai Draft</span>
                </button>

                <button type="button" class="submit-btn lba-primary-btn campaign-step-nav-btn" id="waTargetedNextButton" disabled>
                    Lanjutkan
                </button>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const templateSelect = document.getElementById('waTargetedTemplate');
            const stepRoot = document.getElementById('waTargetedStep');
            const nextButton = document.getElementById('waTargetedNextButton');
            const templateDetails = document.getElementById('waTargetedTemplateDetails');
            const preview = document.getElementById('waTargetedPreview');
            const previewPhone = document.getElementById('waTargetedPreviewPhone');
            const priceNote = document.getElementById('waTargetedPriceNote');
            const accountName = document.getElementById('waTargetedAccountName');
            const templateKind = document.getElementById('waTargetedTemplateKind');
            const channelKind = document.getElementById('waTargetedChannelKind');
            const bubbleHeadline = document.getElementById('waTargetedBubbleHeadline');
            const bubbleMessage = document.getElementById('waTargetedBubbleMessage');
            const cardsBlock = document.getElementById('waTargetedCardsBlock');
            const cardsAccordion = document.getElementById('waTargetedCardsAccordion');
            const previewName = document.getElementById('waTargetedPreviewName');
            const previewSubtitle = document.getElementById('waTargetedPreviewSubtitle');
            const previewHeadline = document.getElementById('waTargetedPreviewHeadline');
            const previewMessage = document.getElementById('waTargetedPreviewMessage');
            const previewAsset = document.getElementById('waTargetedPreviewAsset');
            const previewCardTitle = document.getElementById('waTargetedPreviewCardTitle');
            const previewCardCaption = document.getElementById('waTargetedPreviewCardCaption');
            const previewCta = document.getElementById('waTargetedPreviewCta');
            const templates = @json($waTemplates->values());

            const renderCards = (cards) => {
                if (!cardsAccordion || !cardsBlock) {
                    return;
                }

                cardsAccordion.innerHTML = '';
                const hasCards = Array.isArray(cards) && cards.length > 0;
                cardsBlock.hidden = !hasCards;

                if (!hasCards) {
                    return;
                }

                cards.forEach((card, index) => {
                    const item = document.createElement('article');
                    item.className = 'campaign-wa-lba-template__card-item';
                    item.innerHTML = `
                        <button type="button" class="campaign-wa-lba-template__card-toggle" data-wa-targeted-card-toggle>
                            <span>${card.title || `Card ${index + 1}`}</span>
                            <span>⌄</span>
                        </button>
                        <div class="campaign-wa-lba-template__card-panel" hidden>
                            <p>${card.caption || 'Detail card template.'}</p>
                        </div>
                    `;
                    cardsAccordion.appendChild(item);
                });
            };

            const syncTemplateDisplay = () => {
                if (!templateSelect || !nextButton) {
                    return;
                }

                const selected = templates.find((template) => template.id === templateSelect.value);
                const hasValue = Boolean(selected);
                nextButton.disabled = !hasValue;
                stepRoot?.classList.toggle('campaign-wa-lba-step--selected', hasValue);
                templateDetails.hidden = !hasValue;
                preview.hidden = !hasValue;
                previewPhone?.classList.remove('campaign-wa-lba-preview__phone--basic', 'campaign-wa-lba-preview__phone--carousel');

                if (!selected) {
                    cardsAccordion.innerHTML = '';
                    return;
                }

                priceNote.textContent = selected.price_note || '';
                accountName.textContent = selected.account_name || '-';
                templateKind.textContent = selected.template_kind || '-';
                channelKind.textContent = selected.channel_kind || '-';
                bubbleHeadline.textContent = selected.bubble_headline || '-';
                bubbleMessage.textContent = selected.bubble_message || '-';
                previewName.textContent = selected.account_name || selected.preview_name || 'Name';
                previewSubtitle.textContent = selected.channel_kind === 'Carousel' ? 'Carousel Business Account' : 'Business Account';
                previewHeadline.textContent = selected.bubble_headline || '-';
                previewMessage.textContent = selected.bubble_message || '-';
                previewAsset.src = selected.asset_url || @json(asset('assets/logo.png'));
                previewCardTitle.textContent = (selected.cards && selected.cards[0] && selected.cards[0].title) || 'Template Carousel';
                previewCardCaption.textContent = (selected.cards && selected.cards[0] && selected.cards[0].caption) || selected.bubble_message || 'Detail card pertama akan tampil di sini.';
                previewCta.textContent = selected.cta || 'Coba Sekarang';
                previewPhone?.classList.add(selected.cards && selected.cards.length ? 'campaign-wa-lba-preview__phone--carousel' : 'campaign-wa-lba-preview__phone--basic');
                renderCards(selected.cards || []);
            };

            templateSelect?.addEventListener('change', syncTemplateDisplay);
            cardsAccordion?.addEventListener('click', (event) => {
                const toggle = event.target.closest('[data-wa-targeted-card-toggle]');
                if (!toggle) {
                    return;
                }

                const item = toggle.closest('.campaign-wa-lba-template__card-item');
                const panel = item?.querySelector('.campaign-wa-lba-template__card-panel');
                const isOpen = panel && !panel.hidden;

                if (panel) {
                    panel.hidden = isOpen;
                }
            });

            syncTemplateDisplay();
        });
    </script>
@endpush
