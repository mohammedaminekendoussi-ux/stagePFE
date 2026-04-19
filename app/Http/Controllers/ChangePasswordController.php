<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ChangePasswordController extends Controller
{
    public function showForm()
    {
        // Si l'utilisateur a déjà changé son mot de passe, rediriger vers son dashboard
        if (Auth::user()->password_changed_at !== null) {
            return redirect()->intended('/');
        }
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->mot_de_passe = Hash::make($request->password);
        $user->password_changed_at = now();
        $user->save();

        // Rediriger vers le tableau de bord approprié
        return redirect()->route($this->redirectByRole($user));
    }

    private function redirectByRole($user)
    {
        return match ($user->role) {
            'administrateur' => 'admin.dashboard',
            'directeur'      => 'directeur.dashboard',
            'formateur'      => 'formateur.planning.index',
            'etudiant'       => 'etudiant.dashboard',
            default          => '/',
        };
    }
}