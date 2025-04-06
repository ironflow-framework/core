<?php

namespace IronFlow\Support\Facades;

use IronFlow\Support\Security\PasswordHasher;

/**
 * Façade pour le hachage de mots de passe
 * 
 * @method static string hash(string $password, array $options = [])
 * @method static bool verify(string $password, string $hash)
 * @method static bool needsRehash(string $hash, array $options = [])
 */
class Password extends Facade
{
   protected static function getFacadeAccessor(): string
   {
      return PasswordHasher::class;
   }
}
