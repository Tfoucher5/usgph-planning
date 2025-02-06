<x-app-layout>
  <x-slot:title>Corbeille des Motifs</x-slot:title>

  <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="mb-0">Corbeille des Motifs</h2>
          <a href="{{ route('motif.index') }}" class="btn btn-secondary">
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
                          <th>Nom</th>
                          <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse($deletedMotifs as $motif)
                          <tr>
                              <td>{{ $motif->nom }}</td>
                              <td>
                                  <button type="button" class="btn btn-outline-info btn-inline"
                                      data-bs-toggle="tooltip" title="Restaurer"
                                      onclick="confirmRestauration('{{ route('motif.undelete', $motif->id) }}')">
                                      <i class="fas fa-undo"></i> Restaurer
                                  </button>
                              </td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="3" class="text-center">Aucun motif trouvé dans la corbeille.</td>
                          </tr>
                      @endforelse
                  </tbody>
              </table>
          </div>
      </div>

      <div class="mt-3">
          {{ $deletedMotifs->links() }}
      </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
      function confirmRestauration(url) {
          Swal.fire({
              title: 'Êtes-vous sûr de vouloir restaurer ce motif ?',
              text: "L'élément sera restauré dans la liste des Motifs.",
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
