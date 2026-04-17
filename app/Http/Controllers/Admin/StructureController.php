<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    // ==================== FILIÈRES ====================

    public function filieres()
    {
        $filieres = Filiere::withCount(['groupes', 'modules'])->get();
        return view('admin.structure.index', compact('filieres'))->with('tab', 'filieres');
    }

    public function storeFiliere(Request $request)
    {
        $request->validate([
            'nom'         => 'required|string|max:255|unique:filieres,nom',
            'description' => 'nullable|string',
        ]);

        Filiere::create($request->only('nom', 'description'));

        return redirect()->route('admin.structure.filieres')
                         ->with('success', 'Filière créée avec succès !');
    }

    public function updateFiliere(Request $request, $id)
    {
        $filiere = Filiere::findOrFail($id);

        $request->validate([
            'nom'         => 'required|string|max:255|unique:filieres,nom,' . $id,
            'description' => 'nullable|string',
        ]);

        $filiere->update($request->only('nom', 'description'));

        return redirect()->route('admin.structure.filieres')
                         ->with('success', 'Filière modifiée avec succès !');
    }

    public function destroyFiliere($id)
    {
        Filiere::findOrFail($id)->delete();
        return redirect()->route('admin.structure.filieres')
                         ->with('success', 'Filière supprimée avec succès !');
    }

    // ==================== GROUPES ====================

    public function groupes(Request $request)
    {
        $query = Groupe::with('filiere');

        if ($request->filled('filiere_id')) {
            $query->where('filiere_id', $request->filiere_id);
        }

        $groupes  = $query->get();
        $filieres = Filiere::all();

        return view('admin.structure.index', compact('groupes', 'filieres'))
                    ->with('tab', 'groupes');
    }

    public function storeGroupe(Request $request)
    {
        $request->validate([
            'nom'        => 'required|string|max:255',
            'annee'      => 'required|integer|min:1|max:5',
            'filiere_id' => 'required|exists:filieres,id',
        ]);

        Groupe::create($request->only('nom', 'annee', 'filiere_id'));

        return redirect()->route('admin.structure.groupes')
                         ->with('success', 'Groupe créé avec succès !');
    }

    public function updateGroupe(Request $request, $id)
    {
        $groupe = Groupe::findOrFail($id);

        $request->validate([
            'nom'        => 'required|string|max:255',
            'annee'      => 'required|integer|min:1|max:5',
            'filiere_id' => 'required|exists:filieres,id',
        ]);

        $groupe->update($request->only('nom', 'annee', 'filiere_id'));

        return redirect()->route('admin.structure.groupes')
                         ->with('success', 'Groupe modifié avec succès !');
    }

    public function destroyGroupe($id)
    {
        Groupe::findOrFail($id)->delete();
        return redirect()->route('admin.structure.groupes')
                         ->with('success', 'Groupe supprimé avec succès !');
    }

    // ==================== MODULES ====================

    public function modules(Request $request)
    {
        $query = Module::with('filiere', 'formateur');

        if ($request->filled('filiere_id')) {
            $query->where('filiere_id', $request->filiere_id);
        }

        if ($request->filled('formateur_id')) {
            $query->where('formateur_id', $request->formateur_id);
        }

        $modules    = $query->get();
        $filieres   = Filiere::all();
        $formateurs = User::where('role', 'formateur')->where('actif', true)->get();

        return view('admin.structure.index', compact('modules', 'filieres', 'formateurs'))
                    ->with('tab', 'modules');
    }

    public function storeModule(Request $request)
    {
        $request->validate([
            'nom'           => 'required|string|max:255',
            'coefficient'   => 'required|numeric|min:0.5|max:10',
            'volume_horaire'=> 'required|integer|min:1',
            'filiere_id'    => 'required|exists:filieres,id',
            'formateur_id'  => 'required|exists:users,id',
        ]);

        Module::create($request->only('nom', 'coefficient', 'volume_horaire', 'filiere_id', 'formateur_id'));

        return redirect()->route('admin.structure.modules')
                         ->with('success', 'Module créé avec succès !');
    }

    public function updateModule(Request $request, $id)
    {
        $module = Module::findOrFail($id);

        $request->validate([
            'nom'           => 'required|string|max:255',
            'coefficient'   => 'required|numeric|min:0.5|max:10',
            'volume_horaire'=> 'required|integer|min:1',
            'filiere_id'    => 'required|exists:filieres,id',
            'formateur_id'  => 'required|exists:users,id',
        ]);

        $module->update($request->only('nom', 'coefficient', 'volume_horaire', 'filiere_id', 'formateur_id'));

        return redirect()->route('admin.structure.modules')
                         ->with('success', 'Module modifié avec succès !');
    }

    public function destroyModule($id)
    {
        Module::findOrFail($id)->delete();
        return redirect()->route('admin.structure.modules')
                         ->with('success', 'Module supprimé avec succès !');
    }
}