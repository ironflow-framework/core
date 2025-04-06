<?php

declare(strict_types=1);

namespace IronFlow\Auth\Controllers;

use App\Models\User;
use IronFlow\Auth\OAuth\OAuthManager;
use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

class OAuthController extends Controller
{
    private OAuthManager $oauth;

    public function __construct()
    {
        $this->oauth = OAuthManager::getInstance();
    }

    public function redirectTo(Request $request, string $provider): Response
    {
        try {
            $provider = $this->oauth->getProvider($provider);
            $authUrl = $provider->getAuthorizationUrl([
                'scope' => ['email', 'profile']
            ]);

            session()->set('oauth2state', $provider->getState());
            return Response::redirect($authUrl);
        } catch (\Exception $e) {
            return Response::redirect('/login')
                ->with(['error' => 'Unable to connect with ' . ucfirst($provider)]);
        }
    }

    public function callback(Request $request, string $provider): Response
    {
        if (!$request->get('code')) {
            return Response::redirect('/login')
                ->with(['error' => 'Authorization code not received']);
        }

        if ($request->get('state') !== session()->get('oauth2state')) {
            session()->remove('oauth2state');
            return Response::redirect('/login')
                ->with(['error' => 'Invalid state parameter']);
        }

        try {
            $user = auth()->user();
            if (!$user instanceof User) {
                return Response::redirect('/login')
                    ->with(['error' => 'Unauthorized access']);
            }

            auth()->attempt([
                'provider' => $provider,
                'code' => $request->get('code')
            ]);

            return Response::redirect('/dashboard');
        } catch (\Exception $e) {
            return Response::redirect('/login')
                ->with(['error' => 'Unable to authenticate with ' . ucfirst($provider)]);
        }
    }
}
