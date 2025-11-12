<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentObligatoire;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ObligatoryDocumentController extends Controller
{
    public function index(): View
    {
        $totalRolesCount = Role::count();
        
        $documents = DocumentObligatoire::with('roles')
            ->orderBy('idDocumentObligatoire')
            ->get()
            ->map(function ($document) use ($totalRolesCount) {
                // Calculer la date d'expiration réelle
                if ($document->dateE) {
                    if ($document->dateExpiration) {
                        // Date fixe
                        $document->calculatedExpirationDate = $document->dateExpiration;
                    } elseif ($document->delai) {
                        // Date calculée à partir du délai (aujourd'hui + délai en jours)
                        $document->calculatedExpirationDate = now()->addDays($document->delai)->format('Y-m-d');
                    } else {
                        $document->calculatedExpirationDate = null;
                    }
                } else {
                    $document->calculatedExpirationDate = null;
                }
                
                // Vérifier si tous les rôles sont sélectionnés
                $document->hasAllRoles = $document->roles->count() === $totalRolesCount && $totalRolesCount > 0;
                
                return $document;
            });

        return view('admin.obligatory-documents.index', compact('documents'));
    }

    public function create(): View
    {
        $roles = Role::select('idRole', 'name')->orderBy('name')->get();
        return view('admin.obligatory-documents.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules(), $this->getValidationMessages());

        // Trouver le premier ID disponible
        $availableId = $this->findAvailableId();

        // Créer le document
        $document = new DocumentObligatoire();
        $document->incrementing = false;
        $document->idDocumentObligatoire = $availableId;
        $document->nom = $validated['nom'];
        $this->setExpirationData($document, $validated);
        $document->save();

        // Sync roles
        $document->roles()->sync($validated['roles']);

        return redirect()
            ->route('admin.obligatory_documents.index')
            ->with('status', trans('admin.obligatory_documents.messages.created'));
    }

    public function edit(DocumentObligatoire $obligatoryDocument): View
    {
        $document = $obligatoryDocument;
        $document->load('roles');
        $roles = Role::select('idRole', 'name')->orderBy('name')->get();
        return view('admin.obligatory-documents.edit', compact('document', 'roles'));
    }

    public function update(Request $request, DocumentObligatoire $obligatoryDocument): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules(), $this->getValidationMessages());

        $obligatoryDocument->nom = $validated['nom'];
        $this->setExpirationData($obligatoryDocument, $validated);
        $obligatoryDocument->save();

        // Sync roles
        $obligatoryDocument->roles()->sync($validated['roles']);

        return redirect()
            ->route('admin.obligatory_documents.index')
            ->with('status', trans('admin.obligatory_documents.messages.updated'));
    }

    public function destroy(DocumentObligatoire $obligatoryDocument): RedirectResponse
    {
        $obligatoryDocument->delete();

        return redirect()
            ->route('admin.obligatory_documents.index')
            ->with('status', trans('admin.obligatory_documents.messages.deleted'));
    }

    /**
     * Retourne les règles de validation pour les documents obligatoires
     */
    private function getValidationRules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'expirationType' => ['required', 'in:none,delai,date'],
            'delai' => ['nullable', 'integer', 'min:0', 'required_if:expirationType,delai'],
            'dateExpiration' => ['nullable', 'date', 'required_if:expirationType,date'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:role,idRole'],
        ];
    }

    /**
     * Retourne les messages de validation pour les documents obligatoires
     */
    private function getValidationMessages(): array
    {
        return [
            'nom.required' => 'Le nom du document est requis.',
            'nom.max' => 'Le nom du document ne peut pas dépasser 100 caractères.',
            'expirationType.required' => 'Le type d\'expiration est requis.',
            'delai.required_if' => 'Le délai est requis lorsque le type d\'expiration est "délai".',
            'delai.min' => 'Le délai doit être un nombre positif.',
            'dateExpiration.required_if' => 'La date d\'expiration est requise lorsque le type d\'expiration est "date".',
            'roles.required' => 'Au moins un rôle doit être sélectionné.',
            'roles.min' => 'Au moins un rôle doit être sélectionné.',
        ];
    }

    /**
     * Définit les données d'expiration sur un document
     */
    private function setExpirationData(DocumentObligatoire $document, array $validated): void
    {
        $expirationType = $validated['expirationType'] ?? 'none';
        $document->dateE = $expirationType !== 'none';
        $document->delai = $expirationType === 'delai' ? $validated['delai'] : null;
        $document->dateExpiration = $expirationType === 'date' ? $validated['dateExpiration'] : null;
    }

    /**
     * Trouve le premier ID disponible dans la table documentObligatoire
     */
    private function findAvailableId(): int
    {
        $existingIds = DocumentObligatoire::orderBy('idDocumentObligatoire')
            ->pluck('idDocumentObligatoire')
            ->toArray();

        if (empty($existingIds)) {
            return 1;
        }

        $existingIdsSet = array_flip($existingIds);
        $maxId = max($existingIds);

        for ($id = 1; $id <= $maxId; $id++) {
            if (!isset($existingIdsSet[$id])) {
                return $id;
            }
        }

        return $maxId + 1;
    }
}

