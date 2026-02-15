<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Créer la permission si elle n'existe pas
$permission = Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gerer-tache', 'guard_name' => 'web']);
echo "Permission créée/trouvée: " . $permission->name . "\n";

// Récupérer le rôle CA
$role = Spatie\Permission\Models\Role::where('name', 'CA')->first();
echo "Rôle trouvé: " . $role->name . "\n";

// Donner la permission au rôle
$role->givePermissionTo('gerer-tache');
echo "Permission gerer-tache donnée au rôle CA\n";

// Vider le cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Vérifier
$user = App\Models\Utilisateur::where('email', 'ca@example.com')->first();
$user->syncRoles(['CA']);
echo "Permissions de l'utilisateur CA:\n";
foreach ($user->getAllPermissions() as $perm) {
    echo "  - " . $perm->name . "\n";
}
