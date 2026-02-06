<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NotificationSetting;
use App\Models\Utilisateur;
use App\Models\Evenement;
use App\Models\DocumentObligatoire;
use App\Notifications\SendNotification;
use Carbon\Carbon;

class CheckNotifications extends Command
{
    protected $signature = 'notifications:check';

    protected $description = 'Vérifie les événements (rappels) et les documents obligatoires (manquants + récurrence)';

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

    
    private function processEvents($rule)
    {
        $this->info("Traitement Règle Événements : {$rule->title} (Rappel à J-{$rule->reminder_days})");

        $events = Evenement::where('dateE', '>=', now())->get();

        foreach ($events as $event) {
            $dateRappel = Carbon::parse($event->dateE)->subDays($rule->reminder_days);

            if ($dateRappel->isToday()) {
                $this->info(" -> BINGO ! C'est le jour du rappel pour : {$event->titre}");

                $users = Utilisateur::all();

                foreach ($users as $user) {
                    $dejaFait = $user->notifications()
                        ->where('data->title', "Rappel : {$event->titre}")
                        ->whereDate('created_at', Carbon::today())
                        ->exists();

                    if ($dejaFait) {
                        continue; 
                    }

                    $user->notify(new SendNotification([
                        'title' => "Rappel : {$event->titre}",
                        'message' => "L'événement aura lieu le " . Carbon::parse($event->dateE)->format('d/m/Y'),
                        'action_url' => url('/evenements/' . $event->idEvenement),
                    ]));
                }
                $this->info("    -> Notifications envoyées.");
            }
        }
    }

   
    private function processDocuments($rule)
    {
        $this->info("Traitement Règle Documents : {$rule->title} (Récurrence : {$rule->recurrence_days} jours)");

        $allDocs = DocumentObligatoire::with('roles')->get();

        foreach ($allDocs as $doc) {
            $rolesIds = $doc->roles->pluck('idRole');

            if ($rolesIds->isEmpty()) {
                continue; 
            }

            $usersCibles = Utilisateur::whereHas('rolesCustom', function ($query) use ($rolesIds) {
                $query->whereIn('role.idRole', $rolesIds);
            })->get();

            foreach ($usersCibles as $user) {
                $this->checkSingleUserDocument($user, $doc, $rule);
            }
        }
    }

  
    private function checkSingleUserDocument($user, $doc, $rule)
    {
        $aDepose = $user->documents()
            ->where('idDocumentObligatoire', $doc->idDocumentObligatoire)
            ->exists();

        if (!$aDepose) {
            if ($rule->recurrence_days > 0) {
                $lastNotif = $user->notifications()
                    ->where('data->title', "Document manquant : {$doc->nom}")
                    ->latest()
                    ->first();

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
            ]));

            $this->info("    -> Alerte envoyée à {$user->nom} pour {$doc->nom}");
        }
    }
}

