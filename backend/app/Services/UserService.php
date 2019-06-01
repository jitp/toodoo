<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Arr;

/**
 * Class UserService
 *
 * Provide services for Users.
 *
 * @package App\Services
 */
class UserService extends Service
{
    /**
     * Create in db a new User model.
     *
     * @param array $data
     * @return User
     */
    public function create($data)
    {
        if (!Arr::has($data, 'name')) {

            $name = $this->getDefaultNameFromEmail(Arr::get($data, 'email', 'john@example.org'));

            Arr::set($data, 'name', $name);
        }

        return $this->userQueryBuilder()->create($data);
    }

    /**
     * Get the User model query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function userQueryBuilder()
    {
        return User::query();
    }

    /**
     * Get a default name from an email.
     *
     * @param $email
     * @return mixed
     */
    protected function getDefaultNameFromEmail($email)
    {
        return explode('@', $email)[0];
    }
}