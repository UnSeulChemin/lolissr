<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\Auth\LoginResult;
use App\Models\User;
use App\Repositories\Auth\UserRepository;

use Framework\Auth\AuthenticationInterface;
use Framework\Http\Session;

final class AuthService implements AuthenticationInterface
{
    private const USERNAME_MAX_LENGTH = 50;

    private const PASSWORD_MIN_LENGTH = 6;
    private const PASSWORD_MAX_BYTES = 72;

    private bool $userResolved = false;

    private ?User $currentUser = null;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly LoginThrottleService $loginThrottleService
    ) {
    }

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

        try
        {
            return $this->userRepository->create($username, $passwordHash);
        }
        catch (\PDOException $exception)
        {
            if ($exception->getCode() === '23000' && ($exception->errorInfo[1] ?? null) === 1062)
            {
                return false;
            }

            throw $exception;
        }
    }

    public function login(
        string $username,
        string $password,
        string $ipAddress
    ): LoginResult {
        $username = trim($username);

        if ($this->loginThrottleService->isLocked($username, $ipAddress))
        {
            return LoginResult::LOCKED;
        }

        $user = $this->userRepository->findByUsername($username);

        if (! $this->hasValidPassword($password) || $user === null || ! password_verify($password, $user->password))
        {
            if ($this->loginThrottleService->recordFailure($username, $ipAddress))
            {
                return LoginResult::LOCKED;
            }

            return LoginResult::INVALID_CREDENTIALS;
        }

        $this->loginThrottleService->clear($username, $ipAddress);
        $this->rehashPasswordIfNeeded($user, $password);

        Session::regenerate();
        Session::remove('csrf_token');
        Session::set('user_id', $user->id);

        $this->userResolved = true;
        $this->currentUser = $user;

        return LoginResult::SUCCESS;
    }

    public function logout(): void
    {
        Session::destroy();

        $this->userResolved = true;
        $this->currentUser = null;
    }

    public function user(): ?User
    {
        if ($this->userResolved)
        {
            return $this->currentUser;
        }

        $this->userResolved = true;

        $userId = Session::get('user_id');

        if (! is_int($userId))
        {
            return null;
        }

        $this->currentUser = $this->userRepository->findById($userId);

        if ($this->currentUser === null)
        {
            Session::remove('user_id');
        }

        return $this->currentUser;
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

        return $usernameLength >= 1
            && $usernameLength <= self::USERNAME_MAX_LENGTH
            && mb_strlen($password) >= self::PASSWORD_MIN_LENGTH
            && $this->hasValidPassword($password);
    }

    private function hasValidPassword(string $password): bool
    {
        // Bcrypt only uses the first 72 bytes, including for multibyte text.
        return $password !== ''
            && strlen($password) <= self::PASSWORD_MAX_BYTES
            && ! str_contains($password, "\0");
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

        $this->userRepository->updatePasswordHash($user->id, $passwordHash);
    }
}
