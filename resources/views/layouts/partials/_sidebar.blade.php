<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <div class="pt-4 pl-5">
        <a class="navbar-brand brand-logo me-5" href="{{ url('/dashboard') }}"><img
                src="{{ asset('dashboard_assets/assets/images/dashboard-logo.png') }}" class="me-2" alt="logo" /></a>
    </div>
    <ul class="nav flex-column sidebar-list">

        <!-- Home -->
        <li class="menu-section mb-2">Home</li>

        <li class="nav-item">
            <a class="nav-link small-item" href="{{ route('admin.kpis') }}">KPIs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item" href="{{ route('admin.api-status.index') }}">API Status Cards</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item" href="{{ route('admin.today-performance.index') }}">Today's performance</a>
        </li>

        <!-- Inventory section -->
        <li class="menu-section">Inventory</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.inventory.hotels_list') ? 'active' : '' }}"
                href="{{ route('admin.inventory.hotels_list') }}">
                Hotels List
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.inventory.pinned') ? 'active' : '' }}"
                href="{{ route('admin.inventory.pinned.index') }}">
                Pinned Hotels
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.inventory.content-health*') ? 'active' : '' }}"
                href="{{ route('admin.inventory.content_health.index') }}">
                Content Health
            </a>
        </li>



        <!-- Pricing & Revenue -->
        <li class="menu-section">Pricing &amp; Revenue</li>
        <li class="nav-item">
            <a class="nav-link small-item {{ request()->routeIs('admin.msp.index') ? 'active' : '' }}"
                href="{{ route('admin.msp.index') }}">
                MSP (Minimum Selling Price)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item {{ request()->routeIs('admin.margin-rules.index') ? 'active' : '' }}"
                href="{{ route('admin.margin-rules.index') }}">
                Margin Rules
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item {{ request()->routeIs('admin.ppm.index') ? 'active' : '' }}"
                href="{{ route('admin.ppm.index') }}">
                PPM (Price & Perf. Mgmt.)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item {{ request()->routeIs('admin.promo-engine.*') ? 'active' : '' }}"
                href="{{ route('admin.promo-engine.index') }}">
                Promo Engine
            </a>
        </li>
        {{-- <li class="nav-item">
            <a class="nav-link small-item" href="#">Rate Parity</a>
        </li> --}}

        <!-- Reservations -->
        <li class="menu-section">Reservations</li>
        <li class="nav-item">
            <a class="nav-link small-item" href="{{ route('admin.reservations.index') }}">All Reservations</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item" href="{{ route('admin.reservations.failed') }}">Failed Bookings</a>
        </li>

        <!-- Exclusions -->
        <li class="menu-section">Exclusions</li>
        <li class="nav-item">
            <a class="nav-link small-item" href="{{ url('admin/exclusions/exclusions') }}">Automated Exclusion
                Rules</a>
        </li>
        {{-- <li class="nav-item">
            <a class="nav-link small-item" href="#">Manual Exclusions</a>
        </li> --}}

        <!-- System Health -->
        <li class="menu-section">System Health</li>
        <li class="nav-item">
            <a class="nav-link small-item" href="{{ route('admin.system-health.api-health.index') }}">API Health</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item" href="#">Destination Health</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item" href="#">Alerts Center</a>
        </li>

        <!-- Analytics -->
        <li class="menu-section">Analytics</li>
        <li class="nav-item">
            <a class="nav-link small-item" href="#">Top Destinations</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item" href="#">Sales Performance</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item" href="#">Guest Insights</a>
        </li>

        <!-- Support -->
        <li class="menu-section">Support</li>
        <li class="nav-item">
            <a class="nav-link small-item {{ request()->routeIs('admin.support.*') ? 'active' : '' }}"
                href="{{ route('admin.support.index') }}">
                Customer Support
            </a>
        </li>

        <!-- Settings -->
        {{-- <li class="menu-section">Settings</li>
        <li class="nav-item">
            <a class="nav-link small-item" href="#">API Keys</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item" href="#">User &amp; Roles</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small-item" href="#">System Config</a>
        </li> --}}

    </ul>
    <div class="navbar-menu-wrapper align-items-center justify-content-end pl-4">
        <ul class="navbar-nav navbar-nav-right">

            <li class="nav-item nav-profile dropdown">
                <a class="nav-link" href="#" data-bs-toggle="dropdown" id="profileDropdown">
                    <p>Profile & Logout</p>
                    {{-- <img src="{{asset('dashboard_assets/assets/images/user_icon.png')}}" alt="profile" /> --}}
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="ti-settings text-primary"></i> Profile </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <a class="dropdown-item" :href="route('logout')"
                            onclick="event.preventDefault();
                    this.closest('form').submit();"> <i
                                class="ti-power-off text-primary"></i>
                            {{ __('Log Out') }}
                        </a>
                    </form>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-toggle="offcanvas">
            <span class="icon-menu"></span>
        </button>
    </div>
</nav>
