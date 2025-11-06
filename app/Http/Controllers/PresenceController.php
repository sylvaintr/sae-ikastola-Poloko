<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Enfant;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    /**
     * Return all classes for the selector.
     */
    public function classes()
    {
        $classes = Classe::query()
            ->orderBy('nom')
            ->get(['idClasse', 'nom', 'niveau']);

        return response()->json($classes);
    }

    /**
     * Return students for a given class id.
     */
    public function students(Request $request)
    {
        $classId = (int) $request->query('classe_id');
        if (!$classId) {
            return response()->json([], 200);
        }

        $students = Enfant::query()
            ->where('idClasse', $classId)
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get(['idEnfant', 'prenom', 'nom']);

        return response()->json($students);
    }
}


