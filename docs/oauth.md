# OAuth Authentication

IronFlow intègre un système complet d'authentification OAuth2 basé sur la bibliothèque League OAuth2.

## Configuration

### 1. Installation

Le support OAuth est installé automatiquement avec la commande :
```bash
php forge auth:install
```

### 2. Configuration des providers

Dans votre fichier `.env` :
```env
# Google
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret

# GitHub
GITHUB_CLIENT_ID=your-client-id
GITHUB_CLIENT_SECRET=your-client-secret

# Facebook
FACEBOOK_CLIENT_ID=your-client-id
FACEBOOK_CLIENT_SECRET=your-client-secret
```

Dans `config/oauth.php` :
```php
'providers' => [
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('APP_URL') . '/auth/google/callback',
    ],
    // ...
]
```

## Utilisation

### 1. Routes

```php
// Dans routes/web.php
Router::get('/auth/{provider}', [OAuthController::class, 'redirect']);
Router::get('/auth/{provider}/callback', [OAuthController::class, 'callback']);
```

### 2. Liens dans les vues

```html
<div class="social-auth">
    <a href="/auth/google" class="btn btn-google">
        Se connecter avec Google
    </a>
    
    <a href="/auth/github" class="btn btn-github">
        Se connecter avec GitHub
    </a>
    
    <a href="/auth/facebook" class="btn btn-facebook">
        Se connecter avec Facebook
    </a>
</div>
```

### 3. Personnalisation du processus

```php
use IronFlow\Auth\OAuth\OAuthManager;

class CustomOAuthController extends OAuthController
{
    public function redirect(Request $request, string $provider)
    {
        $provider = $this->oauth->getProvider($provider);
        
        // Personnalisation des scopes
        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['email', 'profile', 'custom-scope']
        ]);
        
        session()->put('oauth2state', $provider->getState());
        return redirect($authUrl);
    }
}
```

## Ajout de nouveaux providers

### 1. Configuration

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

### 2. Implémentation

```php
use League\OAuth2\Client\Provider\AbstractProvider;

class TwitterProvider extends AbstractProvider
{
    // Implémentation spécifique à Twitter
}

// Dans OAuthManager
protected function createProvider(string $name): AbstractProvider
{
    return match ($name) {
        'twitter' => new TwitterProvider([
            'clientId' => $config['client_id'],
            'clientSecret' => $config['client_secret'],
            'redirectUri' => $config['redirect_uri'],
        ]),
        // ...
    };
}
```

## Sécurité

1. **Validation des états** :
   - Protection contre les attaques CSRF
   - Vérification de l'état OAuth2

2. **Gestion des tokens** :
   - Stockage sécurisé
   - Rafraîchissement automatique
   - Révocation des tokens

3. **Bonnes pratiques** :
   - Utilisation de HTTPS
   - Validation des données utilisateur
   - Rate limiting sur les requêtes
