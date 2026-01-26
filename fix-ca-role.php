<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\Utilisateur::where('email', 'ca@example.com')->first();
echo "Utilisateur trouvé: " . $user->email . "\n";

// Supprimer tous les rôles existants
$user->syncRoles([]);
echo "Rôles supprimés\n";

// Attribuer le rôle CA
$user->assignRole('CA');
echo "Rôle CA attribué\n";

// Vérifier
echo "Rôles actuels: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
echo "Permissions: " . implode(', ', $user->getAllPermissions()->pluck('name')->toArray()) . "\n";
