<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Company;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\TaxSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Afficher le formulaire d'inscription
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Traiter l'inscription
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // 1. Créer l'entreprise
            $company = Company::create([
                'name' => $request->company_name,
                'slug' => Str::slug($request->company_name),
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address ?? '',
                'city' => $request->city ?? 'Libreville',
                'country' => 'GA',
                'currency' => 'XAF',
                'timezone' => 'Africa/Libreville',
                'is_active' => true,
                'trial_ends_at' => now()->addDays(14), // 14 jours d'essai
            ]);

            // 2. Créer le site principal
            $site = Site::create([
                'company_id' => $company->id,
                'name' => 'Siège Principal',
                'code' => 'HQ',
                'is_headquarters' => true,
                'is_warehouse' => true,
                'is_store' => true,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address ?? '',
                'city' => $request->city ?? 'Libreville',
                'country' => 'GA',
                'is_active' => true,
            ]);

            // 3. Créer l'utilisateur administrateur
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_id' => $company->id,
                'version' => 'enterprise', // Version par défaut pour l'essai
                'phone' => $request->phone,
                'job_title' => 'Administrateur',
                'language' => 'fr',
                'timezone' => 'Africa/Libreville',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // 4. Assigner le rôle admin
            $user->assignRole('company_admin');

            // 5. Assigner l'accès au site
            $user->sites()->attach($site->id, ['is_primary' => true]);

            // 6. Créer les taxes par défaut (Gabon)
            TaxSeeder::createDefaultTaxes($company);

            DB::commit();

            // Connecter l'utilisateur
            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Bienvenue ! Votre compte a été créé avec succès. Vous disposez de 14 jours d\'essai gratuit.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.']);
        }
    }
}
