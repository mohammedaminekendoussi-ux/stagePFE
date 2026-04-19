<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Groupe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Liste des utilisateurs avec recherche
    public function index(Request $request)
    {
        $query = User::query();

        // Recherche par nom ou role
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('prenom', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filtre par role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('nom')->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    // Formulaire création
    public function create()
    {
        $groupes = Groupe::all();
        return view('admin.users.create', compact('groupes'));
    }

    // Sauvegarder nouvel utilisateur
    public function store(Request $request)
    {
        $request->validate([
            'nom'        => 'required|string|max:255',
            'prenom'     => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'role' => 'required|in:administrateur,directeur,formateur,etudiant',
            'mot_de_passe' => 'required|string|min:6',
            'groupe_id'  => 'nullable|exists:groupes,id',
        ]);

        User::create([
            'nom'                => $request->nom,
            'prenom'             => $request->prenom,
            'email'              => $request->email,
            'role'               => $request->role,
            'mot_de_passe'       => Hash::make($request->mot_de_passe),
            'groupe_id'          => $request->role === 'etudiant' ? $request->groupe_id : null,
            'actif'              => true,
            'tentative_echec'    => 0,
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Utilisateur créé avec succès !');
    }

    // Formulaire modification
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $groupes = Groupe::all();
        return view('admin.users.edit', compact('user', 'groupes'));
    }

    // Sauvegarder modification
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:administrateur,directeur,formateur,etudiant',
            'groupe_id' => 'nullable|exists:groupes,id',
        ]);

        $user->update([
    'nom'       => $request->nom,
    'prenom'    => $request->prenom,
    'email'     => $request->email,
    'role'      => $request->role,
    'groupe_id' => $request->role === 'etudiant' ? $request->groupe_id : null,
    'actif'     => $request->has('actif') ? true : false,
]);

        // Changer mot de passe si fourni
        if ($request->filled('mot_de_passe')) {
            $user->update([
                'mot_de_passe' => Hash::make($request->mot_de_passe)
            ]);
        }

        return redirect()->route('admin.users.index')
                         ->with('success', 'Utilisateur modifié avec succès !');
    }

    // Désactiver / Activer
    public function toggleActif($id)
    {
        $user = User::findOrFail($id);
        $user->update(['actif' => !$user->actif]);

        $message = $user->actif ? 'Utilisateur activé.' : 'Utilisateur désactivé.';
        return redirect()->route('admin.users.index')->with('success', $message);
    }

    // Supprimer
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'Utilisateur supprimé avec succès !');
    }
    public function show($id)
{
    $user = User::with('groupe.filiere')->findOrFail($id);
    // Vous pouvez créer une vue simple pour afficher les détails de l'étudiant
    return view('admin.users.show', compact('user'));
}
}