<?php

namespace App\Auth;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class ApiSessionUserProvider implements UserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        if ($identifier === null || $identifier === '') {
            return null;
        }

        return new GenericUser([
            'id' => (string) $identifier,
            'name' => (string) $identifier,
            'email' => (string) $identifier,
        ]);
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        //
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return false;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        //
    }
}
