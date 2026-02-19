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

    /**
     * Méthode pour afficher la liste des documents obligatoires. Cette méthode récupère tous les documents obligatoires de la base de données, y compris les rôles associés à chaque document, et calcule la date d'expiration réelle en fonction des champs "dateE", "delai" et "dateExpiration". Elle vérifie également si tous les rôles sont sélectionnés pour chaque document. Enfin, elle retourne la vue "admin.obligatory-documents.index" avec les données des documents à afficher dans la liste.
     * @return View La vue avec les données des documents obligatoires
     */
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

    /**
     * Méthode pour afficher le formulaire de création d'un document obligatoire. Cette méthode récupère tous les rôles disponibles dans la base de données pour permettre à l'administrateur de sélectionner les rôles associés au document obligatoire. Elle calcule également la longueur maximale du champ "nom" en interrogeant la structure de la base de données, afin d'appliquer cette contrainte dans le formulaire. Enfin, elle retourne la vue "admin.obligatory-documents.create" avec les données nécessaires pour afficher le formulaire de création.
     * @return View La vue avec les données des rôles et la longueur maximale du champ "nom" pour le formulaire de création
     */
    public function create(): View
    {
        $roles        = Role::select('idRole', 'name')->orderBy('name')->get();
        $nomMaxLength = $this->getNomMaxLength();
        return view('admin.obligatory-documents.create', compact('roles', 'nomMaxLength'));
    }

    /**
     * Méthode pour gérer la soumission du formulaire de création d'un document obligatoire. Cette méthode valide les données soumises par l'administrateur, trouve le premier ID disponible pour le nouveau document, crée un nouvel enregistrement dans la base de données avec les données validées, puis synchronise les rôles associés au document. Enfin, elle redirige vers la liste des documents obligatoires avec un message de succès dans la session. Si la validation échoue, l'administrateur est redirigé en arrière avec les erreurs de validation appropriées.
     * @param Request $request La requête HTTP contenant les données du formulaire de création d'un document obligatoire
     * @return RedirectResponse Redirection vers la liste des documents obligatoires avec un message de succès ou redirection en arrière avec des erreurs de validation
     * @throws \Illuminate\Validation\ValidationException Si la validation des données échoue
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules(), $this->getValidationMessages());

        // Trouver le premier ID disponible
        $availableId = $this->findAvailableId();

        // Créer le document
        $document                        = new DocumentObligatoire();
        $document->incrementing          = false;
        $document->idDocumentObligatoire = $availableId;
        $document->nom                   = $validated['nom'];
        $this->setExpirationData($document, $validated);
        $document->save();

        // Sync roles
        $document->roles()->sync($validated['roles']);

        return redirect()
            ->route('admin.obligatory_documents.index')
            ->with('status', trans('admin.obligatory_documents.messages.created'));
    }

    /**
     * Méthode pour afficher le formulaire d'édition d'un document obligatoire. Cette méthode reçoit un objet "DocumentObligatoire" en paramètre, charge les rôles associés à ce document, récupère tous les rôles disponibles dans la base de données, calcule la longueur maximale du champ "nom" en interrogeant la structure de la base de données, puis retourne la vue "admin.obligatory-documents.edit" avec les données du document à éditer, les rôles disponibles et la longueur maximale du champ "nom" pour le formulaire d'édition. Cette méthode est utilisée pour afficher le formulaire d'édition pré-rempli avec les données du document sélectionné, permettant à l'administrateur de modifier les informations du document obligatoire.
     * @param DocumentObligatoire $obligatoryDocument Le document obligatoire à éditer
     * @return View La vue avec les données du document à éditer, les rôles disponibles et la longueur maximale du champ "nom" pour le formulaire d'édition
     */
    public function edit(DocumentObligatoire $obligatoryDocument): View
    {
        $document = $obligatoryDocument;
        $document->load('roles');
        $roles        = Role::select('idRole', 'name')->orderBy('name')->get();
        $nomMaxLength = $this->getNomMaxLength();
        return view('admin.obligatory-documents.edit', compact('document', 'roles', 'nomMaxLength'));
    }

    /**
     * Méthode pour gérer la soumission du formulaire d'édition d'un document obligatoire. Cette méthode valide les données soumises par l'administrateur, met à jour les informations du document obligatoire avec les données validées, synchronise les rôles associés au document, puis redirige vers la liste des documents obligatoires avec un message de succès dans la session. Si la validation échoue, l'administrateur est redirigé en arrière avec les erreurs de validation appropriées.
     * @param Request $request La requête HTTP contenant les données du formulaire d'édition d'un document obligatoire
     * @param DocumentObligatoire $obligatoryDocument Le document obligatoire à mettre à jour
     * @return RedirectResponse Redirection vers la liste des documents obligatoires avec un message de succès ou redirection en arrière avec des erreurs de validation
     * @throws \Illuminate\Validation\ValidationException Si la validation des données échoue
     */
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

    /**
     * Méthode pour supprimer un document obligatoire. Cette méthode reçoit un objet "DocumentObligatoire" en paramètre, supprime ce document de la base de données, puis redirige vers la liste des documents obligatoires avec un message de succès dans la session. Cette méthode est utilisée pour permettre à l'administrateur de supprimer un document obligatoire qui n'est plus nécessaire ou pertinent.
     * @param DocumentObligatoire $obligatoryDocument Le document obligatoire à supprimer
     * @return RedirectResponse Redirection vers la liste des documents obligatoires avec un message de succès après la suppression du document obligatoire
     */
    public function destroy(DocumentObligatoire $obligatoryDocument): RedirectResponse
    {
        $obligatoryDocument->delete();

        return redirect()
            ->route('admin.obligatory_documents.index')
            ->with('status', trans('admin.obligatory_documents.messages.deleted'));
    }

    /**
     * Méthode pour retourner les règles de validation pour les documents obligatoires. Cette méthode définit les règles de validation pour les champs du formulaire de création et d'édition des documents obligatoires, en utilisant la longueur maximale du champ "nom" récupérée dynamiquement à partir de la structure de la base de données. Les règles incluent des contraintes pour le champ "nom", les types d'expiration, les délais, les dates d'expiration et les rôles associés. Ces règles sont utilisées pour valider les données soumises par l'administrateur lors de la création ou de l'édition d'un document obligatoire.
     * @return array Les règles de validation pour les documents obligatoires
     */
    private function getValidationRules(): array
    {
        $nomMaxLength = $this->getNomMaxLength();

        return [
            'nom'            => ['required', 'string', 'max:' . $nomMaxLength],
            'expirationType' => ['required', 'in:none,delai,date'],
            'delai'          => ['nullable', 'integer', 'min:0', 'required_if:expirationType,delai'],
            'dateExpiration' => ['nullable', 'date', 'required_if:expirationType,date'],
            'roles'          => ['required', 'array', 'min:1'],
            'roles.*'        => ['exists:role,idRole'],
        ];
    }

    /**
     * Méthode pour retourner les messages de validation personnalisés pour les documents obligatoires. Cette méthode définit les messages d'erreur personnalisés pour les règles de validation des champs du formulaire de création et d'édition des documents obligatoires, en utilisant la longueur maximale du champ "nom" récupérée dynamiquement à partir de la structure de la base de données. Ces messages sont utilisés pour fournir des retours d'erreur clairs et spécifiques à l'administrateur lorsque la validation des données soumises échoue lors de la création ou de l'édition d'un document obligatoire.
     * @return array Les messages de validation personnalisés pour les documents obligatoires
     */
    private function getValidationMessages(): array
    {
        $nomMaxLength = $this->getNomMaxLength();

        return [
            'nom.required'               => trans('admin.obligatory_documents.validation.nom_required'),
            'nom.max'                    => trans('admin.obligatory_documents.validation.nom_max', ['max' => $nomMaxLength]),
            'expirationType.required'    => trans('admin.obligatory_documents.validation.expiration_type_required'),
            'delai.required_if'          => trans('admin.obligatory_documents.validation.delai_required_if'),
            'delai.min'                  => trans('admin.obligatory_documents.validation.delai_min'),
            'dateExpiration.required_if' => trans('admin.obligatory_documents.validation.date_expiration_required_if'),
            'roles.required'             => trans('admin.obligatory_documents.fields.roles_required'),
            'roles.min'                  => trans('admin.obligatory_documents.fields.roles_required'),
        ];
    }

    /**
     * Méthode pour récupérer la longueur maximale du champ "nom" à partir de la structure de la base de données. Cette méthode interroge la table "information_schema.columns" pour obtenir la valeur de "character_maximum_length" du champ "nom" dans la table "documentObligatoire". La valeur récupérée est mise en cache pour éviter des requêtes répétées lors de l'affichage des formulaires de création et d'édition des documents obligatoires. Si une erreur survient lors de la récupération de la longueur maximale, une valeur par défaut de 100 est utilisée.
     * @return int La longueur maximale du champ "nom" pour les documents obligatoires
     */
    private function getNomMaxLength(): int
    {
        if (self::$cachedNomMaxLength !== null) {
            return self::$cachedNomMaxLength;
        }

        $default = 100;
        $table   = 'documentObligatoire';
        $column  = 'nom';

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
     * Méthode pour définir les données d'expiration d'un document obligatoire en fonction des données validées. Cette méthode prend un objet "DocumentObligatoire" et un tableau de données validées en paramètre, puis met à jour les champs "dateE", "delai" et "dateExpiration" du document en fonction du type d'expiration sélectionné (aucune expiration, délai ou date fixe). Cette méthode est utilisée lors de la création ou de l'édition d'un document obligatoire pour enregistrer correctement les informations d'expiration en fonction des choix de l'administrateur.
     * @param DocumentObligatoire $document Le document obligatoire à mettre à jour avec les données d'expiration
     * @param array $validated Les données validées du formulaire de création ou d'édition d'un document obligatoire
     */
    private function setExpirationData(DocumentObligatoire $document, array $validated): void
    {
        $expirationType           = $validated['expirationType'] ?? 'none';
        $document->dateE          = $expirationType !== 'none';
        $document->delai          = $expirationType === 'delai' ? $validated['delai'] : null;
        $document->dateExpiration = $expirationType === 'date' ? $validated['dateExpiration'] : null;
    }

    /**
     * Méthode pour trouver le premier ID disponible pour un nouveau document obligatoire. Cette méthode récupère tous les IDs existants des documents obligatoires, puis trouve le premier ID manquant dans la séquence. Si aucun ID n'est manquant, elle retourne l'ID suivant après le maximum existant. Cette méthode est utilisée lors de la création d'un nouveau document obligatoire pour s'assurer que l'ID attribué est unique et suit une séquence logique.
     * @return int Le premier ID disponible pour un nouveau document obligatoire
     * @throws \Exception Si une erreur survient lors de la récupération des IDs existants ou du calcul de l'ID disponible
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
        $maxId          = max($existingIds);

        for ($id = 1; $id <= $maxId; $id++) {
            if (! isset($existingIdsSet[$id])) {
                return $id;
            }
        }

        return $maxId + 1;
    }
}
