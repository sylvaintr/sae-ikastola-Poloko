<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NotificationSetting;
use App\Models\Utilisateur; // Ton modèle Utilisateur
use App\Notifications\SendNotification;
use App\Models\Evenement;
use App\Models\DocumentObligatoire;
use App\Models\Fichier; 
use Carbon\Carbon;

class CheckNotifications extends Command
{
    protected $signature = 'notifications:check';
    protected $description = 'Vérifie les règles globales et envoie les notifications (avec Récurrence et Rappel)';

    public function handle()
    {
        $this->info('--- Démarrage de la vérification ---');
        
        // 1. On récupère toutes les règles actives
        $rules = NotificationSetting::where('is_active', true)->get();

        if ($rules->isEmpty()) {
            $this->info('Aucune règle active trouvée.');
            return;
        }

        foreach ($rules as $rule) {

            // ====================================================
            // CAS 1 : ÉVÉNEMENTS (Logique de "Rappel" / Compte à rebours)
            // ====================================================
            if ($rule->target_type === 'App\Models\Evenement' && $rule->target_id == 0) {
                
                $this->info("Règle Événements : {$rule->title} (Rappel à J-{$rule->reminder_days})");

                // On cherche les événements futurs
                $events = Evenement::where('dateE', '>=', now())->get();

                foreach ($events as $event) {
                    // CALCUL DU RAPPEL : Date Event - Jours de rappel
                    $dateRappel = Carbon::parse($event->dateE)->subDays($rule->reminder_days);

                    // On compare strictement à la date d'aujourd'hui
                    if ($dateRappel->isToday()) {
                        
                        $this->info(" -> BINGO ! C'est le jour du rappel pour : {$event->titre}");

                        $users = Utilisateur::all(); 

                        foreach ($users as $user) {
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

            // ====================================================
            // CAS 2 : DOCUMENTS (Logique de "Récurrence" / Anti-Spam)
            // ====================================================
            elseif ($rule->target_type === 'App\Models\DocumentObligatoire' && $rule->target_id == 0) {
                
                $this->info("Règle Documents : {$rule->title} (Récurrence : {$rule->recurrence_days} jours)");

                $allDocs = DocumentObligatoire::with('roles')->get();

                foreach ($allDocs as $doc) {
                    // Récupération des rôles concernés
                    $rolesIds = $doc->roles->pluck('id_role');
                    if ($rolesIds->isEmpty()) continue;

                    // Récupération des utilisateurs concernés
                    $usersCibles = Utilisateur::whereIn('id_role', $rolesIds)->get();

                    foreach ($usersCibles as $user) {
                        
                        // A. VÉRIFICATION : Le fichier existe-t-il ?
                        $aDepose = Fichier::where('idUser', $user->id)
                                    ->where('idDocumentObligatoire', $doc->idDocumentObligatoire)
                                    ->exists();

                        if (!$aDepose) {
                            // IL MANQUE LE DOCUMENT !

                            // B. ANTI-SPAM (Gestion de la Récurrence)
                            // Si une récurrence est définie (ex: 5 jours), on vérifie la dernière alerte.
                            if ($rule->recurrence_days > 0) {
                                
                                // On cherche la dernière notif envoyée à CE user pour CE document
                                $lastNotif = $user->notifications()
                                                  ->where('data->title', "Document manquant : {$doc->nom}")
                                                  ->latest()
                                                  ->first();

                                if ($lastNotif) {
                                    // Date Prochaine Alerte = Date Dernière Notif + Jours Récurrence
                                    $nextAlertDate = $lastNotif->created_at->addDays($rule->recurrence_days);

                                    // Si on est encore AVANT la date prévue, on ne fait rien
                                    if (now()->lessThan($nextAlertDate)) {
                                        // On passe au user suivant, c'est trop tôt pour le relancer
                                        continue; 
                                    }
                                }
                            }

                            // C. ENVOI DE LA NOTIFICATION
                            $user->notify(new SendNotification([
                                'title' => "Document manquant : {$doc->nom}",
                                'message' => "Merci de déposer le document requis.",
                                'action_url' => url('/documents'),
                            ]));
                            
                            $this->info("    -> Alerte envoyée à {$user->nom} pour {$doc->nom}");
                        }
                    }
                }
            }
        }
        
        $this->info('--- Vérification terminée ---');
    }
}