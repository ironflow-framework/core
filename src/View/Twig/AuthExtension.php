<?php 

namespace IronFlow\View\Twig;

use IronFlow\Database\Model;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AuthExtension extends AbstractExtension
{
    public function getFunctions(){
        return [
            new TwigFunction("auth", [$this,"auth"]),
            new TwigFunction("guest", [$this,"guest"]),
            new TwigFunction("user", [$this,"user"]),
        ];
    }

    public function auth(): bool {
        return \IronFlow\Auth\AuthManager::getInstance()->user() !== null;
    }

    public function guest(): bool {
        return \IronFlow\Auth\AuthManager::getInstance()->user() === null;
    }

    public function user(): ?Model {
        return \IronFlow\Auth\AuthManager::getInstance()->user();
    }
 
}