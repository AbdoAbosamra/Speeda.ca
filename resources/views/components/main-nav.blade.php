@php
    $user = auth()->user();
    $provider = $user?->serviceProvider;
    $unreadCount = $unreadCount ?? 0;
    $hasUnread = $unreadCount > 0;
    $readIds = $readNotificationIds ?? [];
    $isServiceProvider = $user?->isServiceProvider();
@endphp

@once

@endonce

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const nav = document.querySelector('.sp-nav');
            if (!nav) return;

            const toggle = nav.querySelector('.sp-nav__toggle');
            const linksContainer = nav.querySelector('.sp-nav__links');
            const scrollProgress = document.getElementById('scrollProgress');

            // --- Notifications Logic ---
            const notifsContainers = document.querySelectorAll('.sp-notif');

            const markAllAsRead = async (btnElement, wrapperElement) => {
                const originalHTML = btnElement?.innerHTML;
                try {
                    if (btnElement) {
                        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __('general.loading') }}';
                        btnElement.disabled = true;
                    }
                    const res = await fetch('{{ route("notifications.mark-as-read") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        // Sync across all instances
                        document.querySelectorAll('.sp-notif__badge').forEach(b => b.setAttribute('hidden', ''));
                        document.querySelectorAll('.sp-notif').forEach(w => w.classList.remove('sp-notif--unread'));
                        document.querySelectorAll('.sp-notif__item--unread').forEach(el => {
                            el.classList.remove('sp-notif__item--unread');
                            el.querySelector('.sp-notif__dot')?.remove();
                        });
                        document.querySelectorAll('.sp-notif__header-count').forEach(c => c.setAttribute('hidden', ''));
                        document.querySelectorAll('.sp-notif__footer-btn').forEach(btn => btn.style.display = 'none');
                    }
                } catch (err) {
                    console.error('Error marking as read:', err);
                    if (btnElement) {
                        btnElement.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> خطأ!';
                        setTimeout(() => { btnElement.innerHTML = originalHTML || ''; btnElement.disabled = false; }, 2000);
                    }
                }
            };

            const closeAllNotifs = () => {
                notifsContainers.forEach(otherWrapper => {
                    otherWrapper.querySelector('.sp-notif__dropdown')?.setAttribute('aria-hidden', 'true');
                    otherWrapper.querySelector('.sp-notif__trigger')?.setAttribute('aria-expanded', 'false');
                });
            };

            notifsContainers.forEach(wrapper => {
                const trigger = wrapper.querySelector('.sp-notif__trigger');
                const dropdown = wrapper.querySelector('.sp-notif__dropdown');
                const badge = wrapper.querySelector('.sp-notif__badge');
                const list = wrapper.querySelector('.sp-notif__list');
                const markAllBtn = wrapper.querySelector('.sp-notif__footer-btn');

                if (list) {
                    list.addEventListener('scroll', () => {
                        list.classList.toggle('is-at-bottom', list.scrollHeight - list.scrollTop <= list.clientHeight + 20);
                    }, { passive: true });
                }

                if (markAllBtn) {
                    markAllBtn.addEventListener('click', () => markAllAsRead(markAllBtn, wrapper));
                }

                if (trigger && dropdown) {
                    trigger.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const wasOpen = dropdown.getAttribute('aria-hidden') === 'false';
                        
                        // Close all others
                        closeAllNotifs();
                        
                        // Close mobile menu if it is open
                        if (nav.classList.contains('is-open')) {
                            nav.classList.remove('is-open');
                            toggle?.setAttribute('aria-expanded', 'false');
                            linksContainer?.setAttribute('aria-hidden', 'true');
                        }

                        if (wasOpen) return; // if it was open, it's now closed by closeAllNotifs

                        dropdown.setAttribute('aria-hidden', 'false');
                        trigger.setAttribute('aria-expanded', 'true');
                        if (badge && !badge.hasAttribute('hidden')) await markAllAsRead(null, wrapper);
                    });
                }
            });

            // Close notifications on outside click
            document.addEventListener('click', (e) => {
                let insideNotif = false;
                notifsContainers.forEach(wrapper => {
                    const trigger = wrapper.querySelector('.sp-notif__trigger');
                    const dropdown = wrapper.querySelector('.sp-notif__dropdown');
                    if (trigger && dropdown && (trigger.contains(e.target) || dropdown.contains(e.target))) {
                        insideNotif = true;
                    }
                });
                
                if (!insideNotif) {
                    closeAllNotifs();
                }
                
                if (nav.classList.contains('is-open') && !nav.contains(e.target)) {
                    toggleMenu(); // Closes menu if clicking completely outside
                }
            });

            // Toggle Mobile Menu
            const toggleMenu = () => {
                const isOpen = nav.classList.toggle('is-open');
                toggle?.setAttribute('aria-expanded', String(isOpen));
                linksContainer?.setAttribute('aria-hidden', String(!isOpen));
                
                // If we are opening the menu, close any open notifications
                if (isOpen) {
                    closeAllNotifs();
                }
            };

            if (toggle) {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleMenu();
                });
            }

            let ticking = false;
            window.addEventListener('scroll', () => {
                if (!ticking) {
                    requestAnimationFrame(() => {
                        nav.classList.toggle('is-scrolled', window.scrollY > 60);
                        if (scrollProgress) {
                            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                            scrollProgress.style.width = `${docHeight > 0 ? (window.scrollY / docHeight) * 100 : 0}%`;
                        }
                        ticking = false;
                    });
                    ticking = true;
                }
            }, { passive: true });

            const notifModalEl = document.getElementById('notifDetailModal');
            const notifModal = notifModalEl ? new bootstrap.Modal(notifModalEl) : null;

            document.querySelectorAll('.sp-notif__item').forEach(item => {
                item.addEventListener('click', () => {
                    const title = item.getAttribute('data-notif-title');
                    const message = item.getAttribute('data-notif-message');
                    const time = item.getAttribute('data-notif-time');
                    const modalTitle = document.getElementById('notifDetailTitle');
                    const modalMessage = document.getElementById('notifDetailMessage');
                    const modalDate = document.querySelector('#notifDetailDate span');
                    if (modalTitle) modalTitle.textContent = title;
                    if (modalMessage) modalMessage.textContent = message;
                    if (modalDate) modalDate.textContent = time;
                    notifModal?.show();
                    closeNotif();
                });
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (nav.classList.contains('is-open')) { toggleMenu(); toggle?.focus(); }
                }
            });
        });
    </script>
