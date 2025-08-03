<?php

namespace IronFlow\Core\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Security service for authentication and authorization
 */
class SecurityManager
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private AuthorizationCheckerInterface $authorizationChecker,
        private PasswordHasher $passwordHasher
    ) {}

    /**
     * Get the current authenticated user
     */
    public function getUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();
        
        if (!$token instanceof TokenInterface) {
            return null;
        }

        $user = $token->getUser();
        
        return $user instanceof UserInterface ? $user : null;
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->getUser() !== null;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->authorizationChecker->isGranted($role);
    }

    /**
     * Check if user has permission for a specific action
     */
    public function isGranted(string $attribute, mixed $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($attribute, $subject);
    }

    /**
     * Deny access if user doesn't have permission
     */
    public function denyAccessUnlessGranted(string $attribute, mixed $subject = null, string $message = 'Access denied'): void
    {
        if (!$this->isGranted($attribute, $subject)) {
            throw new AccessDeniedException($message);
        }
    }

    /**
     * Ensure user is authenticated
     */
    public function denyAccessUnlessAuthenticated(string $message = 'Authentication required'): void
    {
        if (!$this->isAuthenticated()) {
            throw new AuthenticationException($message);
        }
    }

    /**
     * Hash a password
     */
    public function hashPassword(string $plainPassword): string
    {
        return $this->passwordHasher->hash($plainPassword);
    }

    /**
     * Verify a password
     */
    public function verifyPassword(string $hashedPassword, string $plainPassword): bool
    {
        return $this->passwordHasher->verify($hashedPassword, $plainPassword);
    }

    /**
     * Check if password needs rehashing
     */
    public function needsPasswordRehash(string $hashedPassword): bool
    {
        return $this->passwordHasher->needsRehash($hashedPassword);
    }

    /**
     * Get user roles
     */
    public function getUserRoles(): array
    {
        $user = $this->getUser();
        
        return $user ? $user->getRoles() : [];
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user has all of the specified roles
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get the current security token
     */
    public function getToken(): ?TokenInterface
    {
        return $this->tokenStorage->getToken();
    }
}