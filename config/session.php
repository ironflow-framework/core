<?php

declare(strict_types=1);

return [
   /*
    |--------------------------------------------------------------------------
    | Pilote de session
    |--------------------------------------------------------------------------
    |
    | Cette option contrôle le pilote de session qui sera utilisé pour stocker
    | les données de session. Les options disponibles sont :
    |
    | 'file' - Stockage dans des fichiers
    | 'database' - Stockage dans la base de données
    | 'redis' - Stockage dans Redis
    |
    */
   'driver' => env('SESSION_DRIVER', 'file'),

   /*
    |--------------------------------------------------------------------------
    | Nom du cookie de session
    |--------------------------------------------------------------------------
    |
    | Le nom du cookie qui sera utilisé pour stocker l'ID de session.
    |
    */
   'cookie' => env('SESSION_COOKIE', 'ironflow_session'),

   /*
    |--------------------------------------------------------------------------
    | Durée de vie de la session
    |--------------------------------------------------------------------------
    |
    | Le nombre de minutes pendant lesquelles la session reste active.
    |
    */
   'lifetime' => env('SESSION_LIFETIME', 120),

   /*
    |--------------------------------------------------------------------------
    | Chemin du cookie
    |--------------------------------------------------------------------------
    |
    | Le chemin sur lequel le cookie de session sera disponible.
    |
    */
   'path' => '/',

   /*
    |--------------------------------------------------------------------------
    | Domaine du cookie
    |--------------------------------------------------------------------------
    |
    | Le domaine sur lequel le cookie de session sera disponible.
    |
    */
   'domain' => env('SESSION_DOMAIN', null),

   /*
    |--------------------------------------------------------------------------
    | Sécurité du cookie
    |--------------------------------------------------------------------------
    |
    | Si cette option est définie à true, le cookie de session ne sera envoyé
    | que sur une connexion HTTPS.
    |
    */
   'cookie_secure' => env('SESSION_SECURE_COOKIE', true),

   /*
    |--------------------------------------------------------------------------
    | Protection HTTP Only
    |--------------------------------------------------------------------------
    |
    | Si cette option est définie à true, le cookie de session ne sera pas
    | accessible via JavaScript.
    |
    */
   'cookie_httponly' => true,

   /*
    |--------------------------------------------------------------------------
    | Protection SameSite
    |--------------------------------------------------------------------------
    |
    | Cette option contrôle le comportement du cookie SameSite. Les options
    | disponibles sont :
    |
    | 'lax' - Protection contre les attaques CSRF
    | 'strict' - Protection maximale
    | null - Aucune protection
    |
    */
   'cookie_samesite' => 'lax',

   /*
    |--------------------------------------------------------------------------
    | Stockage des sessions
    |--------------------------------------------------------------------------
    |
    | Cette option définit le chemin où les fichiers de session seront
    | stockés si le pilote 'file' est utilisé.
    |
    */
   'files' => storage_path('framework/sessions'),

   /*
    |--------------------------------------------------------------------------
    | Table de session
    |--------------------------------------------------------------------------
    |
    | Cette option définit le nom de la table qui sera utilisée pour stocker
    | les sessions si le pilote 'database' est utilisé.
    |
    */
   'table' => 'sessions',

   /*
    |--------------------------------------------------------------------------
    | Connexion Redis
    |--------------------------------------------------------------------------
    |
    | Cette option définit la connexion Redis qui sera utilisée pour stocker
    | les sessions si le pilote 'redis' est utilisé.
    |
    */
   'connection' => env('SESSION_CONNECTION', null),
];
