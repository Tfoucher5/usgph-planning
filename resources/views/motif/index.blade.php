<x-app-layout>
  <x-slot:title>Liste des Motifs</x-slot:title>

  <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="mb-0">Liste des Motifs</h2>
          @can('motif-create')
              <a href="{{ route('motif.create') }}" class="btn btn-primary text-white">
                  <i class="fas fa-plus text-white" aria-hidden="true"></i> Ajouter un motif
              </a>
          @endcan
      </div>

      <div class="card shadow">
          <div class="card-body">
              <div class="table-responsive">
                  <table id="MotifsTable" class="table table-striped table-hover">
                      <thead>
                          <tr>
                              <th>Nom</th>
                              <th>Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          @forelse($motifs as $motif)
                              <tr>
                                  <td>{{ $motif->nom }}</td>
                                  <td class="d-flex">
                                      <!-- Edit Button -->
                                      <x-grid.button-action ability="motif-update"
                                          url="{{ route('motif.edit', $motif->id) }}" titre="Modifier"
                                          icone="fas fa-edit" couleur="warning" />

                                      <!-- Delete Button -->
                                      @can('motif-delete')
                                          <form method="POST" action="{{ route('motif.destroy', $motif->id) }}"
                                              class="d-inline delete-form">
                                              @csrf
                                              @method('DELETE')
                                              <button type="button" class="btn btn-outline-danger btn-inline"
                                                  data-bs-toggle="tooltip" title="Supprimer"
                                                  onclick="confirmSuppression(event, this)">
                                                  <i class="fa-regular fa-trash-can fa-fw" aria-hidden="true"></i>
                                              </button>
                                          </form>
                                      @else
                                          <x-grid.button-action ability="none" url="#"
                                              titre="Supprimer (désactivé)" icone="fa-regular fa-trash-can"
                                              couleur="dark" />
                                      @endcan
                                  </td>
                              </tr>
                          @empty
                              <tr>
                                  <td colspan="3" class="text-center">Aucun motif trouvé.</td>
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
                  {{ $motifs->links() }}
              </div>
          </div>
          <div class="col">

              @can('motif-delete')
                  <div class="d-flex justify-content-end mt-3">
                      <a href="{{ route('motif.corbeille') }}" class="btn btn-outline-danger">
                          <i class="fa fa-trash me-2" aria-hidden="true"></i> {{ __('Voir la corbeille') }}
                      </a>
                  </div>
              @endcan
          </div>
      </div>

      <script>
          function confirmSuppression(event, button) {
              event.preventDefault();

              Swal.fire({
                  title: 'Êtes-vous sûr de vouloir supprimer ce motif ?',
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
      </script>
</x-app-layout>
