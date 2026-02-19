<?php
namespace App\Providers;

use App\Models\DocumentObligatoire;
use App\Models\Utilisateur;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Méthode pour enregistrer les services de l'application. Cette méthode est utilisée pour lier des classes ou des interfaces dans le conteneur de services de Laravel, ce qui permet de gérer les dépendances et d'injecter des services dans différentes parties de l'application. Actuellement, cette méthode est vide, ce qui signifie qu'aucun service personnalisé n'est enregistré pour le moment.
     */
    public function register(): void
    {
        //
    }

    /**
     * Méthode pour démarrer les services de l'application. Cette méthode est appelée après que tous les services ont été enregistrés, et elle est utilisée pour effectuer des actions d'initialisation ou de configuration supplémentaires. Dans ce cas, elle configure la longueur par défaut des chaînes de caractères dans la base de données à 125 caractères, définit des liaisons de route personnalisées pour les paramètres "account" et "obligatoryDocument", et configure la pagination pour utiliser le style Bootstrap 5.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(125);

        Route::bind('account', function ($value) {
            return Utilisateur::where('idUtilisateur', $value)->firstOrFail();
        });

        Route::bind('obligatoryDocument', function ($value) {
            return DocumentObligatoire::where('idDocumentObligatoire', $value)->firstOrFail();
        });

        // Utiliser Bootstrap 5 pour la pagination
        Paginator::useBootstrapFive();
    }
}
