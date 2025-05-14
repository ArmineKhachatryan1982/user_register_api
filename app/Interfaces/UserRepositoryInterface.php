<?php
namespace App\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function index();
    public function store(array $data): User;
    public function update($id, $data);
    public function findByEmail(string $email): ?User;
}
