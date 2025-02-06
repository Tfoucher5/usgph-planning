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
  <x-slot:title>{{ isset($motif) ? 'Modifier le motif' : 'Créer un nouveau motif' }}</x-slot:title>
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
      <div class="card shadow-lg" style="max-width: 600px; width: 100%;">
          <div class="card-header text-center">
              <h3>{{ isset($motif) ? 'Modifier le motif' : 'Créer un nouveau motif' }}</h3>
          </div>
          <div class="card-body">
              <x-inputs.form
                  :property="'motif'"
                  :entity="$motif ?? null"
                  :action="isset($motif) ? route('motif.update', $motif->id) : route('motif.store')"
                  method="{{ isset($motif) ? 'PUT' : 'POST' }}">

                  <x-inputs.input-text
                      property="nom"
                      label="Nom du motif"
                      placeholder="Entrez le nom du motif"
                      :old="old('nom')"
                      :entity="$motif ?? null"
                      required
                      autofocus />

                  <div class="form-group d-flex justify-content-between mb-0">
                      <a href="{{ route('motif.index') }}" class="btn btn-secondary">Retour</a>
                      <button type="submit" class="btn btn-primary">{{ isset($motif) ? 'Mettre à jour' : 'Créer le motif' }}</button>
                  </div>
              </x-inputs.form>
          </div>
      </div>
  </div>
</x-app-layout>
