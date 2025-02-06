<header class="header" id="header">

  <div class="header_toggle">
    <i class='bx bx-menu' id="header-toggle"></i>
  </div>

  <div class="d-flex-block m-auto h6">
    Gestion du planning
  </div>

  <div class="dropdown">
    <a class="" id="userInfo" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <div class="avatar">{{ Auth::user()?->initials ?? '?' }}</div>
    </a>
    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userInfo">
      <div class="dropdown-header text-gray-700">
        <h6 class="text-uppercase font-weight-bold">{{ Auth::user()?->identity }}</h6><small>{{ Auth::user()?->inline_roles }}</small>
      </div>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="{{ route('logout') }}"
        onclick="
              event.preventDefault();
              Swal.fire({
                title: '{{ __('Would you like to disconnect?') }}',
                text: '',
                icon: 'warning'
              }).then((result) => {
                if (result.value) {
                  document.getElementById('logout-form').submit();
                }else {
                  return false
                }
              });
            ">
        {{ __('Logout') }}
      </a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
      </form>
    </div>
  </div>

</header>
