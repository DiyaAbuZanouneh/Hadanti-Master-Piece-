<?php

namespace Database\Seeders\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

trait SeedsDemoData
{
    protected function makeUser(string $name, string $email, string $role, Carbon $at, string $status = 'active'): User
    {
        $user = User::create([
            'name'          => $name,
            'email'         => $email,
            'phone'         => $this->phone(),
            'password_hash' => 'password',
            'role'          => $role,
            'status'        => $status,
        ]);
        $this->backdate($user, $at);

        return $user;
    }

    protected function phone(): string
    {
        return '079' . fake()->numerify('#######');
    }

    protected function past(Carbon $date): Carbon
    {
        return $date->greaterThan(now()) ? now() : $date;
    }

    protected function backdate(Model $model, Carbon $at): void
    {
        $model->forceFill(['created_at' => $at, 'updated_at' => $at])->saveQuietly();
    }
}