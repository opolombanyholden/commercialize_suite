<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;
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
        $this->middleware('permission:clients.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Client::where('company_id', auth()->user()->company_id);

        // Recherche
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par statut
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        // Tri
        $sortField = $request->input('sort', 'name');
        $sortDirection = $request->input('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $clients = $query->paginate(20)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(ClientRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        $client = Client::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'client' => $client,
                'message' => 'Client créé avec succès.',
            ]);
        }

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client créé avec succès.');
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load(['quotes' => function ($q) {
            $q->latest()->limit(5);
        }, 'invoices' => function ($q) {
            $q->latest()->limit(5);
        }]);

        // Statistiques
        $stats = [
            'total_quotes' => $client->quotes()->count(),
            'total_invoices' => $client->invoices()->count(),
            'total_spent' => $client->total_spent,
            'unpaid_amount' => $client->invoices()->pending()->sum('balance'),
        ];

        return view('clients.show', compact('client', 'stats'));
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        return view('clients.edit', compact('client'));
    }

    public function update(ClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $client->update($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client mis à jour avec succès.');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        // Vérifier s'il y a des factures non payées
        if ($client->invoices()->pending()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce client car il a des factures non payées.');
        }

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client supprimé avec succès.');
    }

    /**
     * Recherche AJAX pour autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        $clients = Client::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('company_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'type', 'name', 'company_name', 'email', 'phone', 'address', 'city']);

        return response()->json($clients);
    }

    /**
     * Export Excel
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        return Excel::download(
            new ClientsExport(auth()->user()->company_id),
            'clients-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Import Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        try {
            Excel::import(
                new ClientsImport(auth()->user()->company_id),
                $request->file('file')
            );

            return back()->with('success', 'Clients importés avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Toggle statut actif
     */
    public function toggleActive(Client $client)
    {
        $this->authorize('update', $client);

        $client->update(['is_active' => !$client->is_active]);

        return back()->with('success', 'Statut mis à jour.');
    }
}
