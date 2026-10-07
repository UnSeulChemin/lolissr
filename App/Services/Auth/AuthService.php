<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTO\Auth\LoginIdentityData;
use App\Enums\Auth\LoginResult;
use App\Models\User\User;
use App\Repositories\Auth\UserRepository;

use Framework\Auth\AuthenticationInterface;
use Framework\Http\Session;

final class AuthService implements AuthenticationInterface
{
    private const USERNAME_MAX_LENGTH = 50;

    public const PASSWORD_MIN_LENGTH = 12;
    private const PASSWORD_MAX_BYTES = 72;

    // Precomputed bcrypt hash at PHP 8.3's PASSWORD_DEFAULT cost; never generate it during login.
    private const DUMMY_PASSWORD_HASH = '$2y$10$2Oszd4ZDk5JcFODRhMc4Ie4spNqeAiVKTUILcPiwjuyq3jBUDhPK6';

    private bool $userResolved = false;

    private ?User $currentUser = null;

    private ?LoginIdentityData $loginIdentity = null;
    private string $loginIpAddress = '';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly LoginThrottleService $loginThrottleService
    )
    {
    }

    // =================================================
    // AUTHENTIFICATION
    // =================================================

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

    public function login(string $username, string $password, string $ipAddress): LoginResult
    {
        $username = trim($username);

        $identity = $this->loginThrottleService->resolveIdentity($username);
        $this->loginIdentity = $identity;
        $this->loginIpAddress = $ipAddress;

        if ($this->loginThrottleService->isLocked($identity, $ipAddress))
        {
            return LoginResult::LOCKED;
        }

        $user = $identity->user;

        $passwordMatches = $this->hasValidPassword($password)
            && password_verify($password, $user === null ? self::DUMMY_PASSWORD_HASH : $user->password);

        if (! $passwordMatches || $user === null)
        {
            if ($this->loginThrottleService->recordFailure($identity, $ipAddress))
            {
                return LoginResult::LOCKED;
            }

            return LoginResult::INVALID_CREDENTIALS;
        }

        $this->loginThrottleService->clear($identity, $ipAddress);
        $this->rehashPasswordIfNeeded($user, $password);

        Session::regenerate();
        Session::remove('csrf_token');
        Session::set('user_id', $user->id);

        $this->userResolved = true;
        $this->currentUser = $user;

        return LoginResult::SUCCESS;
    }

    public function remainingLoginLockMinutes(): int
    {
        return $this->loginIdentity === null ? 0
            : $this->loginThrottleService->remainingLockMinutes($this->loginIdentity, $this->loginIpAddress);
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

    // =================================================
    // VALIDATION
    // =================================================

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
        // Bcrypt utilise uniquement les 72 premiers octets, même pour du texte multioctet.
        return $password !== ''
            && strlen($password) <= self::PASSWORD_MAX_BYTES
            && ! str_contains($password, "\0");
    }

    // =================================================
    // MOT DE PASSE
    // =================================================

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
