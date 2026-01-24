<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationSetting;
use App\Models\Evenement;
use App\Models\DocumentObligatoire;

class NotificationController extends Controller
{
    // Affiche la liste
    public function index()
    {
        $settings = NotificationSetting::with('target')->latest()->get();
        return view('admin.notifications.index', compact('settings'));
    }

    // Affiche le formulaire
    public function create()
    {
        // On charge les données pour les listes déroulantes (sélecteur JS)
        $evenements = Evenement::all(); 
        $documents = DocumentObligatoire::all();
        
        return view('admin.notifications.create', compact('evenements', 'documents'));
    }

    // Enregistre la notification
    public function store(Request $request)
    {
        // 1. Tes règles de validation
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'module_id' => 'required|integer',
            'module_type' => 'required|string', // "Document" ou "Evènement"
            'recurrence_days' => 'nullable|integer',
            'reminder_days' => 'nullable|integer',
            'description' => 'nullable|string', // J'ajoute description car elle est dans ton formulaire
            'is_active' => 'nullable', // Pour le switch
        ]);

        // 2. Conversion du type "String" en "Classe Laravel"
        // C'est indispensable pour le polymorphisme
        $targetClass = null;

        if ($request->module_type === 'Document') {
            $targetClass = \App\Models\DocumentObligatoire::class;
        } elseif ($request->module_type === 'Evènement') {
            $targetClass = \App\Models\Evenement::class;
        }

        // Sécurité : si le type est inconnu
        if (!$targetClass) {
            return back()->withErrors(['module_type' => 'Type de module invalide.']);
        }

        // 3. Création en base de données
        NotificationSetting::create([
            'title' => $request->title,
            'description' => $request->description,
            'recurrence_days' => $request->recurrence_days,
            'reminder_days' => $request->reminder_days,
            'is_active' => $request->has('is_active'), // Checkbox cochée = true
            
            // Les colonnes magiques pour le lien
            'target_id' => $request->module_id,
            'target_type' => $targetClass,
        ]);

        // 4. Redirection
        return redirect()->route('admin.notifications.index')
                         ->with('success', 'Notification ajoutée avec succès !');
    }

    public function edit($id)
{
    // 1. On trouve la règle
    $setting = \App\Models\NotificationSetting::findOrFail($id);

    // 2. On l'envoie à la vue 'edit'
    return view('admin.notifications.edit', compact('setting'));
}

public function update(Request $request, $id)
{
    // Validation...
    $setting = \App\Models\NotificationSetting::findOrFail($id);
    
    // Mise à jour (Simplifiée)
    $setting->update([
        'title' => $request->title,
        'description' => $request->description,
        'recurrence_days' => $request->recurrence_days,
        'reminder_days' => $request->reminder_days,
        'is_active' => $request->has('is_active'), // Checkbox handling
        // On ne met à jour le module que si l'utilisateur en a choisi un nouveau
        'target_type' => $request->module_type == 'Document' ? 'App\Models\DocumentObligatoire' : 'App\Models\Evenement',
        'target_id' => $request->module_id,
    ]);

    return redirect()->route('admin.notifications.index')->with('success', 'Règle modifiée avec succès');
}

// Marquer une notification comme lue et rediriger
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead(); // C'est ici que la magie opère !
            
            // On redirige vers l'URL stockée dans la notif (action_url)
            return redirect($notification->data['action_url'] ?? '/');
        }

        return back();
    }
}