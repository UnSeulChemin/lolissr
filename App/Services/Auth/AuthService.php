<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Auth\UserRepository;

use Framework\Support\Session;

final readonly class AuthService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    // =========================================
    // AUTHENTIFICATION
    // =========================================

    public function register(string $username, string $password): bool
    {
        $username = trim($username);

        if ($username === '' || $password === '')
        {
            return false;
        }

        if ($this->userRepository->findByUsername($username) !== null)
        {
            return false;
        }

        return $this->userRepository->create(
            $username,
            password_hash($password, PASSWORD_DEFAULT)
        );
    }

    public function login(string $username, string $password): bool
    {
        $user = $this->userRepository->findByUsername(trim($username));

        if ($user === null || ! password_verify($password, $user->password))
        {
            return false;
        }

        Session::regenerate();
        Session::remove('csrf_token');
        Session::set('user_id', $user->id);

        return true;
    }

    public function logout(): void
    {
        Session::destroy();
    }

    // =========================================
    // UTILISATEUR
    // =========================================

    public function user(): ?User
    {
        $userId = (int) Session::get('user_id', 0);

        if ($userId <= 0)
        {
            return null;
        }

        return $this->userRepository->findById($userId);
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }
}