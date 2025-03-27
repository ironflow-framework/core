# API Authentication

IronFlow fournit une authentification API robuste via tokens.

## Configuration

### 1. Configuration du guard

```php
// Dans config/auth.php
'guards' => [
    'api' => [
        'driver' => 'token',
        'provider' => 'users',
        'token_key' => 'api_token', // Nom de la colonne dans la table users
    ],
]
```

### 2. Migration

```php
use IronFlow\Database\Schema\Schema;
use IronFlow\Database\Migrations\Migration;

class AddApiTokenToUsers extends Migration
{
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->string('api_token', 80)
                  ->unique()
                  ->nullable();
        });
    }
}
```

## Utilisation

### 1. Génération de token

```php
use IronFlow\Support\Str;

class UserController extends Controller
{
    public function generateToken()
    {
        $token = Str::random(60);
        
        auth()->user()->update([
            'api_token' => hash('sha256', $token),
        ]);
        
        return response()->json([
            'token' => $token,
        ]);
    }
}
```

### 2. Authentification des requêtes

```php
// Via header Bearer
$response = $client->request('GET', '/api/user', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
    ],
]);

// Via query string
$response = $client->get('/api/user?api_token=' . $token);
```

### 3. Protection des routes

```php
// Route unique
Router::get('/api/user', [UserController::class, 'show'])
    ->middleware('auth:api');

// Groupe de routes
Router::group(['middleware' => 'auth:api'], function () {
    Router::get('/api/posts', [PostController::class, 'index']);
    Router::post('/api/posts', [PostController::class, 'store']);
});
```

## Réponses d'erreur

```json
// 401 Unauthorized
{
    "message": "Unauthenticated."
}

// 403 Forbidden
{
    "message": "This action is unauthorized."
}
```

## Bonnes pratiques

1. **Sécurité des tokens** :
   - Utiliser HTTPS
   - Stocker les tokens de manière sécurisée
   - Implémenter une expiration des tokens
   - Permettre la révocation des tokens

2. **Rate Limiting** :
   ```php
   Router::middleware(['auth:api', 'throttle:60,1'])->group(function () {
       Router::get('/api/posts', [PostController::class, 'index']);
   });
   ```

3. **Validation des requêtes** :
   ```php
   public function store(Request $request)
   {
       $validated = $request->validate([
           'title' => 'required|string|max:255',
           'content' => 'required|string',
       ]);
       
       // ...
   }
   ```

## Exemple complet

```php
use IronFlow\Auth\TokenGuard;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function index(Request $request)
    {
        return Response::json([
            'user' => $request->user(),
            'data' => [
                // ...
            ]
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            // ...
        ]);
        
        $data = Model::create($validated);
        
        return Response::json($data, 201);
    }
}
