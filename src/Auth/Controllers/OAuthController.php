<?php

declare(strict_types=1);

namespace IronFlow\Auth\Controllers;

use IronFlow\Auth\OAuth\OAuthManager;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Routing\Controller;

class OAuthController extends Controller
{
    private OAuthManager $oauth;

    public function __construct()
    {
        $this->oauth = OAuthManager::getInstance();
    }

    public function redirect(Request $request, string $provider)
    {
        try {
            $provider = $this->oauth->getProvider($provider);
            $authUrl = $provider->getAuthorizationUrl([
                'scope' => ['email', 'profile']
            ]);

            session()->put('oauth2state', $provider->getState());
            return Response::redirect($authUrl);
        } catch (\Exception $e) {
            return Response::redirect('/login')
                ->with('error', 'Unable to connect with ' . ucfirst($provider));
        }
    }

    public function callback(Request $request, string $provider)
    {
        if (!$request->query('code')) {
            return Response::redirect('/login')
                ->with('error', 'Authorization code not received');
        }

        if ($request->query('state') !== session()->get('oauth2state')) {
            session()->remove('oauth2state');
            return Response::redirect('/login')
                ->with('error', 'Invalid state parameter');
        }

        try {
            auth()->attempt([
                'provider' => $provider,
                'code' => $request->query('code')
            ]);

            return Response::redirect('/dashboard');
        } catch (\Exception $e) {
            return Response::redirect('/login')
                ->with('error', 'Unable to authenticate with ' . ucfirst($provider));
        }
    }
}
