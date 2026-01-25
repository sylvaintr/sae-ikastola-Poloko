<?php
// Minimal Laravel bootstrap for debugging
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Utilisateur;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== TOUTES LES TABLES DE LA BASE ===\n";
$tables = Schema::getTableListing();
foreach ($tables as $table) {
    if (strpos($table, 'role') !== false || strpos($table, 'permission') !== false || strpos($table, 'model') !== false) {
        echo "- " . $table . "\n";
    }
}

echo "\n";
$u = Utilisateur::where('email','ca@example.com')->first();
if ($u) {
    echo "ID: " . $u->idUtilisateur . "\n";
    echo "Email: " . $u->email . "\n";
    echo "Roles: " . json_encode($u->getRoleNames()->toArray()) . "\n";
    echo "Permissions: " . json_encode($u->getAllPermissions()->pluck('name')->toArray()) . "\n";
} else {
    echo "❌ Utilisateur CA (ca@example.com) NOT FOUND!\n";
}

echo "\n=== Tous les roles ===\n";
$roles = Role::all();
foreach ($roles as $role) {
    echo "- " . $role->name . " (id=" . $role->id . ")\n";
}

echo "\n=== Toutes les permissions ===\n";
$perms = Permission::all();
foreach ($perms as $perm) {
    echo "- " . $perm->name . " (id=" . $perm->id . ")\n";
}

echo "\n=== Association role CA -> permissions ===\n";
$caRole = Role::where('name','CA')->first();
if ($caRole) {
    echo "Role CA found (id=" . $caRole->id . ")\n";
    echo "Permissions du role CA:\n";
    foreach ($caRole->permissions as $perm) {
        echo "  - " . $perm->name . "\n";
    }
} else {
    echo "❌ Role CA NOT FOUND!\n";
}

echo "\n=== Vérification tables Spatie ===\n";
echo "Contenu table 'role' (Spatie roles):\n";
$rolesTable = DB::table('role')->get();
echo json_encode($rolesTable->toArray(), JSON_PRETTY_PRINT) . "\n";

echo "\nContenu table 'permissions' (Spatie permissions):\n";
$permsTable = DB::table('permissions')->get();
echo json_encode($permsTable->toArray(), JSON_PRETTY_PRINT) . "\n";

echo "\nContenu table 'role_has_permissions':\n";
$rolePermsTable = DB::table('role_has_permissions')->get();
echo json_encode($rolePermsTable->toArray(), JSON_PRETTY_PRINT) . "\n";

echo "\nContenu table 'model_has_roles':\n";
try {
    $modelRolesTable = DB::table('model_has_roles')->get();
    echo json_encode($modelRolesTable->toArray(), JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "TABLE NOT FOUND: " . $e->getMessage() . "\n";
}

echo "\n=== Vérification association utilisateur CA -> role CA ===\n";
if ($u && $caRole) {
    $hasRole = $u->hasRole('CA');
    echo "User has role 'CA': " . ($hasRole ? "YES" : "NO") . "\n";
}
