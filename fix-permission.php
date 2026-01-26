<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Récupérer le rôle CA
$role = Spatie\Permission\Models\Role::where('name', 'CA')->first();
echo "Rôle trouvé: " . $role->name . "\n";

// Donner la permission gerer-tache
$role->givePermissionTo('gerer-tache');
echo "Permission gerer-tache donnée au rôle CA\n";

// Vider le cache des permissions
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Vérifier
$user = App\Models\Utilisateur::where('email', 'ca@example.com')->first();
$user->syncRoles(['CA']);
echo "Permissions de l'utilisateur CA: " . implode(', ', $user->getAllPermissions()->pluck('name')->toArray()) . "\n";
