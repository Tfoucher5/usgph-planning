<div aria-label="breadcrumb" class="breadcrumb">
  <ol class="breadcrumb w-100 mt-3">
    <li class="breadcrumb-item">
      <a href="{{ route('welcome') }}" class="ms-2"><i class="fa-solid fa-house me-1"></i>{{ __('Accueil') }}</a>
    </li>
    @foreach ($breadcrumb_items as $breadcrumb_item)
      <li class="breadcrumb-item ">
        <a href="{{ $breadcrumb_item->lien }}" class="{{ $breadcrumb_item->isActive ? 'text-primary' : '' }}">{{ $breadcrumb_item->libelle }}</a>
      </li>
    @endforeach
  </ol>
</div>