@endonce

<div class="sp-scroll-progress" id="scrollProgress" aria-hidden="true"></div>

<nav class="sp-nav" role="navigation" aria-label="{{ __('general.main_navigation') }}">
    <div class="sp-nav__inner">
        <a class="sp-brand" href="{{ route('home') }}" aria-label="{{ config('app.name', 'Speeda') }} - {{ __('general.home') }}">
            <img width="128" height="128" class="sp-brand__img" src="{{ asset('images/speeda-logo-128.webp') }}" alt="{{ config('app.name', 'Speeda') }}" loading="eager">
        </a>

        @if($isServiceProvider)
            <div class="sp-notif sp-notif--mobile {{ $hasUnread ? 'sp-notif--unread' : '' }}">
                <button class="sp-notif__trigger" type="button" aria-label="{{ __('admin.notifications') }}{{ $hasUnread ? ' (' . $unreadCount . ' ' . __('general.new') . ')' : '' }}" aria-expanded="false" aria-haspopup="true">
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    <span class="sp-notif__badge" @if(!$hasUnread) hidden @endif>{{ $unreadCount }}</span>
                </button>
                <div class="sp-notif__dropdown" aria-hidden="true">
                    <div class="sp-notif__header">
                        <div class="sp-notif__header-left">
                            <i class="fas fa-bell" aria-hidden="true"></i>
                            <span>{{ __('admin.notifications') }}</span>
                            @if($unreadCount > 0)
                                <span class="sp-notif__header-count">{{ $unreadCount }}</span>
                            @endif
                        </div>
                        @if($unreadCount > 0)
                            <button type="button" class="sp-notif__footer-btn mark-all-read-btn">
                                <i class="fas fa-check-double" aria-hidden="true"></i>
                                {{ __('general.mark_all_read') }}
                            </button>
                        @endif
                    </div>
                    <div class="sp-notif__list">
                        @forelse($activeNotifications as $notif)
                            @php $isRead = in_array($notif->id, $readNotificationIds); @endphp
                            <div class="sp-notif__item {{ $isRead ? '' : 'sp-notif__item--unread' }}" 
                                 role="button"
                                 tabindex="0"
                                 data-notif-id="{{ $notif->id }}"
                                 data-notif-title="{{ $notif->title }}"
                                 data-notif-message="{{ $notif->message }}"
                                 data-notif-time="{{ $notif->created_at->diffForHumans() }}">
                                <div class="sp-notif__title">
                                    @if(!$isRead)<span class="sp-notif__dot" aria-hidden="true"></span>@endif
                                    <span>{{ Str::limit($notif->title, 50) }}</span>
                                </div>
                                <div class="sp-notif__message">{{ Str::limit($notif->message, 80) }}</div>
                                <div class="sp-notif__time">
                                    <i class="fas fa-clock" aria-hidden="true"></i>
                                    {{ $notif->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <div class="sp-notif__empty">
                                <i class="fas fa-bell-slash" aria-hidden="true"></i>
                                <div class="sp-notif__empty-text">{{ __('admin.no_notifications') }}</div>
                                <div class="sp-notif__empty-sub">{{ __('general.all_caught_up') }}</div>
                            </div>
                        @endforelse
                    </div>
                    <div class="sp-notif__footer">
                        <a href="{{ route('notifications.index') }}" class="sp-notif__view-all">
                            <i class="fas fa-list" aria-hidden="true"></i>
                            {{ __('general.view_all_notifications') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <button class="sp-nav__toggle" type="button" aria-label="{{ __('general.toggle_navigation') }}" aria-expanded="false" aria-controls="navLinks">
            <span class="sp-nav__toggle-bar"></span>
            <span class="sp-nav__toggle-bar"></span>
            <span class="sp-nav__toggle-bar"></span>
        </button>

        <div class="sp-nav__links" id="navLinks" aria-hidden="false">
            <div class="sp-nav__menu" role="menubar">
                <a class="sp-link" href="{{ route('home') }}" role="menuitem" @if(request()->routeIs('home')) aria-current="page" @endif>
                    <i class="fas fa-home" aria-hidden="true"></i> {{ __('general.home') }}
                </a>
                <a class="sp-link" href="{{ route('categories') }}" role="menuitem" @if(request()->routeIs('categories')) aria-current="page" @endif>
                    <i class="fas fa-th-large" aria-hidden="true"></i> {{ __('general.categories') }}
                </a>
                <a class="sp-link" href="{{ route('service-providers.index') }}" role="menuitem" @if(request()->routeIs('service-providers.*')) aria-current="page" @endif>
                    <i class="fas fa-users" aria-hidden="true"></i> {{ __('service_provider.service_providers') }}
                </a>
                <a class="sp-link" href="{{ route('offers.index') }}" role="menuitem" @if(request()->routeIs('offers.*')) aria-current="page" @endif>
                    <i class="fas fa-tag" aria-hidden="true"></i> {{ __('offers.nav_offers') }}
                </a>
            </div>

            <div class="sp-actions">
                @include('components.language-switcher')

                @if($isServiceProvider)
                    <div class="sp-notif sp-notif--desktop {{ $hasUnread ? 'sp-notif--unread' : '' }}">
                        <button class="sp-notif__trigger" type="button" aria-label="{{ __('admin.notifications') }}{{ $hasUnread ? ' (' . $unreadCount . ' ' . __('general.new') . ')' : '' }}" aria-expanded="false" aria-haspopup="true">
                            <i class="fas fa-bell" aria-hidden="true"></i>
                            <span class="sp-notif__badge" @if(!$hasUnread) hidden @endif>{{ $unreadCount }}</span>
                        </button>
                        <div class="sp-notif__dropdown" aria-hidden="true">
                            <div class="sp-notif__header">
                                <div class="sp-notif__header-left">
                                    <i class="fas fa-bell" aria-hidden="true"></i>
                                    <span>{{ __('admin.notifications') }}</span>
                                    @if($unreadCount > 0)
                                        <span class="sp-notif__header-count">{{ $unreadCount }}</span>
                                    @endif
                                </div>
                                @if($unreadCount > 0)
                                    <button type="button" class="sp-notif__footer-btn mark-all-read-btn">
                                        <i class="fas fa-check-double" aria-hidden="true"></i>
                                        {{ __('general.mark_all_read') }}
                                    </button>
                                @endif
                            </div>
                            <div class="sp-notif__list">
                                @forelse($activeNotifications as $notif)
                                    @php $isRead = in_array($notif->id, $readNotificationIds); @endphp
                                    <div class="sp-notif__item {{ $isRead ? '' : 'sp-notif__item--unread' }}" 
                                         role="button"
                                         tabindex="0"
                                         data-notif-id="{{ $notif->id }}"
                                         data-notif-title="{{ $notif->title }}"
                                         data-notif-message="{{ $notif->message }}"
                                         data-notif-time="{{ $notif->created_at->diffForHumans() }}">
                                        <div class="sp-notif__title">
                                            @if(!$isRead)<span class="sp-notif__dot" aria-hidden="true"></span>@endif
                                            <span>{{ Str::limit($notif->title, 50) }}</span>
                                        </div>
                                        <div class="sp-notif__message">{{ Str::limit($notif->message, 80) }}</div>
                                        <div class="sp-notif__time">
                                            <i class="fas fa-clock" aria-hidden="true"></i>
                                            {{ $notif->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="sp-notif__empty">
                                        <i class="fas fa-bell-slash" aria-hidden="true"></i>
                                        <div class="sp-notif__empty-text">{{ __('admin.no_notifications') }}</div>
                                        <div class="sp-notif__empty-sub">{{ __('general.all_caught_up') }}</div>
                                    </div>
                                @endforelse
                            </div>
                            <div class="sp-notif__footer">
                                <a href="{{ route('notifications.index') }}" class="sp-notif__view-all">
                                    <i class="fas fa-list" aria-hidden="true"></i>
                                    {{ __('general.view_all_notifications') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                @auth
                    @if($user->isClient())
                        <a href="{{ route('offers.favorites') }}" class="sp-btn" @if(request()->routeIs('offers.favorites')) aria-current="page" @endif>
                            <i class="far fa-bookmark" aria-hidden="true"></i> {{ __('offers.my_favorites') }}
                        </a>
                    @endif
                    <div class="sp-user" aria-label="{{ $user->name }}">
                        <img class="sp-user__avatar" src="{{ $user->profile_photo_url ?? asset('images/user.png') }}" onerror="this.onerror=null;this.src='{{ asset('images/user.png') }}';" alt="{{ $user->name }}" loading="lazy">
                        <div class="sp-user__details">
                            <span class="sp-user__name">{{ $user->name }}</span>
                            <span class="sp-user__email">{{ $user->email }}</span>
                        </div>
                    </div>

                    @if($provider)
                        <a href="{{ route('service-providers.show', $provider) }}" class="sp-btn sp-btn--primary text-white" @if(request()->url() === route('service-providers.show', $provider)) aria-current="page" @endif>
                            <i class="fas fa-id-card" aria-hidden="true"></i> {{ __('general.my_profile') }}
                        </a>
                    @elseif($user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="sp-btn sp-btn--primary text-white">
                            <i class="fas fa-tachometer-alt" aria-hidden="true"></i> {{ __('admin.dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('profile.edit') }}" class="sp-btn sp-btn--primary text-white" @if(request()->routeIs('profile.edit')) aria-current="page" @endif>
                            <i class="fas fa-user-gear" aria-hidden="true"></i> {{ __('general.my_account') }}
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" style="display: contents;">
                        @csrf
                        <button type="submit" class="sp-btn sp-btn--danger">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i> {{ __('general.logout') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}?tab=login" class="sp-btn sp-btn--primary text-white">
                        <i class="fas fa-sign-in-alt" aria-hidden="true"></i> {{ __('general.login') }}
                    </a>
                    @guest
                        <a href="{{ route('login') }}?tab=register" class="sp-btn sp-btn--primary text-white">
                            <i class="fas fa-user-plus" aria-hidden="true"></i> {{ __('general.register') }}
                        </a>
                    @endguest
                @endauth
            </div>
        </div>
    </div>
</nav>
<!-- Notification Detail Modal -->
<div class="modal fade" id="notifDetailModal" tabindex="-1" aria-labelledby="notifDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('general.close') }}"></button>
                <div class="modal-meta" id="notifDetailDate">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    <span></span>
                </div>
                <h5 class="modal-title" id="notifDetailTitle"></h5>
            </div>
            <div class="modal-body" id="notifDetailMessage"></div>
            <div class="modal-footer">
                <button type="button" class="sp-btn sp-btn--primary" data-bs-dismiss="modal">
                    {{ __('general.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

