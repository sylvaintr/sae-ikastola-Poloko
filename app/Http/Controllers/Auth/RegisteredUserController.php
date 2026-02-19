<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Méthode pour afficher le formulaire d'inscription. Cette méthode retourne la vue "auth.register" qui contient le formulaire permettant aux utilisateurs de créer un nouveau compte. Le formulaire inclut des champs pour le prénom, le nom, l'adresse e-mail, le mot de passe, la langue préférée et la date de naissance, ainsi qu'un champ reCAPTCHA pour la validation anti-bot.
     * @return View La vue du formulaire d'inscription
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Méthode pour gérer la soumission du formulaire d'inscription. Cette méthode valide les données soumises par l'utilisateur, crée un nouvel utilisateur dans la base de données avec le statut de validation à false (non validé), assigne automatiquement le rôle "parent" à l'utilisateur, déclenche l'événement "Registered", puis redirige vers la page de connexion avec un message indiquant que l'inscription est en attente de validation. Si le reCAPTCHA est activé et que la validation échoue, l'utilisateur est redirigé en arrière avec une erreur spécifique pour le reCAPTCHA.
     * @param Request $request La requête HTTP contenant les données du formulaire d'inscription
     * @return RedirectResponse Redirection vers la page de connexion avec un message de statut ou redirection en arrière avec des erreurs de validation
     * @throws \Illuminate\Validation\ValidationException Si la validation des données échoue
     * @throws \Exception Si une erreur survient lors de la création de l'utilisateur ou de l'assignation du rôle
     */
    public function store(Request $request): RedirectResponse
    {
        // Validation du reCAPTCHA (si activé)
        if (config('services.recaptcha.enabled', true)) {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            if (! $recaptchaResponse || ! $this->verifyRecaptcha($recaptchaResponse)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['g-recaptcha-response' => __('auth.recaptcha_failed')]);
            }
        }

        $request->validate([
            // Accept either a single 'name' (compatibility) or prenom+nom
            'name'          => ['required_without:prenom', 'string', 'max:255'],
            'prenom'        => ['required_without:name', 'string', 'max:255'],
            'nom'           => ['required_without:name', 'string', 'max:255'],
            'email'         => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:utilisateur,email',
            ],
            'password'      => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'languePref'    => ['nullable', 'string', 'in:fr,eus'],
            'dateNaissance' => ['nullable', 'date', 'before:today'],
        ]);

        // Map 'name' -> prenom/nom when provided
        $prenom = $request->input('prenom');
        $nom    = $request->input('nom');
        if (! $prenom && $request->filled('name')) {
            $parts  = preg_split('/\s+/', trim($request->input('name')), 2);
            $prenom = $parts[0] ?? null;
            $nom    = $parts[1] ?? '';
        }

        // Créer l'utilisateur avec statutValidation à false (non validé)
        $user = Utilisateur::create([
            'prenom'           => $prenom,
            'nom'              => $nom,
            'email'            => $request->email,
            'mdp'              => Hash::make($request->password),
            'languePref'       => $request->input('languePref', 'fr'),
            'dateNaissance'    => $request->filled('dateNaissance') ? $request->dateNaissance : null,
            'statutValidation' => false, // Toujours non validé pour les inscriptions publiques
        ]);

        // Assigner automatiquement le rôle "parent"
        $parentRole = Role::where('name', 'parent')->first();
        if ($parentRole) {
            $user->rolesCustom()->sync([
                $parentRole->idRole => ['model_type' => Utilisateur::class],
            ]);
        }

        event(new Registered($user));

        // Ne pas connecter l'utilisateur automatiquement car le compte n'est pas validé
        // Rediriger vers la page de connexion avec un message de confirmation
        return redirect(route('login'))
            ->with('status', __('auth.registration_pending_validation'));
    }

    /**
     * Méthode pour vérifier la réponse du reCAPTCHA. Cette méthode envoie une requête à l'API de Google reCAPTCHA pour valider la réponse fournie par l'utilisateur. En environnement local avec les clés de test, elle accepte toute réponse non vide pour faciliter le développement. En production, elle vérifie que la réponse est valide en utilisant la clé secrète configurée et l'adresse IP de l'utilisateur.
     * @param string $response La réponse du reCAPTCHA fournie par l'utilisateur
     * @return bool Vrai si la validation du reCAPTCHA est réussie, faux sinon
     * @throws \Exception Si une erreur survient lors de la communication avec l'API de Google reCAPTCHA
     */
    private function verifyRecaptcha(string $response): bool
    {
        $secretKey = config('services.recaptcha.secret_key');

        if (! $secretKey) {
            return false;
        }

        // En environnement local avec les clés de test, accepter toute réponse non vide
        // Les clés de test de Google acceptent toujours n'importe quelle réponse
        // La clé de test est récupérée depuis la configuration, pas hardcodée
        $testSecretKey = config('services.recaptcha.test_secret_key');
        if (config('app.env') === 'local' && $secretKey === $testSecretKey) {
            // Clés de test : accepter toute réponse non vide pour le développement
            return ! empty($response);
        }

        $url  = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret'   => $secretKey,
            'response' => $response,
            'remoteip' => request()->ip(),
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($options);
        $result  = @file_get_contents($url, false, $context);

        $json = $result !== false ? json_decode($result, true) : null;
        return isset($json['success']) && $json['success'] === true;
    }
}
