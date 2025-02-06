<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterRequest;
use App\Http\Services\Admin\SyntheseService;
use App\Http\Services\Conge\AbsenceService;
use App\Http\Services\Planning\PlanningService;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Session;

class SyntheseController extends Controller
{
    private const ABILITY = 'synthese';

    private const PATH_VIEWS = 'synthese';

    /**
     * @var SyntheseService
     */
    private $service;

    /**
     * @var PlanningService
     */
    private $planningService;

    /**
     * @var AbsenceService
     */
    private $absenceService;

    /**
     * Constructor
     *
     * @param  SyntheseService  $service
     */
    public function __construct(SyntheseService $service, AbsenceService $absenceService, PlanningService $planningService)
    {
        $this->middleware('auth');
        $this->service = $service;
        $this->absenceService = $absenceService;
        $this->planningService = $planningService;
        Session::put('level_menu_1', 'Synthèse');
        Session::put('level_menu_2', self::ABILITY);
    }

    /**
     * Summary of index
     *
     * @return mixed|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        Session::put('level_menu_2', self::ABILITY);

        /**
         * @var User
         */
        $currentUser = Auth::user();

        if ($currentUser->isA('admin')) {
            $employeeList = $this->service->getInfosUtilisateurs();

            $anneeEnCours = Carbon::now();
            $troisMoisAvant = Carbon::now()->subMonths(3);

            foreach ($employeeList as &$employee) {
                $heuresParSemaine = $this->service->getHeuresParSemaine([], $employee['id'], $anneeEnCours)->toArray();

                // Filtrer pour ne garder que les 3 derniers mois
                $employee['workWeeks'] = collect($heuresParSemaine)->filter(function ($semaine) use ($troisMoisAvant) {
                    $debutSemaine = Carbon::now()
                        ->setISODate($semaine['annee'], $semaine['semaine'])
                        ->startOfWeek();

                    return $debutSemaine->isAfter($troisMoisAvant);
                })->toArray();
            }

            return view(self::PATH_VIEWS . '.index', [
                'employees' => $employeeList,
            ]);
        }

        Session::put('erreur', 'Vous n\'avez pas l\'autorisation d\'accéder à cette page.');

        return redirect()->back();
    }

    /**
     * Summary of show
     *
     * @param  mixed  $id
     * @param  \App\Http\Requests\Admin\FilterRequest  $request
     *
     * @return mixed|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id, FilterRequest $request)
    {
        if (auth()->user()->isA('admin')) {
            $data = $request->all();

            $user = User::where('id', $id)->first();

            if (! $user) {
                abort(404, 'Utilisateur non trouvé.');
            }
            $semaines = $this->service->getHeuresParSemaine($data, $user->id, null);
            if (isset($data['annee'])) {
                $annee = Carbon::parse($data['annee']);
            } else {
                $annee = Carbon::now();
            }
            $nbAbsence = $this->absenceService->getCongesPayesOfUserForYear($user, $annee);
            $annees = $this->service->getAnneesDisponibles($user->id);

            return view(self::PATH_VIEWS . '.show', compact('semaines', 'annees', 'user', 'nbAbsence'));
        }
        Session::put('erreur', 'Vous n\'avez pas la permission d\'accéder à cette page.');

        return redirect()->back();
    }

    /**
     * Summary of export
     *
     * @param  mixed  $id
     * @param  \App\Http\Requests\Admin\FilterRequest  $request
     *
     * @return \Illuminate\Http\Response
     *
     * @codeCoverageIgnore
     */
    public function export($id, FilterRequest $request)
    {
        $data = $request->all();

        $user = User::where('id', $id)->first();

        $anneeEnCours = $request->input('annee')
            ? Carbon::parse($request->input('annee'))
            : Carbon::now();

        $semaines = $this->service->getHeuresParSemaine($data, $user->id, $anneeEnCours);

        $filename = 'heures_travaillees_' . now()->format('Y_m_d') . '.csv';

        // Créer le contenu du CSV
        $csvContent = $this->generateCsvContent($semaines);

        // Retourner une réponse pour télécharger le fichier
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    /**
     * Summary of generateCsvContent
     *
     * @param  mixed  $semaines
     *
     * @return bool|string
     *
     * @codeCoverageIgnore
     */
    private function generateCsvContent($semaines)
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new \Exception('Unable to open temporary file stream.');
        }

        fputcsv($handle, [
            'Année-Semaine',
            'Lundi',
            'Mardi',
            'Mercredi',
            'Jeudi',
            'Vendredi',
            'Samedi',
            'Dimanche',
            'Total',
            'Heures supp',
        ]);

        $sortedSemaines = $semaines->map(function ($item, $key) {
            return [
                'key' => $key,
                'annee' => $item['annee'],
                'semaine' => (int) str_replace('S', '', $key),
                'data' => $item,
            ];
        })
            ->sortByDesc('annee')
            ->sortBy([
                ['annee', 'desc'],
                ['semaine', 'desc'],
            ]);

        foreach ($sortedSemaines as $week) {
            $donnees = $week['data'];
            $key = $week['key'];

            fputcsv($handle, [
                $donnees['annee'] . '-' . $key,
                $this->formatJour($donnees['jours'][1]),
                $this->formatJour($donnees['jours'][2]),
                $this->formatJour($donnees['jours'][3]),
                $this->formatJour($donnees['jours'][4]),
                $this->formatJour($donnees['jours'][5]),
                $this->formatJour($donnees['jours'][6]),
                $this->formatJour($donnees['jours'][0]),
                number_format($donnees['total'], 2) . 'h',
                $donnees['heures_supp'] === 0 ? '0' : number_format($donnees['heures_supp'], 2),
            ]);
        }

        rewind($handle);

        $csvContent = stream_get_contents($handle);

        fclose($handle);

        return $csvContent;
    }

    /**
     * Summary of formatJour
     *
     * @param  mixed  $details
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    private function formatJour($details)
    {
        if (isset($details['absence'])) {
            switch ($details['absence']) {
                case 'Congé Annuel':
                    return 'CA';
                case 'Repos Compensateur':
                    return 'RC';
                case 'Congé Exceptionnel':
                    return 'CE';
                case 'Congé Maladie':
                    return 'CM';
            }
        }

        return isset($details['heures']) ? number_format($details['heures'], 2) : '';
    }

    /**
     * Summary of showGraphiqueyear
     * @param \App\Models\User $user
     * @return mixed|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showGraphiqueyear(User $user) {
        /**
         * @var User
         */
        $currentUser = Auth::user();

        if ($currentUser->isA('admin')) {
            $employe = User::where('id', $user->id)->first();

            $anneeEnCours = Carbon::now();
            $employee['workWeeks'] = $this->service->getHeuresParSemaine([], $employe->id, $anneeEnCours)->toArray();
            $employee['id'] = $employe->id;

            return view(self::PATH_VIEWS . '.graphique', [
                'employee' => $employee
            ]);
        }

        Session::put('erreur', 'Vous n\'avez pas l\'autorisation d\'accéder à cette page.');

        return redirect()->back();
    }

    /**
     * Summary of showTacheValidation
     * @param \App\Models\User $user
     * @return mixed|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showTacheValidation(User $user) {
        if (auth()->user()->isA('admin')) {
            $plannings = $this->service->getTacheToValidate($user);
            $salaries = $this->planningService->getSalaries();
            return view(self::PATH_VIEWS . '.tacheValidation', compact('plannings', 'user', 'salaries'));
        } else {
            Session::put('erreur', 'Vous n\'avez pas la permission d\'accéder à cette page.');
            return redirect()->back();
        }
    }

}
