<x-app-layout>
    <x-slot:title>Détails des heures</x-slot:title>
    <div class="container mt-4">
        <div class="row">
            @foreach ($employees as $employee)
                <div class="col-12" data-user-id="{{ $employee['id'] }}">
                    <div class="card shadow-sm border-light mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">{{ $employee['name'] }}</h5>
                            <p class="card-text mb-0">{{ $employee['email'] }}</p>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 400px; width: 100%;">
                                <canvas id="chart-{{ $employee['id'] }}"></canvas>
                            </div>
                            <div class="text-center mt-4">
                                <a href="{{ route('synthese.show', $employee['id']) }}"
                                    class="btn btn-primary text-white">Voir le tableau</a>
                                <a href="{{ route('synthese.graphique', $employee['id']) }}"
                                    class="btn btn-primary text-white">Voir le graphique pour l'année en cours</a>
                                <a href="{{ route('synthese.tacheValidation', $employee['id']) }}"
                                    class="btn btn-primary text-white">Voir les tâches à valider</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <script>
        window.employees = @json($employees);
    </script>
    @push('scripts')
        @vite('resources/js/hours-chart.js')
    @endpush
</x-app-layout>
