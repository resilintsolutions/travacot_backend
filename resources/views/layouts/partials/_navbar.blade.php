<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
  <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
    <a class="navbar-brand brand-logo me-5" href="{{url('/dashboard')}}"><img src="{{asset('dashboard_assets/assets/images/dashboard-logo.png')}}" class="me-2" alt="logo" /></a>
    <a class="navbar-brand brand-logo-mini" href="{{url('/dashboard')}}"><img src="{{asset('dashboard_assets/assets/images/dashboard-logo.png')}}" alt="logo" /></a>
  </div>
  <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
    {{-- <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
      <span class="icon-menu"></span>
    </button> --}}

    <ul class="navbar-nav navbar-nav-right">

      <li class="nav-item nav-profile dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
          <img src="{{asset('dashboard_assets/assets/images/user_icon.png')}}" alt="profile" />
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
          <a class="dropdown-item" href="{{route('profile.edit')}}">
            <i class="ti-settings text-primary"></i> Profile </a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf

            <a class="dropdown-item" :href="route('logout')"
              onclick="event.preventDefault();
                this.closest('form').submit();"> <i class="ti-power-off text-primary"></i>
              {{ __('Log Out') }}
            </a>
          </form>
        </div>
      </li>
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
      <span class="icon-menu"></span>
    </button>
  </div>
</nav>