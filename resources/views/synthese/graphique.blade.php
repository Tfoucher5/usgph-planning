<x-app-layout>
  <x-slot:title>Détails des heures</x-slot:title>
  <div class="container-fluid py-4">
      <div class="row">
          <div class="col-12">
              <div class="card shadow-sm">
                  <div class="card-header bg-white">
                      <div class="d-flex justify-content-between align-items-center">
                          <h5 class="mb-0">Graphique des heures travaillées</h5>
                      </div>
                  </div>
                  <div class="card-body">
                      @if(session('erreur'))
                          <div class="alert alert-danger alert-dismissible fade show" role="alert">
                              {{ session('erreur') }}
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>
                      @endif

                      <div class="chart-container" style="position: relative; height: 60vh; width: 100%;">
                          <canvas id="chart-{{ $employee['id'] }}"></canvas>
                      </div>
                  </div>
                  <div class="card-footer bg-white border-top">
                      <div class="row text-center">
                          <div class="col-md-4">
                              <div class="small text-muted">Total des heures</div>
                              <div class="h5 mb-0" id="totalHours">
                                  {{ array_sum(array_column($employee['workWeeks'], 'total')) }} heures
                              </div>
                          </div>
                          <div class="col-md-4">
                              <div class="small text-muted">Moyenne hebdomadaire</div>
                              <div class="h5 mb-0" id="avgHours">
                                  {{ number_format(array_sum(array_column($employee['workWeeks'], 'total')) / count($employee['workWeeks']), 1) }} heures
                              </div>
                          </div>
                          <div class="col-md-4">
                              <div class="small text-muted">Semaines travaillées</div>
                              <div class="h5 mb-0">
                                  {{ count($employee['workWeeks']) }} semaines
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Tableau récapitulatif -->
      <div class="row mt-4">
          <div class="col-12">
              <div class="card shadow-sm">
                  <div class="card-header bg-white">
                      <h5 class="mb-0">Détail par semaine</h5>
                  </div>
                  <div class="card-body">
                      <div class="table-responsive">
                          <table class="table table-hover">
                              <thead>
                                  <tr>
                                      <th>Semaine</th>
                                      <th>Heures travaillées</th>
                                      <th>Différence</th>
                                      <th>Status</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  @foreach($employee['workWeeks'] as $index => $week)
                                      <tr>
                                          <td>Semaine {{ $index }}</td>
                                          <td>{{ $week['total'] }} heures</td>
                                          <td>
                                              @php
                                                  $diff = $week['total'] - 35;
                                                  $class = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted');
                                                  $symbol = $diff > 0 ? '+' : '';
                                              @endphp
                                              <span class="{{ $class }}">
                                                  {{ $symbol }}{{ number_format($diff, 1) }}
                                              </span>
                                          </td>
                                          <td>
                                              @if($week['total'] >= 35)
                                                  <span class="badge bg-success">Complet</span>
                                              @else
                                                  <span class="badge bg-warning text-dark">Incomplet</span>
                                              @endif
                                          </td>
                                      </tr>
                                  @endforeach
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
  <script>
    window.employee = @json($employee);
</script>

  @push('scripts')
      @vite('resources/js/hours-chart-year.js')
  @endpush
</x-app-layout>
