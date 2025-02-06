<x-app-layout>
    <x-slot:title>Détails des heures</x-slot:title>

    <div class="container py-5">
        <div class="mb-4">
            <a href="{{ url()->previous() }}" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-left me-2"></i> Retour
            </a>
        </div>

        <div class="card shadow-lg border-0">
            <div class="card-header bg-white py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="display-6 mb-0">Tâches en attente de validation pour {{ $user->identity }}</h1>
                    @if (!$plannings->isEmpty())
                        <span class="badge bg-primary fs-6">{{ $plannings->count() }} tâche(s)</span>
                    @endif
                </div>
                @if (Auth::user()->isA('admin'))
                  @if (isset($salaries) && $salaries->isNotEmpty())
                    <div class="mb-4 mt-4 align-items-center justify-content-between">
                      <x-inputs.input-select2 property="salarie_id" name="salarie_id" :entity="(object) ['salarie_id' => $user->id ?? null]"
                          label="Sélectionner un salarié" :values="$salaries" itemValue="id"
                          itemLabel="identity" :required="false" class="form-select form-select-lg" />
                    </div>
                  @else
                    <div class="alert alert-info text-center">
                        Aucun salarié disponible
                    </div>
                  @endif
                @endif
            </div>

            <div class="card-body">
                @if ($plannings->isEmpty())
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>Aucune tâche en attente de validation.</div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-uppercase fw-bold">Nom</th>
                                    <th class="text-uppercase fw-bold">Lieu</th>
                                    <th class="text-uppercase fw-bold">Date/heure</th>
                                    <th class="text-uppercase fw-bold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($plannings as $planning)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $planning->nom }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $planning->lieu->nom }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{{ $planning->plannifier_le }}</span>
                                                <small class="text-muted">
                                                    {{ $planning->heure_debut }} - {{ $planning->heure_fin }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <form action="{{ route('archive.archivate', $planning->id) }}"
                                                    method="POST" class="me-2">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fas fa-check me-2"></i>Valider
                                                    </button>
                                                </form>

                                                <a href="{{ route('planning.edit', $planning->id) }}"
                                                    class="btn btn-primary">
                                                    <i class="fas fa-edit me-2"></i>Modifier
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-4">
                            {{ $plannings->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function changeSalarie(salarie_id) {
            console.log('Selected value:', salarie_id);
            window.location.href = `/synthese/validation/${salarie_id}`;
        }
    </script>
</x-app-layout>
