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
  <x-slot:title>{{ isset($absence) ? 'Modifier l\'absence' : 'Déclarer une nouvelle absence' }}</x-slot:title>
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
      <div class="card shadow-lg" style="max-width: 600px; width: 100%;">
          <div class="card-header text-center">
              <h3>{{ isset($absence) ? 'Modifier l\'absence' : 'Déclarer une nouvelle absence' }}</h3>
          </div>
          <div class="card-body">
              <x-inputs.form
                  :property="'absence'"
                  :entity="$absence ?? null"
                  :action="isset($absence) ? route('absence.update', $absence->id) : route('absence.store')"
                  method="{{ isset($absence) ? 'PUT' : 'POST' }}">

                  <x-inputs.input-select2
                    :property="'motif_id'"
                    :name="'motif_id'"
                    :entity="$absence"
                    :label="'Motif'"
                    :values="$motifs"
                    :itemValue="'id'"
                    :itemLabel="'nom'"
                    :required="true"
                  />

                  <div class="form-group mb-3">
                    <label for="date_debut">Date de début</label>
                    <input type="date" id="date_debut" name="date_debut"
                        class="form-control @error('date_debut') is-invalid @enderror"
                        value="{{ old('date_debut', isset($absence) && $absence->date_debut ? \Carbon\Carbon::parse($absence->date_debut)->format('Y-m-d') : (request('date_debut') ? \Carbon\Carbon::parse(request('date_debut'))->format('Y-m-d') : '')) }}"
                        min="{{ \Carbon\Carbon::now()->format('Y-m-d')}}"
                        required>
                  </div>
                  <div class="form-group mb-3">
                    <label for="date_fin">Date de fin</label>
                    <input type="date" id="date_fin" name="date_fin"
                        class="form-control @error('date_fin') is-invalid @enderror"
                        value="{{ old('date_fin', isset($absence) && $absence->date_fin ? \Carbon\Carbon::parse($absence->date_fin)->format('Y-m-d') : (request('date_fin') ? \Carbon\Carbon::parse(request('date_fin'))->format('Y-m-d') : '')) }}"
                        min="{{ \Carbon\Carbon::now()->format('Y-m-d')}}"
                        required>
                  </div>
                  <div class="form-group d-flex justify-content-between mb-0">
                      <a href="{{ route('absence.index') }}" class="btn btn-secondary">Retour</a>
                      <button type="submit" class="btn btn-primary">{{ isset($absence) ? 'Mettre à jour' : 'Créer le absence' }}</button>
                  </div>
              </x-inputs.form>
          </div>
      </div>
  </div>
</x-app-layout>
