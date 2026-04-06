<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Afficher le formulaire de connexion
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Traiter la connexion
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $ip = $request->ip();
        $tentativesKey = 'login_attempts_' . $ip;

        // Vérifier si l'IP est bloquée
        if (Cache::has($tentativesKey) && Cache::get($tentativesKey) >= 5) {
    $blockedUntil = Cache::get($tentativesKey . '_blocked');
    if ($blockedUntil && now()->lt($blockedUntil)) {
        $secondsLeft = now()->diffInSeconds($blockedUntil);
        return back()->withErrors([
            'email' => 'Trop de tentatives. Réessayez dans ' . ceil($secondsLeft) . ' secondes.',
        ])->with('blocked_until', $blockedUntil->timestamp)->onlyInput('email');
    } else {
        Cache::forget($tentativesKey);
        Cache::forget($tentativesKey . '_blocked');
    }
}

        // Chercher l'utilisateur par email
        $user = User::where('email', $request->email)->first();

        // Vérifier identifiants
        if (!$user || !Hash::check($request->password, $user->mot_de_passe)) {
            // Incrémenter le compteur d'échecs
            $attempts = Cache::get($tentativesKey, 0) + 1;
            Cache::put($tentativesKey, $attempts, now()->addMinutes(10));

            if ($attempts >= 5) {
                Cache::put($tentativesKey . '_blocked', now()->addMinute(), now()->addMinute());
            }

            return back()->withErrors([
                'email' => 'Identifiants incorrects.',
            ])->onlyInput('email');
        }

        // Vérifier si le compte est actif
        if (!$user->actif) {
            return back()->withErrors([
                'email' => 'Ce compte est désactivé. Contactez l\'administrateur.',
            ])->onlyInput('email');
        }

        // Connexion réussie : nettoyer le cache des tentatives
        Cache::forget($tentativesKey);
        Cache::forget($tentativesKey . '_blocked');

        // Mettre à jour les tentatives dans la base (optionnel)
        $user->update(['tentative_echec' => 0]);

        // Connecter l'utilisateur manuellement
        Auth::login($user);

        // Rediriger selon le rôle
        return $this->redirectByRole($user);
    }

    // Déconnexion
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // Redirection après login
    private function redirectByRole($user)
    {
        return match ($user->role) {
            'administrateur' => redirect()->route('admin.dashboard'),
            'directeur'      => redirect()->route('directeur.dashboard'),
            'formateur'      => redirect()->route('formateur.planning.index'),
            'etudiant'       => redirect()->route('etudiant.planning.index'),
            default          => redirect('/'),
        };
    }
}