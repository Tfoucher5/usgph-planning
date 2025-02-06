<x-app-layout>
    <x-slot:title>
        Détail heures de travail
    </x-slot:title>

    <div class="card">
        <div class="card-header">
            <h3>Heures travaillées par semaine</h3>
        </div>
        <div class="card-body">
            <!-- Affichage des erreurs globales -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="GET" class="mb-4">

                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <h6 class="text-muted">Total des heures sur la période :</h6>
                        <p class="h5 text-success">
                            <strong>{{ number_format($semaines->sum('total'), 2) }} h / 1582 h</strong>
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-muted">Total des heures supplémentaires :</h6>
                        <p class="h5 text-warning">
                            <strong>{{ number_format($semaines->sum('heures_supp'), 2) }} h</strong>
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-muted">Jours de congés annuel restants :</h6>
                        <p class="h5 text-info">
                            <strong>{{ 25 - number_format($nbAbsence, 2) }} j</strong>
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="annee">Période</label>
                            <select name="annee" id="annee" class="form-control">
                                <option value="{{\Carbon\Carbon::now()}}">Sélectionnez une année</option>
                                @foreach ($annees as $annee)
                                    <option value="{{ $annee['value'] }}"
                                        {{ request('annee') == $annee['value'] ? 'selected' : '' }}>
                                        {{ $annee['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="mois">Mois</label>
                            <select name="mois" id="mois"
                                class="form-control @error('mois') is-invalid @enderror">
                                <option value="">Tous les mois</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('mois') == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->locale('fr')->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                            @error('mois')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="semaine">Semaine</label>
                            <input type="number" name="semaine" id="semaine" min="1" max="53"
                                class="form-control @error('semaine') is-invalid @enderror"
                                value="{{ request('semaine') }}" placeholder="Numéro de semaine">
                            @error('semaine')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end mt-3">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary text-white">Filtrer</button>
                            <a href="{{ route('synthese.show', $user->id) }}"
                                class="btn btn-secondary ml-2">Réinitialiser</a>
                            <a href="{{ route('export.csv', $user->id) }}" class="btn btn-success">
                                Exporter le tableau
                            </a>
                        </div>
                    </div>
                </div>
            </form>
            <div class="mb-4 d-flex align-items-center flex-wrap">
                <h6 class="me-3">Légende:</h6>
                <div class="me-3">
                    <span class="badge bg-ferie text-white">Jour férié</span>
                </div>
                <div class="me-3">
                    <span class="badge bg-repos text-white">Jour de repos</span>
                </div>
                <div class="me-3">
                    <span class="badge bg-absence-congé-annuel text-white">Congé Annuel (CA)</span>
                </div>
                <div class="me-3">
                    <span class="badge bg-absence-repos-compensateur text-white">Repos Compensateur (RC)</span>
                </div>
                <div class="me-3">
                    <span class="badge bg-absence-congé-exceptionnel text-white">Congé Exceptionnel (CE)</span>
                </div>
                <div class="me-3">
                    <span class="badge bg-absence-congé-maladie text-white">Congé Maladie (CM)</span>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Année-Semaine</th>
                            <th>Lundi</th>
                            <th>Mardi</th>
                            <th>Mercredi</th>
                            <th>Jeudi</th>
                            <th>Vendredi</th>
                            <th>Samedi</th>
                            <th>Dimanche</th>
                            <th>Total</th>
                            <th>Heures supp'</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($semaines as $key => $donnees)
                            <tr>
                                <td>{{ $donnees['annee'] }}-S{{ $donnees['semaine'] }}</td>
                                @foreach ($donnees['jours'] as $jour => $details)
                                    <td
                                        class="
                                  {{ isset($details['ferie']) && $details['ferie'] === true ? 'bg-ferie text-white' : '' }}
                                  {{ isset($details['repos']) && $details['repos'] === true ? 'bg-repos text-white' : '' }}
                                  {{ isset($details['absence']) ? 'bg-absence-' . strtolower(str_replace(' ', '-', $details['absence'])) . ' text-white' : '' }}">
                                        @php
                                            $displayValue = '';
                                            if (isset($details['absence'])) {
                                                switch ($details['absence']) {
                                                    case 'Congé Annuel':
                                                        $displayValue = 'CA';
                                                        break;
                                                    case 'Repos Compensateur':
                                                        $displayValue = 'RC';
                                                        break;
                                                    case 'Congé Exceptionnel':
                                                        $displayValue = 'CE';
                                                        break;
                                                    case 'Congé Maladie':
                                                        $displayValue = 'CM';
                                                        break;
                                                }
                                            } else {
                                                $displayValue = number_format($details['heures'], 2);
                                            }
                                        @endphp
                                        {{ $displayValue }}
                                    </td>
                                @endforeach
                                <td><strong>{{ number_format($donnees['total'], 2) }}h</strong></td>
                                <td>{{ $donnees['heures_supp'] == 0 ? '0' : number_format($donnees['heures_supp'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Aucune donnée disponible</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
