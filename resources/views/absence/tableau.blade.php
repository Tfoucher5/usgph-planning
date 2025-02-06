<x-app-layout>
    <x-slot:title>Tableau des Absences</x-slot:title>

    <div class="container-fluid py-5 bg-light">
        <!-- Cards de statistiques -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                <i class="fas fa-calendar-alt text-primary fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Congés annuels restants</h6>
                                <h3 class="mb-0 ">{{ 25 - number_format($nbConges, 2) }} j</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                                <i class="fas fa-bed text-danger fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Congés maladie</h6>
                                <h3 class="mb-0">{{ $joursParMotif['Congé Maladie'] ?? 0 }} j</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="fas fa-couch text-success fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Repos compensateur</h6>
                                <h3 class="mb-0">{{ $joursParMotif['Repos Compensateur'] ?? 0 }} j</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                <i class="fas fa-star text-warning fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Congé exceptionnel</h6>
                                <h3 class="mb-0">{{ $joursParMotif['Congé Exceptionnel'] ?? 0 }} j</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau principal -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-4">
                <div class="row align-items-center mb-3">
                    <div class="col">
                        <h4 class="mb-0">Tableau des Absences de :</h4>
                    </div>
                    <div class="col-2">
                      <h4 class="mb-0 align-items-right">Filtre :</h4>
                  </div>
                </div>
                <div class="col">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <!-- Select du salarié autonome -->
                            @if (isset($salaries) && $salaries->isNotEmpty())
                                <div class="mb-0">
                                    <x-inputs.input-select2 property="salarie_id" name="salarie_id" :entity="(object) ['salarie_id' => $user->id ?? null]"
                                        label="Salarié" :values="$salaries" itemValue="id" itemLabel="identity"
                                        :required="false" class="form-select-sm" onchange="changeSalarie(this.value)" />
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <!-- Formulaire pour le filtre par année -->
                            <form method="GET" class="d-flex justify-content-end align-items-center gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label mb-0">Période:</label>
                                    <select name="annee" class="form-select form-select-sm" style="min-width: 200px;">
                                        @foreach ($annees as $annee)
                                            <option value="{{ $annee['value'] }}"
                                                {{ request('annee') == $annee['value'] ? 'selected' : '' }}>
                                                {{ $annee['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm px-4">
                                    <i class="fas fa-filter me-2"></i>Filtrer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Légende -->
            <div class="card-body border-bottom bg-light py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <span class="badge bg-absence-congé-annuel me-2">CA</span>
                        <small class="text-muted">Congé Annuel</small>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-absence-repos-compensateur me-2">RC</span>
                        <small class="text-muted">Repos Compensateur</small>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-absence-congé-exceptionnel me-2">CE</span>
                        <small class="text-muted">Congé Exceptionnel</small>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-absence-congé-maladie me-2">CM</span>
                        <small class="text-muted">Congé Maladie</small>
                    </div>
                    <div class="col-auto">
                        <span class="badge hachure text-dark me-2">EA</span>
                        <small class="text-muted">En attente de validation</small>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center py-3" style="width: 200px;">Semaine</th>
                            <th class="text-center">Lundi</th>
                            <th class="text-center">Mardi</th>
                            <th class="text-center">Mercredi</th>
                            <th class="text-center">Jeudi</th>
                            <th class="text-center">Vendredi</th>
                            <th class="text-center">Samedi</th>
                            <th class="text-center">Dimanche</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tableauData as $year => $weeks)
                            @foreach ($weeks as $weekNumber => $weekData)
                                <tr>
                                    <td class="fw-medium text-center">
                                        Semaine du {{ \Carbon\Carbon::parse($weekData['week_start'])->format('d/m/Y') }}
                                    </td>
                                    @foreach ($weekData['days'] as $dayNumber => $absences)
                                        @php
                                            $bgClass = '';
                                            $textClass = '';
                                            $displayValue = '';

                                            if (!empty($absences)) {
                                                $motif = $absences[0]['motif'] ?? null;
                                                $status = $absences[0]['statut'] ?? null;

                                                switch ($motif) {
                                                    case 'Congé Annuel':
                                                        $bgClass = 'bg-absence-congé-annuel';
                                                        $displayValue = 'CA';
                                                        break;
                                                    case 'Repos Compensateur':
                                                        $bgClass = 'bg-absence-repos-compensateur';
                                                        $displayValue = 'RC';
                                                        break;
                                                    case 'Congé Exceptionnel':
                                                        $bgClass = 'bg-absence-congé-exceptionnel';
                                                        $displayValue = 'CE';
                                                        break;
                                                    case 'Congé Maladie':
                                                        $bgClass = 'bg-absence-congé-maladie';
                                                        $displayValue = 'CM';
                                                        break;
                                                }

                                                if ($status && $status->value === 'en attente') {
                                                    $bgClass .= ' hachure';
                                                }
                                            }
                                        @endphp
                                        <td class="text-center {{ $bgClass }}" style="height: 45px;">
                                            <span class="fw-medium">{{ $displayValue }}</span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function changeSalarie(salarie_id) {
            window.location.href = `/absence/tableau/${salarie_id}`;
        }
    </script>
</x-app-layout>
