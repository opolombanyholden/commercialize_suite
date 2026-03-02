<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientsExport;
use App\Imports\ClientsImport;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:clients.view')->only(['index', 'show']);
        $this->middleware('permission:clients.create')->only(['create', 'store']);
        $this->middleware('permission:clients.edit')->only(['edit', 'update']);
        $this->middleware('permission:clients.delete')->only('destroy');
    }

    /**
     * Liste des clients
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $query = Client::where('company_id', $companyId);

        // Recherche
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filtre par type
        if ($type = $request->input('type')) {
            if ($type === 'individual') {
                $query->individuals();
            } elseif ($type === 'business') {
                $query->businesses();
            }
        }

        // Filtre par statut
        if ($request->has('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        // Tri
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $clients = $query->paginate(15)->withQueryString();

        // Statistiques
        $stats = [
            'total' => Client::where('company_id', $companyId)->count(),
            'individuals' => Client::where('company_id', $companyId)->individuals()->count(),
            'businesses' => Client::where('company_id', $companyId)->businesses()->count(),
        ];

        return view('clients.index', compact('clients', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create(): View
    {
        return view('clients.create');
    }

    /**
     * Enregistrer un nouveau client
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        $client = Client::create($data);

        // Redirection selon le contexte
        if ($request->boolean('return_json')) {
            return response()->json(['client' => $client]);
        }

        if ($request->filled('redirect_to')) {
            return redirect($request->redirect_to)
                ->with('success', 'Client créé avec succès.')
                ->with('client_id', $client->id);
        }

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Afficher un client
     */
    public function show(Request $request, Client $client): View
    {
        $this->authorizeCompany($request, $client);

        // Historique des documents
        $quotes = $client->quotes()->latest()->take(10)->get();
        $invoices = $client->invoices()->latest()->take(10)->get();

        // Statistiques
        $stats = [
            'total_quotes' => $client->quotes()->count(),
            'total_invoices' => $client->invoices()->count(),
            'total_spent' => $client->total_spent,
            'pending_amount' => $client->invoices()
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->sum('balance'),
        ];

        return view('clients.show', compact('client', 'quotes', 'invoices', 'stats'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Request $request, Client $client): View
    {
        $this->authorizeCompany($request, $client);

        return view('clients.edit', compact('client'));
    }

    /**
     * Mettre à jour un client
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorizeCompany($request, $client);

        $client->update($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Supprimer un client
     */
    public function destroy(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeCompany($request, $client);

        // Vérifier qu'il n'y a pas de documents liés
        if ($client->invoices()->exists()) {
            return back()->with('error', 'Impossible de supprimer un client avec des factures.');
        }

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client supprimé avec succès.');
    }

    /**
     * Activer/Désactiver un client
     */
    public function toggleStatus(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeCompany($request, $client);

        $client->update(['is_active' => !$client->is_active]);

        $status = $client->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Le client a été {$status}.");
    }

    /**
     * Exporter les clients en Excel
     */
    public function export(Request $request)
    {
        $companyId = $request->user()->company_id;

        return Excel::download(
            new ClientsExport($companyId),
            'clients-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Formulaire d'import
     */
    public function importForm(): View
    {
        return view('clients.import');
    }

    /**
     * Importer des clients depuis Excel
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        try {
            $import = new ClientsImport($request->user()->company_id);
            Excel::import($import, $request->file('file'));

            $count = $import->getRowCount();

            return redirect()
                ->route('clients.index')
                ->with('success', "{$count} client(s) importé(s) avec succès.");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Recherche AJAX pour autocomplete
     */
    public function search(Request $request)
    {
        $companyId = $request->user()->company_id;
        $search = $request->input('q', '');

        $clients = Client::where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->take(10)
            ->get(['id', 'name', 'company_name', 'email', 'type']);

        return response()->json($clients);
    }

    /**
     * Vérifier que le client appartient à l'entreprise
     */
    protected function authorizeCompany(Request $request, Client $client): void
    {
        if ($client->company_id !== $request->user()->company_id) {
            abort(403, 'Accès non autorisé à ce client.');
        }
    }
}
