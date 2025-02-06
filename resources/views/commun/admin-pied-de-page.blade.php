<div class="row mt-5 d-flex justify-content-end">
  {{-- <div id="div-btn-retour" class="col-12 col-md-4 text-center">
    <a href="#" id="btn-retour" class="btn btn-sm d-block text-white btn-primary" onclick="confirmation({{ $disabled ?? false }})">
      <i class="fa fa-arrow-circle-left"></i></span>&nbsp;{{ __('Back') }}
    </a>
  </div> --}}
  @if (!($disabled ?? false))
    <div id="div-btn-annulation" class="col-12 col-md-4 text-center">
      <a href="#" id="btn-annulation" class="btn btn-sm d-block text-white btn-danger" onclick="confirmation()">
        <i class="fa fa-ban"></i></span>&nbsp;{{ __('Cancel') }}
      </a>
    </div>
    <div id="div-btn-validation" class="col-12 col-md-4 text-center">
      <button type="submit" id="btn-validation" class="btn btn-sm d-block text-white btn-success w-100">
        <i class="fa fa-check"></i>&nbsp;{{ $texte_validation ?? __('Validate') }}
      </button>
    </div>
  @endif
</div>

<div class="col-12 text-end mt-3">* {{ __('required field') }}</div>

<script>
  function confirmation(disabled) {
    event.preventDefault();
    if (!disabled) {
      Swal.fire({
        title: '{{ __('The data entered will be lost, do you confirm?') }}',
        icon: 'question',
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: true,
        confirmButtonText: '{{ __('Yes') }}',
        cancelButtonText: '{{ __('No') }}',
      }).then((result) => {
        if (result.value) {
          window.location.href = '{{ url()->previous() == url()->current() ? url($retour) : url()->previous() }}';
        } else {
          return false
        }
      });
    } else {
      window.location.href = '{{ url()->previous() == url()->current() ? url($retour) : url()->previous() }}';
    }
  }
</script>

@if (count($errors) > 0)
  <script type="module">
    @if (count($errors) > 1)
      var message = '{{ __(':count errors were detected', ['count' => count($errors)]) }}';
    @else
      var message = '{{ __('1 error has been detected') }}';
    @endif
    $(document).ready(function() {
      Swal.fire({
        icon: 'error',
        html: message + ' {!! __('in the form.<br>Your input has not been sent. Please correct and try again') !!}',
        backdrop: `
        rgba(180, 4, 4, 0.4)
        `
      });
    });
  </script>
@endif
