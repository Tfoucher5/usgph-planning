<x-app-layout>
    <x-slot:title>{{ isset($tache) ? 'Modifier la tâche' : 'Créer une nouvelle tâche' }}</x-slot:title>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow-lg" style="max-width: 800px; width: 100%;">
            <div class="card-header text-center">
                <h3>{{ isset($tache) ? 'Modifier la tâche' : 'Créer une nouvelle tâche' }}</h3>
            </div>
            <div class="card-body">
                <form id="planningForm"
                    action="{{ isset($tache) ? route('tache.update', $tache->id) : route('tache.store') }}"
                    method="POST">
                    @csrf
                    @if (isset($tache))
                        @method('PUT')
                    @endif

                    @if (auth()->user()->isA('admin'))
                        <div class="form-group mb-3">
                            <label for="user_id">Salarié</label>
                            <select id="user_id" name="user_id"
                                class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">Veuillez sélectionner un salarié</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('user_id', isset($tache) ? $tache->user_id : (request('user_id'))) == $user->id ? 'selected' : '' }}>
                                        {{ $user->identity }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                    @endif

                    <div class="form-group mb-3">
                        <label for="nom">Tâche</label>
                        <select name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror"
                            required>
                            <option value="" disabled selected>Veuillez sélectionner une tâche</option>
                            @foreach ($taches as $tacheList)
                                <option value="{{ $tacheList->nom }}"
                                    {{ (isset($tache) && $tache->nom == $tacheList->nom) || old('nom') == $tacheList->nom ? 'selected' : '' }}>
                                    {{ $tacheList->nom }}
                                </option>
                            @endforeach
                            {{-- <option value="other">Autre...</option> --}}
                        </select>
                        @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- <!-- Champ pour entrer une tâche personnalisée -->
                  <div class="form-group mb-3" id="other-task-group" style="display: none;">
                      <label for="other_task">Précisez votre tâche</label>
                      <input type="text" name="other_task" id="other_task"
                          class="form-control text-black @error('other_task') is-invalid @enderror">
                      @error('other_task')
                          <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                  </div> --}}


                    {{-- --}}
                    {{-- Possibilité d'ajouter un commentaire ? --}}
                    {{-- --}}

                    {{-- <div class="form-group mb-3">
                    <label for="commentaire">Commentaire</label>
                    <textarea type="text" id="commentaire" name="commentaire"
                        class="form-control @error('commentaire') is-invalid @enderror"
                        value="{{ old('commentaire', isset($tache) ? $tache->commentaire : '') }}" required>
                    </textarea>
                    @error('commentaire')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div> --}}

                    <div class="form-group mb-3">
                        <label for="lieu_id">Lieu</label>
                        <select name="lieu_id" id="lieu_id"
                            class="form-control @error('lieu_id') is-invalid @enderror" required>
                            <option value="" disabled selected>Veuillez sélectionner un lieu</option>
                            @foreach ($lieux as $lieu)
                                <option value="{{ $lieu->id }}"
                                    {{ (isset($tache) && $tache->lieu_id === $lieu->id) || old('lieu_id') == $lieu->id ? 'selected' : '' }}>
                                    {{ $lieu->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('lieu_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                      <label for="jour">Jour de la semaine</label>
                      <select name="jour" id="jour" class="form-control @error('jour') is-invalid @enderror" required>
                          <option value="" disabled selected>Veuillez sélectionner un jour</option>
                          <option value="1"
                              {{ old('jour', isset($tache) ? $tache->jour : request('jour')) == 1 ? 'selected' : '' }}>
                              Lundi
                          </option>
                          <option value="2"
                              {{ old('jour', isset($tache) ? $tache->jour : request('jour')) == 2 ? 'selected' : '' }}>
                              Mardi
                          </option>
                          <option value="3"
                              {{ old('jour', isset($tache) ? $tache->jour : request('jour')) == 3 ? 'selected' : '' }}>
                              Mercredi
                          </option>
                          <option value="4"
                              {{ old('jour', isset($tache) ? $tache->jour : request('jour')) == 4 ? 'selected' : '' }}>
                              Jeudi
                          </option>
                          <option value="5"
                              {{ old('jour', isset($tache) ? $tache->jour : request('jour')) == 5 ? 'selected' : '' }}>
                              Vendredi
                          </option>
                          <option value="6"
                              {{ old('jour', isset($tache) ? $tache->jour : request('jour')) == 6 ? 'selected' : '' }}>
                              Samedi
                          </option>
                          <option value="0"
                              {{ old('jour', isset($tache) ? $tache->jour : request('jour')) == 0 ? 'selected' : '' }}>
                              Dimanche
                          </option>
                      </select>
                      @error('jour')
                          <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                  </div>

                    <div class="form-group mb-3">
                        <label for="heure_debut">Heure de début</label>
                        <div class="d-flex">
                            <select id="heure_debut_heure" name="heure_debut_heure"
                                class="form-control @error('heure_debut_heure') is-invalid @enderror" required>
                                @for ($i = 0; $i < 24; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                        {{ old('heure_debut_heure', isset($tache) ? \Carbon\Carbon::parse($tache->heure_debut)->format('H') : (request('heure_debut') ? \Carbon\Carbon::parse(request('heure_debut'))->format('H') : '')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                            <span class="mx-2">:</span>
                            <select id="heure_debut_minute" name="heure_debut_minute"
                                class="form-control @error('heure_debut_minute') is-invalid @enderror" required>
                                <option value="00"
                                    {{ old('heure_debut_minute', isset($tache) ? \Carbon\Carbon::parse($tache->heure_debut)->format('i') : (request('heure_debut') ? \Carbon\Carbon::parse(request('heure_debut'))->format('i') : '')) == '00' ? 'selected' : '' }}>
                                    00</option>
                                <option value="15"
                                    {{ old('heure_debut_minute', isset($tache) ? \Carbon\Carbon::parse($tache->heure_debut)->format('i') : (request('heure_debut') ? \Carbon\Carbon::parse(request('heure_debut'))->format('i') : '')) == '15' ? 'selected' : '' }}>
                                    15</option>
                                <option value="30"
                                    {{ old('heure_debut_minute', isset($tache) ? \Carbon\Carbon::parse($tache->heure_debut)->format('i') : (request('heure_debut') ? \Carbon\Carbon::parse(request('heure_debut'))->format('i') : '')) == '30' ? 'selected' : '' }}>
                                    30</option>
                                <option value="45"
                                    {{ old('heure_debut_minute', isset($tache) ? \Carbon\Carbon::parse($tache->heure_debut)->format('i') : (request('heure_debut') ? \Carbon\Carbon::parse(request('heure_debut'))->format('i') : '')) == '45' ? 'selected' : '' }}>
                                    45</option>
                            </select>
                        </div>
                        @error('heure_debut_heure')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('heure_debut_minute')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="heure_fin">Heure de fin</label>
                        <div class="d-flex">
                            <select id="heure_fin_heure" name="heure_fin_heure"
                                class="form-control @error('heure_fin_heure') is-invalid @enderror" required>
                                @for ($i = 0; $i < 24; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}"
                                        {{ old('heure_fin_heure', isset($tache) ? \Carbon\Carbon::parse($tache->heure_fin)->format('H') : (request()->heure_fin ? substr(request()->heure_fin, 0, 2) : '')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                            <span class="mx-2">:</span>
                            <select id="heure_fin_minute" name="heure_fin_minute"
                                class="form-control @error('heure_fin_minute') is-invalid @enderror" required>
                                <option value="00"
                                    {{ old('heure_fin_minute', isset($tache) ? \Carbon\Carbon::parse($tache->heure_fin)->format('i') : (request()->heure_fin ? substr(request()->heure_fin, 3, 2) : '00')) == '00' ? 'selected' : '' }}>
                                    00</option>
                                <option value="15"
                                    {{ old('heure_fin_minute', isset($planning) ? \Carbon\Carbon::parse($planning->heure_fin)->format('i') : (request()->heure_fin ? substr(request()->heure_fin, 3, 2) : '00')) == '15' ? 'selected' : '' }}>
                                    15</option>
                                <option value="30"
                                    {{ old('heure_fin_minute', isset($planning) ? \Carbon\Carbon::parse($planning->heure_fin)->format('i') : (request()->heure_fin ? substr(request()->heure_fin, 3, 2) : '00')) == '30' ? 'selected' : '' }}>
                                    30</option>
                                <option value="45"
                                    {{ old('heure_fin_minute', isset($planning) ? \Carbon\Carbon::parse($planning->heure_fin)->format('i') : (request()->heure_fin ? substr(request()->heure_fin, 3, 2) : '00')) == '45' ? 'selected' : '' }}>
                                    45</option>
                            </select>
                        </div>
                        @error('heure_fin_heure')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('heure_fin_minute')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group d-flex justify-content-between mb-0">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Retour</a>
                        <button type="submit"
                            class="btn btn-primary">{{ isset($tache) ? 'Mettre à jour' : 'Créer la tâche' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const heureDebutHeureSelect = document.getElementById('heure_debut_heure');
            const heureDebutMinuteSelect = document.getElementById('heure_debut_minute');
            const heureFinHeureSelect = document.getElementById('heure_fin_heure');
            const heureFinMinuteSelect = document.getElementById('heure_fin_minute');

            // Validation du formulaire
            document.getElementById('planningForm').addEventListener('submit', function(event) {
                const heureDebut = parseInt(heureDebutHeureSelect.value) * 60 + parseInt(
                    heureDebutMinuteSelect.value);
                const heureFin = parseInt(heureFinHeureSelect.value) * 60 + parseInt(heureFinMinuteSelect
                    .value);

                if (heureFin <= heureDebut) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'L\'heure de fin doit être supérieure à l\'heure de début.',
                        text: 'Veuillez renseigner une heure de fin correcte.',
                        icon: 'warning',
                    });
                }
            });
        });
    </script>
</x-app-layout>
