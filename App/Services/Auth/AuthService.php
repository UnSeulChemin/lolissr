<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Auth\UserRepository;

use Framework\Support\Session;

final readonly class AuthService
{
    private const USERNAME_MAX_LENGTH = 50;

    private const PASSWORD_MIN_LENGTH = 6;
    private const PASSWORD_MAX_LENGTH = 1024;

    public function __construct(
        private UserRepository $userRepository
    ) {}

    // =========================================
    // AUTHENTIFICATION
    // =========================================

    public function register(string $username, string $password): bool
    {
        $username = trim($username);

        if (! $this->hasValidCredentials($username, $password))
        {
            return false;
        }

        if ($this->userRepository->findByUsername($username) !== null)
        {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($passwordHash === false)
        {
            return false;
        }

        return $this->userRepository->create($username, $passwordHash);
    }

    public function login(string $username, string $password): bool
    {
        $user = $this->userRepository->findByUsername(trim($username));

        if ($user === null || ! password_verify($password, $user->password))
        {
            return false;
        }

        $this->rehashPasswordIfNeeded($user, $password);

        Session::regenerate();
        Session::remove('csrf_token');
        Session::set('user_id', $user->id);

        return true;
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function user(): ?User
    {
        $userId = Session::get('user_id');

        if (! is_int($userId))
        {
            return null;
        }

        return $this->userRepository->findById($userId);
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    // =========================================
    // VALIDATION
    // =========================================

    private function hasValidCredentials(string $username, string $password): bool
    {
        $usernameLength = mb_strlen($username);
        $passwordLength = mb_strlen($password);

        return $usernameLength >= 1
            && $usernameLength <= self::USERNAME_MAX_LENGTH
            && $passwordLength >= self::PASSWORD_MIN_LENGTH
            && $passwordLength <= self::PASSWORD_MAX_LENGTH;
    }

    // =========================================
    // MOT DE PASSE
    // =========================================

    private function rehashPasswordIfNeeded(User $user, string $password): void
    {
        if (! password_needs_rehash($user->password, PASSWORD_DEFAULT))
        {
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($passwordHash === false)
        {
            return;
        }

        $this->userRepository->updatePasswordHash($user->id, $passwordHash);
    }
}