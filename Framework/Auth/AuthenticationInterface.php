<?php

declare(strict_types=1);

namespace Framework\Auth;

interface AuthenticationInterface
{
    public function check(): bool;
}