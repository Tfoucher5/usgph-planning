<x-app-layout>
    <x-slot:title>{{ isset($planning) ? 'Modifier la tâche' : 'Créer une nouvelle tâche' }}</x-slot:title>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow-lg" style="max-width: 800px; width: 100%;">
            <div class="card-header text-center">
                <h3>{{ isset($planning) ? 'Modifier la tâche' : 'Créer une nouvelle tâche' }}</h3>
            </div>
            <div class="card-body">
                <form id="planningForm"
                    action="{{ isset($planning) ? route('planning.update', $planning->id) : route('planning.store') }}"
                    method="POST">
                    @csrf
                    @if (isset($planning))
                        @method('PUT')
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (auth()->user()->isA('admin'))
                        <div class="form-group mb-3">
                            <label for="user_id">Salarié</label>
                            <select id="user_id" name="user_id"
                                class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">Veuillez sélectionner un salarié</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('user_id', isset($planning) ? $planning->user_id : request('user_id')) == $user->id ? 'selected' : '' }}>
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
                            @foreach ($taches as $tache)
                                <option value="{{ $tache->nom }}"
                                    {{ (isset($planning) && $planning->nom == $tache->nom) || old('nom') == $tache->nom ? 'selected' : '' }}>
                                    {{ $tache->nom }}
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
                          value="{{ old('commentaire', isset($planning) ? $planning->commentaire : '') }}" required>
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
                                    {{ (isset($planning) && $planning->lieu_id === $lieu->id) || old('lieu_id') == $lieu->id ? 'selected' : '' }}>
                                    {{ $lieu->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('lieu_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="plannifier_le">Date prévue</label>
                        <input type="date" id="plannifier_le" name="plannifier_le"
                            class="form-control @error('plannifier_le') is-invalid @enderror"
                            value="{{ old('plannifier_le', isset($planning) && $planning->plannifier_le ? \Carbon\Carbon::parse($planning->plannifier_le)->format('Y-m-d') : (request('plannifier_le') ? \Carbon\Carbon::parse(request('plannifier_le'))->format('Y-m-d') : '')) }}"
                            required>
                        @error('plannifier_le')
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
                                        {{ old('heure_debut_heure', isset($planning) ? \Carbon\Carbon::parse($planning->heure_debut)->format('H') : (request('heure_debut') ? \Carbon\Carbon::parse(request('heure_debut'))->format('H') : '')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                            <span class="mx-2">:</span>
                            <select id="heure_debut_minute" name="heure_debut_minute"
                                class="form-control @error('heure_debut_minute') is-invalid @enderror" required>
                                <option value="0"
                                    {{ old('heure_debut_minute', isset($planning) ? \Carbon\Carbon::parse($planning->heure_debut)->format('i') : (request('heure_debut') ? \Carbon\Carbon::parse(request('heure_debut'))->format('i') : '')) == '00' ? 'selected' : '' }}>
                                    00</option>
                                <option value="15"
                                    {{ old('heure_debut_minute', isset($planning) ? \Carbon\Carbon::parse($planning->heure_debut)->format('i') : (request('heure_debut') ? \Carbon\Carbon::parse(request('heure_debut'))->format('i') : '')) == '15' ? 'selected' : '' }}>
                                    15</option>
                                <option value="30"
                                    {{ old('heure_debut_minute', isset($planning) ? \Carbon\Carbon::parse($planning->heure_debut)->format('i') : (request('heure_debut') ? \Carbon\Carbon::parse(request('heure_debut'))->format('i') : '')) == '30' ? 'selected' : '' }}>
                                    30</option>
                                <option value="45"
                                    {{ old('heure_debut_minute', isset($planning) ? \Carbon\Carbon::parse($planning->heure_debut)->format('i') : (request('heure_debut') ? \Carbon\Carbon::parse(request('heure_debut'))->format('i') : '')) == '45' ? 'selected' : '' }}>
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
                                        {{ old('heure_fin_heure', isset($planning) ? \Carbon\Carbon::parse($planning->heure_fin)->format('H') : (request()->heure_fin ? substr(request()->heure_fin, 0, 2) : '')) == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                            <span class="mx-2">:</span>
                            <select id="heure_fin_minute" name="heure_fin_minute"
                                class="form-control @error('heure_fin_minute') is-invalid @enderror" required>
                                <option value="0"
                                    {{ old('heure_fin_minute', isset($planning) ? \Carbon\Carbon::parse($planning->heure_fin)->format('i') : (request()->heure_fin ? substr(request()->heure_fin, 3, 2) : '00')) == '00' ? 'selected' : '' }}>
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
                            class="btn btn-primary">{{ isset($planning) ? 'Mettre à jour' : 'Créer la tâche' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
