<div class="row mt-5">
  <div class="col-12 offset-md-{{ isset($route) ? 2 : 4 }} col-md-4 text-center">
    <a href="{{ url()->previous() == url()->current() ? url($retour) : url()->previous() }}" class="btn btn-sm d-block text-white btn-primary">
      <i class="fa fa-arrow-circle-left"></i></span>&nbsp;{{ __('Back') }}
    </a>
  </div>
  @if (isset($route))
    <div class="col-12 col-md-4 text-center">
      <a href="{{ route($route, $arguments) }}" class="btn btn-sm d-block text-white btn-warning">
        <i class="fa fa-edit"></i></span>&nbsp;{{ __('Cancel') }}
      </a>
    </div>
  @endif
</div>

<div class="col-12 text-end mt-3">* {{ __('required field') }}</div>
