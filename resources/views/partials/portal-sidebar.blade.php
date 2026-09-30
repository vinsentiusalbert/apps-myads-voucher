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
            <img src="{{ asset('assets/shopads-icon.svg') }}" alt="ShopAds" class="portal-brand__logo">
            <p class="portal-brand__text">ShopAds</p>
        </div>

        @if (! empty($createButtonHref))
            <a href="{{ $createButtonHref }}" class="portal-create-btn portal-create-btn--link">{{ $createButtonLabel }}</a>
        @else
            <a
                href="{{ route('campaign.menu', ['channel' => 'sms', 'menu' => 'location-based-area', 'view' => 'create']) }}"
                class="portal-create-btn portal-create-btn--link"
                data-campaign-category-open
            >{{ $createButtonLabel }}</a>
        @endif

        <nav class="portal-nav" aria-label="Main navigation">
            @foreach ($navMenus as $menu)
                @php
                    $isActive = ($activeNav ?? null) === $menu['key'];
                    $hasChildren = ! empty($menu['children']);
                    $isSelected = false;

                    if ($hasChildren) {
                        $isSelected = collect($menu['children'])->contains(fn ($child) => ($activeSubnav ?? null) === $child['key']);
                    }

                    $itemClasses = ['portal-nav__item'];
                    if ($isActive) {
                        $itemClasses[] = 'portal-nav__item--active';
                    }
                    if ($isSelected) {
                        $itemClasses[] = 'portal-nav__item--selected';
                        $itemClasses[] = 'portal-nav__item--open';
                    }
                    if ($hasChildren) {
                        $itemClasses[] = 'portal-nav__item--dropdown';
                    }
                @endphp

                <div class="{{ implode(' ', $itemClasses) }}" @if($hasChildren) data-nav-group @endif>
                    @if ($hasChildren)
                        <button type="button" class="portal-nav__head portal-nav__toggle" data-nav-toggle aria-controls="portal-subnav-{{ $menu['key'] }}" aria-expanded="{{ $isSelected ? 'true' : 'false' }}">
                            @include('partials.portal-sidebar-icon', ['icon' => $menu['icon']])
                            <span>{{ $menu['label'] }}</span>
                            <span class="portal-nav__caret" aria-hidden="true">
                                <svg viewBox="0 0 24 24">
                                    <path d="M7 10l5 5 5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </button>

                        <div class="portal-subnav" id="portal-subnav-{{ $menu['key'] }}" @if(! $isSelected) inert @endif>
                            @foreach ($menu['children'] as $child)
                                @php
                                    $childHref = $child['href'] ?? '#';
                                    $isChildActive = ($activeSubnav ?? null) === $child['key'];
                                @endphp
                                <a href="{{ $childHref }}" class="portal-subnav__item{{ $isChildActive ? ' portal-subnav__item--active' : '' }}" @if($isChildActive) aria-current="page" @endif @if(! empty($child['interactive'])) data-subnav-link @endif>
                                    <span class="portal-subnav__icon" aria-hidden="true">
                                        @include('partials.portal-sidebar-icon', ['icon' => $child['icon'], 'subnav' => true])
                                    </span>
                                    <span>{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        @php
                            $menuHref = $menu['href'] ?? '#';
                            $linkClasses = 'portal-nav__head';
                            if (! empty($menu['interactive'])) {
                                $linkClasses .= ' portal-nav__link';
                            }
                        @endphp
                        @if (($menu['href'] ?? null) !== null)
                            <a href="{{ $menuHref }}" class="{{ $linkClasses }}" @if($isActive) aria-current="page" @endif @if(! empty($menu['dashboard'])) data-dashboard-link @endif>
                                @include('partials.portal-sidebar-icon', ['icon' => $menu['icon']])
                                <span>{{ $menu['label'] }}</span>
                            </a>
                        @else
                            <button type="button" class="{{ $linkClasses }}" @if(! empty($menu['dashboard'])) data-dashboard-link @endif>
                                @include('partials.portal-sidebar-icon', ['icon' => $menu['icon']])
                                <span>{{ $menu['label'] }}</span>
                            </button>
                        @endif
                    @endif
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

        {{-- Membership badge / CTA di sidebar --}}
        @if (!empty($sidebarMembership['is_member']))
            <div class="sidebar-membership sidebar-membership--active">
                <span class="sidebar-membership__star">⭐</span>
                <div class="sidebar-membership__info">
                    <span class="sidebar-membership__label">Member Aktif</span>
                    <span class="sidebar-membership__quota">Unlimited SMS (Maks 5k/iklan)</span>
                </div>
            </div>
        @else
            <a href="{{ route('dashboard') }}#membership" class="sidebar-membership sidebar-membership--join" id="sidebar-join-membership">
                <span class="sidebar-membership__star">⭐</span>
                <span class="sidebar-membership__label">Join Membership</span>
                <span class="sidebar-membership__badge">Unlimited SMS</span>
            </a>
        @endif
    </div>
</aside>
