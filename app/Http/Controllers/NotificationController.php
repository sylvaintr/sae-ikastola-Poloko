<?php
namespace App\Http\Controllers;

use App\Models\DocumentObligatoire;
use App\Models\Evenement;
use App\Models\NotificationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Methode pour afficher la liste des paramètres de notification
     * @return View la vue affichant la liste des paramètres de notification
     */
    public function index(): View
    {
        $settings = NotificationSetting::with('target')->latest()->get();
        return view('admin.notifications.index', compact('settings'));
    }

    /**
     * Methode pour afficher le formulaire de création d'un paramètre de notification
     * @return View la vue affichant le formulaire de création d'un paramètre de notification
     */
    public function create(): View
    {
        return view('admin.notifications.create');
    }

    /**
     * Methode pour stocker un nouveau paramètre de notification dans la base de données
     * @param Request $request la requête HTTP contenant les données du formulaire de création d'un paramètre de notification
     * @return RedirectResponse la réponse de redirection vers la liste des paramètres de notification avec un message de succès
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'module_id'       => 'required|integer',
            'module_type'     => 'required|in:Document,Evènement',
            'recurrence_days' => 'nullable|integer',
            'reminder_days'   => 'nullable|integer',
            'description'     => 'nullable|string',
            'is_active'       => 'nullable',
        ]);

        $targetClass = null;

        if ($request->module_type === 'Document') {
            $targetClass = DocumentObligatoire::class;
        } elseif ($request->module_type === 'Evènement') {
            $targetClass = Evenement::class;
        }

        NotificationSetting::create([
            'title'           => $request->title,
            'description'     => $request->description,
            'recurrence_days' => $request->recurrence_days,
            'reminder_days'   => $request->reminder_days,
            'is_active'       => $request->has('is_active'),
            'target_id'       => $request->module_id,
            'target_type'     => $targetClass,
        ]);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification ajoutée avec succès !');
    }

    /**
     * Methode pour afficher le formulaire d'édition d'un paramètre de notification
     * @param int $id l'identifiant du paramètre de notification à éditer
     * @return View la vue affichant le formulaire d'édition du paramètre de notification
     */
    public function edit($id): View
    {
        $setting = NotificationSetting::findOrFail($id);
        return view('admin.notifications.edit', compact('setting'));
    }
/**
 * Methode pour mettre à jour un paramètre de notification dans la base de données
 * @param Request $request la requête HTTP contenant les données du formulaire d'édition d'un paramètre de notification
 * @param int $id l'identifiant du paramètre de notification à mettre à jour
 * @return RedirectResponse la réponse de redirection vers la liste des paramètres de notification
 */
    public function update(Request $request, $id): RedirectResponse
    {
        $setting = NotificationSetting::findOrFail($id);

        $request->validate([
            'title'           => 'required|string|max:255',
            'module_id'       => 'required|integer',
            'module_type'     => 'required|in:Document,Evènement',
            'recurrence_days' => 'nullable|integer',
            'reminder_days'   => 'nullable|integer',
            'description'     => 'nullable|string',
            'is_active'       => 'nullable',
        ]);

        $targetClass = null;

        if ($request->module_type === 'Document') {
            $targetClass = DocumentObligatoire::class;
        } elseif ($request->module_type === 'Evènement') {
            $targetClass = Evenement::class;
        }

        $setting->update([
            'title'           => $request->title,
            'description'     => $request->description,
            'recurrence_days' => $request->recurrence_days,
            'reminder_days'   => $request->reminder_days,
            'is_active'       => $request->has('is_active'),
            'target_type'     => $targetClass,
            'target_id'       => $request->module_id,
        ]);

        return redirect()->route('admin.notifications.index');
    }

    /**
     * Methode pour marquer une notification comme lue pour l'utilisateur connecté
     * @param string $id l'identifiant de la notification à marquer comme lue
     * @return RedirectResponse la réponse de redirection vers la page précédente
     */
    public function markAsRead($id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return back();
    }
}
