<?php
namespace App\Console\Commands;

use App\Models\DocumentObligatoire;
use App\Models\Evenement;
use App\Models\NotificationSetting;
use App\Models\Utilisateur;
use App\Notifications\SendNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckNotifications extends Command
{
    protected $signature = 'notifications:check';

    protected $description = 'Vérifie les événements (rappels) et les documents obligatoires';

    /**
     * Méthode pour exécuter la commande de vérification des notifications. Cette méthode affiche des informations de démarrage, récupère les règles de notification actives, puis traite les événements et les documents obligatoires en fonction de ces règles. Pour les événements, elle vérifie si un rappel doit être envoyé aujourd'hui et envoie des notifications aux utilisateurs concernés. Pour les documents obligatoires, elle vérifie si les utilisateurs cibles ont déposé les documents requis et envoie des notifications de rappel si nécessaire. Enfin, elle affiche des informations de fin de vérification.
     * @return void
     */
    public function handle()
    {
        $this->info('--- Démarrage de la vérification ---');
        $this->info('Date serveur : ' . now()->format('Y-m-d H:i:s'));

        $rules = NotificationSetting::where('is_active', true)->get();

        if ($rules->isEmpty()) {
            $this->info('Aucune règle active trouvée.');
            return;
        }

        foreach ($rules as $rule) {
            if ($rule->target_type === 'App\Models\Evenement' && $rule->target_id == 0) {
                $this->processEvents($rule);
            } elseif ($rule->target_type === 'App\Models\DocumentObligatoire' && $rule->target_id == 0) {
                $this->processDocuments($rule);
            }
        }

        $this->info('--- Vérification terminée ---');
    }

    /**
     * Méthode pour traiter les règles de notification liées aux événements. Cette méthode affiche des informations sur la règle en cours de traitement, récupère tous les événements à venir, puis vérifie si un rappel doit être envoyé aujourd'hui en fonction de la date de l'événement et du nombre de jours avant le rappel défini dans la règle. Si un rappel doit être envoyé, elle vérifie pour chaque utilisateur s'il a déjà reçu une notification pour cet événement et cette date, et si ce n'est pas le cas, elle envoie une notification de rappel à l'utilisateur. Enfin, elle affiche des informations sur les notifications envoyées.
     * @param NotificationSetting $rule La règle de notification à traiter pour les événements
     * @return void
     */
    private function processEvents($rule)
    {
        $this->info("Traitement Règle Événements : {$rule->title} (Rappel à J-{$rule->reminder_days})");

        $events = Evenement::where('dateE', '>=', now())->get();

        foreach ($events as $event) {
            $dateRappel = Carbon::parse($event->dateE)->subDays($rule->reminder_days);

            if ($dateRappel->isToday()) {
                $this->info(" -> BINGO ! C'est le jour du rappel pour : {$event->titre}");

                $users = Utilisateur::all();

                // CORRECTION ICI : On force le format Y-m-d pour la comparaison
                $dateEventFormattee = Carbon::parse($event->dateE)->format('Y-m-d');

                foreach ($users as $user) {
                    $dejaFait = $user->notifications()
                        ->where('data->event_id', $event->idEvenement)
                        ->where('data->event_date', $dateEventFormattee)
                        ->exists();

                    if ($dejaFait) {
                        continue;
                    }

                    $user->notify(new SendNotification([
                        'title' => "Rappel : {$event->titre}",
                        'message'    => "L'événement aura lieu le " . Carbon::parse($event->dateE)->format('d/m/Y'),
                        'action_url' => url('/evenements/' . $event->idEvenement),
                        'event_id'   => $event->idEvenement,
                        'event_date' => $dateEventFormattee,
                    ]));
                }
                $this->info("    -> Notifications envoyées.");
            }
        }
    }

    /**
     * Méthode pour traiter les règles de notification liées aux documents obligatoires. Cette méthode affiche des informations sur la règle en cours de traitement, récupère tous les documents obligatoires avec leurs rôles associés, puis pour chaque document, elle trouve les utilisateurs cibles en fonction de leurs rôles. Pour chaque utilisateur cible, elle vérifie s'il a déposé le document requis. Si ce n'est pas le cas, elle vérifie si un rappel doit être envoyé en fonction de la récurrence définie dans la règle et des notifications précédemment envoyées. Si un rappel doit être envoyé, elle envoie une notification à l'utilisateur pour lui rappeler de déposer le document requis. Enfin, elle affiche des informations sur les notifications envoyées.
     * @param NotificationSetting $rule La règle de notification à traiter pour les documents obligatoires
     * @return void
     */
    private function processDocuments($rule)
    {
        $this->info("Traitement Règle Documents : {$rule->title} (Récurrence : {$rule->recurrence_days} jours)");

        $allDocs = DocumentObligatoire::with('roles')->get();

        foreach ($allDocs as $doc) {
            $rolesIds = $doc->roles->pluck('idRole');

            if (! $rolesIds->isEmpty()) {

                $usersCibles = Utilisateur::whereHas('rolesCustom', function ($query) use ($rolesIds) {
                    $query->whereIn('role.idRole', $rolesIds);
                })->get();

                foreach ($usersCibles as $user) {
                    $this->checkSingleUserDocument($user, $doc, $rule);
                }
            }
        }
    }

    /**
     * Méthode pour vérifier si un utilisateur a déposé un document obligatoire et envoyer une notification de rappel si nécessaire. Cette méthode prend un utilisateur, un document obligatoire et une règle de notification en paramètre, puis vérifie si l'utilisateur a déposé le document requis. Si ce n'est pas le cas, elle vérifie si un rappel doit être envoyé en fonction de la récurrence définie dans la règle et des notifications précédemment envoyées pour ce document. Si un rappel doit être envoyé, elle envoie une notification à l'utilisateur pour lui rappeler de déposer le document requis. Enfin, elle affiche des informations sur les notifications envoyées.
     * @param Utilisateur $user L'utilisateur à vérifier pour le dépôt du document obligatoire
     * @param DocumentObligatoire $doc Le document obligatoire à vérifier pour l'utilisateur
     * @param NotificationSetting $rule La règle de notification à utiliser pour déterminer la récurrence des rappels
     * @return void
     * @throws \Exception Si une erreur survient lors de la vérification du dépôt du document ou de l'envoi de la notification
     */
    private function checkSingleUserDocument($user, $doc, $rule)
    {
        $aDepose = $user->documents()
            ->where('idDocumentObligatoire', $doc->idDocumentObligatoire)
            ->exists();

        if (! $aDepose) {
            if ($rule->recurrence_days > 0) {
                $lastNotif = $user->notifications()
                    ->where('data->doc_id', $doc->idDocumentObligatoire)
                    ->latest()
                    ->first();

                if (! $lastNotif) {
                    $lastNotif = $user->notifications()
                        ->where('data->title', "Document manquant : {$doc->nom}")
                        ->latest()
                        ->first();
                }

                if ($lastNotif) {
                    $nextAlertDate = $lastNotif->created_at->addDays($rule->recurrence_days);

                    if (now()->lessThan($nextAlertDate)) {
                        return;
                    }
                }
            }

            $user->notify(new SendNotification([
                'title' => "Document manquant : {$doc->nom}",
                'message' => "Merci de déposer le document requis : {$doc->nom}.",
                'action_url' => url('/documents'),
                'doc_id'     => $doc->idDocumentObligatoire,
            ]));

            $this->info("    -> Alerte envoyée à {$user->nom} pour {$doc->nom}");
        }
    }
}
