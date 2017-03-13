<?php

namespace App\Services\Social;

interface Token
{
    public function isExpired();

    public function info();

    public function getScopes();

    public function setScopes(array $scopes);
}
