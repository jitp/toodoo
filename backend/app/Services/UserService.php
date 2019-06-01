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
        $data = $this->prepareData($data);

        return $this->userQueryBuilder()->create($data);
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param array $attributes [attribute => value] to look for in db
     * @param array $values [attribute => value] to be added to $attributes and inserted in db
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function firstOrCreate($attributes, $values = [])
    {
        $data = $this->prepareData($attributes + $values);

        return $this->userQueryBuilder()->firstOrCreate($attributes, $data);
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

    /**
     * Prepare data for creating a User.
     *
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {
        //Provide a default name based on email if none was given
        if (!Arr::has($data, 'name')) {

            $name = $this->getDefaultNameFromEmail(Arr::get($data, 'email', 'john@example.org'));

            Arr::set($data, 'name', $name);
        }

        return $data;
    }
}