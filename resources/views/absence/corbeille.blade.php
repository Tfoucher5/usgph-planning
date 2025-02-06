<x-app-layout>
  <x-slot:title>Corbeille des Absences</x-slot:title>

  <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="mb-0">Corbeille des Absences</h2>
          <a href="{{ route('absence.index') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left"></i> Retour à la liste
          </a>
      </div>
      @if (session('success'))
          <div class="alert alert-success">
              {{ session('success') }}
          </div>
      @endif

      <div class="card shadow">
          <div class="card-body">
              <table class="table table-striped table-hover">
                  <thead>
                      <tr>
                        <th>Motif</th>
                        <th>Date de début</th>
                        <th>Date de fin</th>
                        <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse($deletedAbsences as $absence)
                          <tr>
                              <td>{{ $absence->nom }}</td>
                              <td>{{ $absence->motif->nom }}</td>
                              <td>{{ \Carbon\Carbon::parse($absence->date_debut)->format('d/m/Y') }}</td>
                              <td>{{ \Carbon\Carbon::parse($absence->date_fin)->format('d/m/Y') }}</td>
                              <td>
                              <td>
                                  <button type="button" class="btn btn-outline-info btn-inline"
                                      data-bs-toggle="tooltip" title="Restaurer"
                                      onclick="confirmRestauration('{{ route('absence.undelete', $absence->id) }}')">
                                      <i class="fas fa-undo"></i> Restaurer
                                  </button>
                              </td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="3" class="text-center">Aucune absences trouvées dans la corbeille.</td>
                          </tr>
                      @endforelse
                  </tbody>
              </table>
          </div>
      </div>

      <div class="mt-3">
          {{ $deletedAbsences->links() }}
      </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
      function confirmRestauration(url) {
          Swal.fire({
              title: 'Êtes-vous sûr de vouloir restaurer ce absence ?',
              text: "L'élément sera restauré dans la liste des absences.",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#28a745',
              cancelButtonColor: '#6c757d',
              confirmButtonText: 'Oui, restaurer',
              cancelButtonText: 'Annuler'
          }).then((result) => {
              if (result.isConfirmed) {
                  window.location.href = url;
              }
          });
      }
  </script>
</x-app-layout>
