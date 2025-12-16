<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentObligatoire;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ObligatoryDocumentController extends Controller
{
    /**
     * Cache de la longueur maximale du champ `nom` pour éviter les requêtes répétées.
     * Utilisé plutôt qu'une variable statique locale afin de pouvoir être réinitialisé dans les tests.
     *
     * @var int|null
     */
    private static $cachedNomMaxLength = null;

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
        $nomMaxLength = $this->getNomMaxLength();
        return view('admin.obligatory-documents.create', compact('roles', 'nomMaxLength'));
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
        $nomMaxLength = $this->getNomMaxLength();
        return view('admin.obligatory-documents.edit', compact('document', 'roles', 'nomMaxLength'));
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
        $nomMaxLength = $this->getNomMaxLength();

        return [
            'nom' => ['required', 'string', 'max:' . $nomMaxLength],
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
        $nomMaxLength = $this->getNomMaxLength();

        return [
            'nom.required' => trans('admin.obligatory_documents.validation.nom_required'),
            'nom.max' => trans('admin.obligatory_documents.validation.nom_max', ['max' => $nomMaxLength]),
            'expirationType.required' => trans('admin.obligatory_documents.validation.expiration_type_required'),
            'delai.required_if' => trans('admin.obligatory_documents.validation.delai_required_if'),
            'delai.min' => trans('admin.obligatory_documents.validation.delai_min'),
            'dateExpiration.required_if' => trans('admin.obligatory_documents.validation.date_expiration_required_if'),
            'roles.required' => trans('admin.obligatory_documents.fields.roles_required'),
            'roles.min' => trans('admin.obligatory_documents.fields.roles_required'),
        ];
    }

    /**
     * Récupère dynamiquement la longueur maximale du champ "nom" dans la base.
     */
    private function getNomMaxLength(): int
    {
        if (self::$cachedNomMaxLength !== null) {
            return self::$cachedNomMaxLength;
        }

        $default = 100;
        $table = 'documentObligatoire';
        $column = 'nom';

        try {
            $length = DB::table('information_schema.columns')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('column_name', $column)
                ->value('character_maximum_length');

            self::$cachedNomMaxLength = $length ? (int) $length : $default;
        } catch (\Throwable $e) {
            self::$cachedNomMaxLength = $default;
        }

        return self::$cachedNomMaxLength;
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

