<div class="l-navbar" id="nav-bar">
    <nav class="nav">
        <div>
            <a href="#" class="nav_logo">
                <i class="bx bx-layer nav_logo-icon"></i><span class="nav_logo-name">Planning</span>
            </a>
            <div class="nav_list">
                <a href="{{ route('planning.index') }}"
                    class="nav_link {{ session('level_menu_2') == 'planning' ? 'menu-active' : '' }}">
                    <i class="bx bx-calendar nav_icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip"
                        data-bs-title="{{ Auth::user()->isA('admin') ? 'P' : 'Mon p' }}lanning"></i>
                    <span class="nav_name">{{ Auth::user()->isA('admin') ? 'P' : 'Mon p' }}lanning</span>
                </a>
                <a href="{{ route('absence.index')}}"
                    class="nav_link {{ session('level_menu_2') == 'absence' ? 'menu-active' : '' }}">
                    <i class="bx bx-user nav_icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip"
                        data-bs-title="{{ Auth::user()->isA('admin') ? 'A' : 'Mes a' }}bsences"></i>
                    <span class="nav_name">{{ Auth::user()->isA('admin') ? 'A' : 'Mes a' }}bsences</span>
                </a>
                @if (Auth::user()->isA('admin'))
                    <a href="{{ route('synthese.index') }}"
                        class="nav_link {{ session('level_menu_2') == 'synthese' ? 'menu-active' : '' }}">
                        <i class="bx bx-bar-chart-alt-2 nav_icon" data-bs-toggle="tooltip"
                            data-bs-custom-class="custom-tooltip" data-bs-title="Synthèse"></i>
                        <span class="nav_name">Synthèse</span>
                    </a>
                    <a href="{{ route('lieu.index') }}"
                        class="nav_link {{ session('level_menu_2') == 'lieu' ? 'menu-active' : '' }}">
                        <i class="bx bx-map-pin nav_icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip"
                            data-bs-title="Gestion des Lieux"></i>
                        <span class="nav_name">Lieux</span>
                    </a>
                    <a href="{{ route('tache.index') }}" class="nav_link {{ session('level_menu_2') == 'tache' ? 'menu-active' : '' }}">
                      <i class="bx bx-calendar-event nav_icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip"
                         data-bs-title="Planning prévisionnel"></i>
                      <span class="nav_name">Planning prévisionnel</span>
                    </a>
                    {{-- <a href="{{ route('motif.index') }}" class="nav_link {{ session('level_menu_2') == 'motif' ? 'menu-active' : '' }}">
                      <i class="bx bx-calendar-event nav_icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip"
                         data-bs-title="Motifs"></i>
                      <span class="nav_name">Motifs</span>
                    </a> --}}
                @endif
            </div>
        </div>
        <a class="nav_link" href="{{ route('logout') }}"
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
            <i class="bx bx-log-out nav_icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip"
                data-bs-title="{{ __('Logout') }}"></i>
            <span class="nav_name">{{ __('Logout') }}</span>
        </a>
    </nav>
</div>
