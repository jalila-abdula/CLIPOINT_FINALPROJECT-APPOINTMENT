@php
    $isPortalDashboard = request()->routeIs('portal.*', 'clients.*', 'appointments.*', 'reports.*', 'service-records.*', 'users.*', 'profile.*');
    $links = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'show' => true, 'icon' => 'dashboard'],
        ['label' => 'Client', 'route' => 'clients.index', 'show' => auth()->user()->isAdmin() || auth()->user()->isReceptionist(), 'icon' => 'client'],
        ['label' => 'Appointment', 'route' => 'appointments.index', 'show' => true, 'icon' => 'appointment'],
        ['label' => 'Service Record', 'route' => 'service-records.index', 'show' => true, 'icon' => 'record'],
        ['label' => 'Reports', 'route' => 'reports.index', 'show' => true, 'icon' => 'report'],
        ['label' => 'Users', 'route' => 'users.index', 'show' => auth()->user()->isAdmin(), 'icon' => 'users'],
    ];
@endphp

<aside @class([
       'app-sidebar',
       'dashboard-sidebar' => $isPortalDashboard,
   ])
       :class="{
           'translate-x-0': sidebarOpen,
           '-translate-x-full': !sidebarOpen
       }">
    <a href="{{ route('dashboard') }}" @class([
        'app-sidebar-logo',
        'dashboard-sidebar-logo' => $isPortalDashboard,
    ])>
        <x-application-logo @class([
            'h-16 w-16',
            'dashboard-sidebar-logo-mark' => $isPortalDashboard,
        ]) />
        <div>
            <p @class([
                'app-sidebar-title',
                'dashboard-sidebar-title' => $isPortalDashboard,
            ])>CLIPOINT</p>
            <p @class([
                'app-sidebar-caption',
                'dashboard-sidebar-caption' => $isPortalDashboard,
            ])>Appointment System</p>
        </div>
    </a>

    <nav @class([
        'app-nav-list',
        'dashboard-nav-list' => $isPortalDashboard,
    ])>
        @foreach ($links as $link)
            @if ($link['show'])
                @php
                    $active = request()->routeIs(str_replace('.index', '.*', $link['route'])) || request()->routeIs($link['route']);
                @endphp
                <a href="{{ route($link['route']) }}"
                   class="{{ $isPortalDashboard ? 'dashboard-nav-link' : 'app-nav-link' }} {{ $active ? ($isPortalDashboard ? 'dashboard-nav-link-active' : 'app-nav-link-active') : '' }}"
                   @click="if (window.innerWidth < 1024) sidebarOpen = false">
                    @switch($link['icon'])
                        @case('dashboard')
                            <svg class="app-nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3Zm10 8h8v-6h-8ZM3 21h8v-6H3Zm10-10h8V3h-8Z"/></svg>
                            @break
                        @case('client')
                            <svg class="app-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                            @break
                        @case('appointment')
                            <svg class="app-nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h2v2h6V2h2v2h3v18H4V4h3Zm11 7H6v11h12V9Z"/></svg>
                            @break
                        @case('record')
                            <svg class="app-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v6h6"/><path d="M10 13h4M10 17h4"/></svg>
                            @break
                        @case('report')
                            <svg class="app-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19h16"/><path d="M7 15V9"/><path d="M12 15V5"/><path d="M17 15v-3"/></svg>
                            @break
                        @default
                            <svg class="app-nav-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg>
                    @endswitch
                    <span>{{ $link['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    <div @class([
        'app-sidebar-profile-wrap mt-auto pt-8',
        'dashboard-sidebar-profile-wrap' => $isPortalDashboard,
    ]) x-data="{ profileOpen: false }">
        <button type="button"
                class="{{ $isPortalDashboard ? 'dashboard-sidebar-profile-trigger' : 'app-sidebar-profile-trigger' }}"
                @click="profileOpen = !profileOpen"
                @keydown.escape.window="profileOpen = false"
                aria-haspopup="menu"
                :aria-expanded="profileOpen">
            <span class="{{ $isPortalDashboard ? 'dashboard-sidebar-profile-avatar' : 'app-sidebar-profile-avatar' }}">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="{{ $isPortalDashboard ? 'dashboard-sidebar-profile-meta' : 'app-sidebar-profile-meta' }}">
                <span class="{{ $isPortalDashboard ? 'dashboard-sidebar-profile-name' : 'app-sidebar-profile-name' }}">{{ auth()->user()->name }}</span>
                <span class="{{ $isPortalDashboard ? 'dashboard-sidebar-profile-email' : 'app-sidebar-profile-email' }}">{{ auth()->user()->email }}</span>
            </span>
            <svg class="{{ $isPortalDashboard ? 'dashboard-sidebar-profile-chevron' : 'app-sidebar-profile-chevron' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div x-cloak
             x-show="profileOpen"
             x-transition.origin.bottom
             @click.outside="profileOpen = false"
             class="{{ $isPortalDashboard ? 'dashboard-sidebar-profile-dropdown' : 'app-sidebar-profile-dropdown' }}"
             role="menu">
            <a href="{{ route('profile.edit') }}" class="{{ $isPortalDashboard ? 'dashboard-sidebar-profile-item' : 'app-sidebar-profile-item' }}" role="menuitem" @click="profileOpen = false">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="{{ $isPortalDashboard ? 'dashboard-sidebar-profile-item dashboard-sidebar-profile-item-danger' : 'app-sidebar-profile-item app-sidebar-profile-item-danger' }}" role="menuitem">Log out</button>
            </form>
        </div>
    </div>
</aside>
