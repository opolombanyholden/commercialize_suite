<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Afficher le profil
     */
    public function show()
    {
        $user = auth()->user();
        $user->load(['company', 'sites', 'roles']);

        return view('profile.show', compact('user'));
    }

    /**
     * Formulaire de modification du profil
     */
    public function edit()
    {
        $user = auth()->user();

        return view('profile.edit', compact('user'));
    }

    /**
     * Mettre à jour le profil
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'language' => ['required', 'in:fr,en'],
            'timezone' => ['required', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $data = $request->only(['name', 'email', 'phone', 'job_title', 'language', 'timezone']);

        // Upload avatar
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')
                ->store('avatars/' . $user->id, 'public');
        }

        $user->update($data);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Formulaire de changement de mot de passe
     */
    public function editPassword()
    {
        return view('profile.password');
    }

    /**
     * Changer le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Mot de passe modifié avec succès.');
    }

    /**
     * Supprimer l'avatar
     */
    public function deleteAvatar()
    {
        $user = auth()->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return back()->with('success', 'Avatar supprimé.');
    }

    /**
     * Préférences utilisateur
     */
    public function preferences()
    {
        $user = auth()->user();

        return view('profile.preferences', compact('user'));
    }

    /**
     * Mettre à jour les préférences
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'preferences' => ['nullable', 'array'],
        ]);

        auth()->user()->update([
            'preferences' => $request->preferences ?? [],
        ]);

        return back()->with('success', 'Préférences mises à jour.');
    }
}
