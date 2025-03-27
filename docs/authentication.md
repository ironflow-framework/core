# Authentication

Le système d'authentification d'IronFlow offre une solution flexible et sécurisée pour gérer l'authentification des utilisateurs.

## Configuration rapide

```bash
php iron auth:install
```

Cette commande va :
- Créer les migrations nécessaires
- Installer les vues d'authentification
- Configurer les routes
- Générer les contrôleurs

## Types d'authentification

### 1. Authentification Web (Session)

```php
// Dans un contrôleur
public function login(Request $request)
{
    $credentials = $request->only(['email', 'password']);
    
    if (auth()->attempt($credentials)) {
        return redirect('/dashboard');
    }
    
    return back()->with('error', 'Invalid credentials');
}
```

### 2. Authentification API (Token)

```php
// Configuration dans config/auth.php
'guards' => [
    'api' => [
        'driver' => 'token',
        'provider' => 'users',
    ],
]

// Utilisation dans les requêtes
$response = $client->get('/api/user', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
    ],
]);
```

### 3. Authentification OAuth

```php
// Configuration dans .env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret

// Routes
Router::get('/auth/{provider}', [OAuthController::class, 'redirect']);
Router::get('/auth/{provider}/callback', [OAuthController::class, 'callback']);

// Lien dans la vue
<a href="/auth/google">Se connecter avec Google</a>
```

## Middleware d'authentification

```php
// Protection d'une route
Router::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');

// Protection d'un groupe de routes
Router::group(['middleware' => 'auth'], function () {
    Router::get('/profile', [ProfileController::class, 'show']);
    Router::put('/profile', [ProfileController::class, 'update']);
});
```

## Formulaires (Furnace)

Le système d'authentification utilise les composants Furnace pour les formulaires :

```php
use IronFlow\Furnace\Forms\Auth\LoginForm;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login', [
            'form' => new LoginForm()
        ]);
    }
}
```

## Personnalisation

### Guards personnalisés

```php
use IronFlow\Auth\Guards\GuardInterface;

class CustomGuard implements GuardInterface
{
    // Implémentation des méthodes requises
}

// Enregistrement dans config/auth.php
'guards' => [
    'custom' => [
        'driver' => 'custom',
        'provider' => 'users',
    ],
]
```

### Providers OAuth supplémentaires

```php
// Dans config/oauth.php
'providers' => [
    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect_uri' => env('APP_URL') . '/auth/twitter/callback',
    ],
]
```

## Sécurité

- Sessions régénérées après connexion
- Protection CSRF sur les formulaires
- Validation des états OAuth
- Hachage sécurisé des mots de passe
- Rate limiting sur les tentatives de connexion

## Événements

Le système d'authentification déclenche plusieurs événements :

- `UserLoggedIn`
- `UserLoggedOut`
- `UserRegistered`
- `PasswordReset`

## Intégration avec CraftPanel

Le système d'authentification s'intègre avec CraftPanel pour la gestion des permissions :

```php
// Vérification des permissions
if (auth()->user()->can('edit-posts')) {
    // ...
}

// Groupes d'utilisateurs
if (auth()->user()->inGroup('admin')) {
    // ...
}
```
