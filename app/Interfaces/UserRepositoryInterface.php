<?php

namespace App\Interfaces;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email);
    public function findByLogin(string $login);
}
