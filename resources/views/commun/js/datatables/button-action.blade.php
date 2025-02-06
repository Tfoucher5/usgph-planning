@can((isset($ability_model) ? $ability_model : $path) . '-' . ($ability ?? 'update'))
  `
  <div data-bs-toggle="tooltip" title="{!! $titre !!}">
    <a href="{{ url($path) }}/` + row.{{ $id ?? 'id' }} + `/{{ $action }}" class="btn btn-outline-{{ $couleur }} btn-inline btn-fit"
      {{ $target ?? false ? 'target="_blank"' : '' }}>
      <i class="{{ $icone }} fa-fw"></i>
    </a>
  </div>&nbsp;
  `
@else
  `
  <div data-bs-toggle="tooltip" title="{{ $titre }}">
    <a href="#" class="btn btn-outline-dark btn-inline btn-fit disabled">
      <i class="{{ $icone }} fa-fw"></i>
    </a>
  </div>&nbsp;
  `
@endcan
