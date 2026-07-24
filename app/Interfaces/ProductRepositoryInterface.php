<?php

namespace App\Interfaces;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function getPaginated(int $perPage = 15);
}
