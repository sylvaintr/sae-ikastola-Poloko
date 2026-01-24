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
    /**
     * Le nom de la commande à taper dans le terminal.
     */
    protected $signature = 'notifications:check';

    /**
     * Description de la commande.
     */
    protected $description = 'Vérifie les événements (rappels) et les documents obligatoires (manquants + récurrence)';

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
                
                $this->info("Traitement Règle Événements : {$rule->title} (Rappel à J-{$rule->reminder_days})");

                // On cherche les événements futurs
                $events = Evenement::where('dateE', '>=', now())->get();

                foreach ($events as $event) {
                    // Calcul : Date de l'event MOINS le délai de rappel
                    $dateRappel = Carbon::parse($event->dateE)->subDays($rule->reminder_days);

                    // On compare strictement à la date d'aujourd'hui
                    if ($dateRappel->isToday()) {
                        
                        $this->info(" -> BINGO ! C'est le jour du rappel pour : {$event->titre}");

                        // On envoie à tous les utilisateurs (ou filtre si nécessaire)
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
            // CAS 2 : DOCUMENTS OBLIGATOIRES (Logique de "Récurrence" / Manquants)
            // ====================================================
            elseif ($rule->target_type === 'App\Models\DocumentObligatoire' && $rule->target_id == 0) {
                
                $this->info("Traitement Règle Documents : {$rule->title} (Récurrence : {$rule->recurrence_days} jours)");

                // On récupère les documents obligatoires avec leurs rôles cibles
                $allDocs = DocumentObligatoire::with('roles')->get();

                foreach ($allDocs as $doc) {
                    
                    // 1. Récupérer les IDs des rôles concernés (table Role -> idRole)
                    $rolesIds = $doc->roles->pluck('idRole');
                    
                    if ($rolesIds->isEmpty()) continue;

                    // 2. RÉCUPÉRATION DES UTILISATEURS (CORRIGÉ)
                    // On utilise 'rolesCustom' (défini dans ton Utilisateur.php) pour passer par la table 'avoir'
                    // On filtre sur 'role.idRole'
                    $usersCibles = Utilisateur::whereHas('rolesCustom', function($query) use ($rolesIds) {
                        $query->whereIn('role.idRole', $rolesIds); 
                    })->get();

                    foreach ($usersCibles as $user) {
                        
                        // 3. VÉRIFICATION : L'utilisateur a-t-il rendu le document ?
                        // PROBLÈME ACTUEL : Pas de liaison ID entre Document et DocumentObligatoire.
                        // SOLUTION TEMPORAIRE : On compare les NOMS.
                        
                        $aDepose = $user->documents()
                                        // ⚠️ VÉRIFIE ICI : Est-ce que la colonne s'appelle 'nom' ou 'titre' dans ta table 'document' ?
                                        ->where('nom', $doc->nom) 
                                        ->exists();

                        if (!$aDepose) {
                            // --- IL MANQUE LE DOCUMENT ! ---

                            // 4. ANTI-SPAM (Gestion de la Récurrence)
                            if ($rule->recurrence_days > 0) {
                                // On cherche la dernière notification envoyée pour ce document précis
                                $lastNotif = $user->notifications()
                                                  ->where('data->title', "Document manquant : {$doc->nom}")
                                                  ->latest() // La plus récente
                                                  ->first();

                                if ($lastNotif) {
                                    // On calcule la date de la prochaine alerte autorisée
                                    $nextAlertDate = $lastNotif->created_at->addDays($rule->recurrence_days);
                                    
                                    // Si on est aujourd'hui (now) et que c'est encore trop tôt (< nextAlertDate)
                                    if (now()->lessThan($nextAlertDate)) {
                                        // On saute cet utilisateur, on ne le spamme pas
                                        continue; 
                                    }
                                }
                            }

                            // 5. ENVOI DE LA NOTIFICATION
                            $user->notify(new SendNotification([
                                'title' => "Document manquant : {$doc->nom}",
                                'message' => "Merci de déposer le document requis : {$doc->nom}.",
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