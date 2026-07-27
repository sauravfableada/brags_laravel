<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByLogin(string $login)
    {
        return $this->model->where('email', $login)->orWhere('username', $login)->first();
    }

    public function findByPhone(string $phone)
    {
        return $this->model->whereHas('detail', function ($query) use ($phone) {
            $query->where('phone', $phone);
        })->first();
    }
}
