<x-app-layout>
@if ($errors->any())
  <div class="alert alert-danger">
      <ul>
          @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
      </ul>
  </div>
@endif
  <x-slot:title>{{ isset($lieu) ? 'Modifier le lieu' : 'Créer un nouveau lieu' }}</x-slot:title>
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
      <div class="card shadow-lg" style="max-width: 600px; width: 100%;">
          <div class="card-header text-center">
              <h3>{{ isset($lieu) ? 'Modifier le lieu' : 'Créer un nouveau lieu' }}</h3>
          </div>
          <div class="card-body">
              <x-inputs.form
                  :property="'lieu'"
                  :entity="$lieu ?? null"
                  :action="isset($lieu) ? route('lieu.update', $lieu->id) : route('lieu.store')"
                  method="{{ isset($lieu) ? 'PUT' : 'POST' }}">

                  <x-inputs.input-text
                      property="nom"
                      label="Nom du lieu"
                      placeholder="Entrez le nom du lieu"
                      :old="old('nom')"
                      :entity="$lieu ?? null"
                      required
                      autofocus />

                  <div class="form-group d-flex justify-content-between mb-0">
                      <a href="{{ route('lieu.index') }}" class="btn btn-secondary">Retour</a>
                      <button type="submit" class="btn btn-primary">{{ isset($lieu) ? 'Mettre à jour' : 'Créer le lieu' }}</button>
                  </div>
              </x-inputs.form>
          </div>
      </div>
  </div>
</x-app-layout>
