<?php

namespace App\Support\Repositories;

use App\Models\User;

class UserRepository
{
    public function create(array $data):User
    {
        return User::query()->create($data);

    }

    public function findByEmail(string $email):?User
    {
        return User::query()->where('email',$email)->first();
    }

    public function update(string $id, array $data):bool|int
    {
        return User::query()->where('id',$id)->update($data);
    }
}
