<x-app-layout>
    <x-slot:title>Liste des Absences</x-slot:title>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Liste des Absences</h2>
            <div class="W-200, col-30 col-md-6 d-flex justify-content-center align-items-center">
                @if (Auth::user()->isA('admin'))
                    @if (isset($salaries) && $salaries->isNotEmpty())
                        <x-inputs.input-select2 property="salarie_id" name="salarie_id" :entity="(object) ['salarie_id' => $currentUserId ?? null]" label="Salarié"
                            :values="$salaries" itemValue="id" itemLabel="identity" :required="false" />
                    @else
                        <p class="text-muted text-center">Aucun salarié disponible.</p>
                    @endif
                    <div class="p-2">
                        <x-grid.button-action ability="absence-retrieve" url="{{ route('absence.index') }}"
                            titre="Voir toutes les absences" icone="fas fa-sync" couleur="danger" />
                    </div>
                    <div class="p-2">
                        <a href="{{ route('absence.tableau', ['salarie_id', null]) }}" class="btn btn-success text-white">
                            Voir tableau
                        </a>
                    </div>
                @endif
                @can('absence-create')
                    <a href="{{ route('absence.create') }}" class="btn btn-primary text-white">
                        <i class="fas fa-plus text-white" aria-hidden="true"></i> Déclarer une absence
                    </a>
                @endcan
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="absencesTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                @if (Auth::user()->isA('admin'))
                                    <th>Employé</th>
                                @endif
                                <th>Motif</th>
                                <th>Date de début</th>
                                <th>Date de fin</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absences as $absence)
                                <tr>
                                    @if (Auth::user()->isA('admin'))
                                        <td>{{ $absence->user->first_name . ' ' . $absence->user->last_name }}</td>
                                    @endif
                                    <td>{{ $absence->motif->nom }}</td>
                                    <td>{{ \Carbon\Carbon::parse($absence->date_debut)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($absence->date_fin)->format('d/m/Y') }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $absence->status === \App\Enums\ValidationStatus::VALIDATED
                                                ? 'success'
                                                : ($absence->status === \App\Enums\ValidationStatus::WAITING
                                                    ? 'secondary'
                                                    : 'danger') }}">
                                            {{ ucfirst($absence->status->value) }}
                                        </span>
                                    </td>
                                    <td class="d-flex">
                                        @can('absence-update')
                                            @if ($absence->status === \App\Enums\ValidationStatus::WAITING)
                                                <x-grid.button-action ability="absence-update"
                                                    url="{{ route('absence.edit', $absence->id) }}" titre="Modifier"
                                                    icone="fas fa-edit" couleur="warning" />
                                            @endif
                                        @endcan

                                        @can('absence-delete')
                                            @if ($absence->status === \App\Enums\ValidationStatus::WAITING)
                                                <form method="POST" action="{{ route('absence.destroy', $absence->id) }}"
                                                    class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-outline-danger btn-inline"
                                                        data-bs-toggle="tooltip" title="Supprimer"
                                                        onclick="confirmSuppression(event, this)">
                                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                        @if ($absence->status === \App\Enums\ValidationStatus::WAITING)
                                            @can('absence-confirm')
                                                <x-grid.button-action ability="absence-confirm"
                                                    url="{{ route('absence.confirm', $absence->id) }}" titre="Valider"
                                                    icone="fa fa-check" couleur="success" />
                                            @endcan

                                            @can('absence-refuse')
                                                <x-grid.button-action ability="absence-refuse"
                                                    url="{{ route('absence.refuse', $absence->id) }}" titre="Refuser"
                                                    icone="fas fa-xmark" couleur="danger" />
                                            @endcan
                                        @else
                                            <div style="height: 35px; visibility: hidden;">&nbsp;</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->isA('admin') ? 6 : 5 }}" class="text-center">
                                        Aucune absence trouvée.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="mt-3">
                    {{ $absences->links() }}
                </div>
            </div>
            <div class="col">
                @can('absence-delete')
                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('absence.corbeille') }}" class="btn btn-outline-danger">
                            <i class="fa fa-trash me-2" aria-hidden="true"></i> {{ __('Voir la corbeille') }}
                        </a>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    <script>
        function confirmSuppression(event, button) {
            event.preventDefault();

            Swal.fire({
                title: 'Êtes-vous sûr de vouloir supprimer cette absence ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }

        function changeSalarie(salarieId) {
            console.log('Selected value:', salarieId); // Changed 'value' to 'salarieId'
            window.location.href = `/absence/salarie/${salarieId}`;
        }
    </script>
</x-app-layout>
