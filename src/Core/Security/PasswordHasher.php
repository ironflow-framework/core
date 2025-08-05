<?php

namespace IronFlow\Core\Security;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use InvalidArgumentException;

/**
 * Password hashing service using Symfony Password Hasher
 */
class PasswordHasher
{
    private PasswordHasherInterface $hasher;

    public function __construct()
    {
        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'auto'],
            'memory-hard' => ['algorithm' => 'argon2i'],
            'legacy' => ['algorithm' => 'bcrypt'],
        ]);

        $this->hasher = $factory->getPasswordHasher('common');
    }

    /**
     * Hash a password
     */
    public function hash(string $plainPassword): string
    {
        if (empty($plainPassword)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        return $this->hasher->hash($plainPassword);
    }

    /**
     * Verify a password against its hash
     */
    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        if (empty($hashedPassword) || empty($plainPassword)) {
            return false;
        }

        return $this->hasher->verify($hashedPassword, $plainPassword);
    }

    /**
     * Check if a password needs to be rehashed
     */
    public function needsRehash(string $hashedPassword): bool
    {
        return $this->hasher->needsRehash($hashedPassword);
    }
}
