<?php

namespace Tests\Unit;

use App\Services\UserService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class UserServiceTest
 *
 * Tests UserService class
 *
 * @package Tests\Unit
 */
class UserServiceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use WithoutMiddleware;
    use DatabaseMigrations;

    /**
     * @var UserService
     */
    protected $userService = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->userService = app(UserService::class);
    }

    /**
     * Test creating a user from the service.
     *
     * @dataProvider createUserDataProvider
     * @return void
     */
    public function testCreateUser($data)
    {
        $this->userService->create($data);

        if (Arr::has($data, 'name')) {
            $this->assertDatabaseHas('users', [
                'name' => $data['name'],
                'email' => $data['email'],
                'deleted_at' => null
            ]);
        } else {
            $this->assertDatabaseHas('users', [
                'name' => explode('@', $data['email'])[0],
                'email' => $data['email'],
                'deleted_at' => null
            ]);
        }
    }

    /**
     * Test a user can be created if not already in db.
     *
     * @dataProvider createUserDataProvider
     * @return void
     */
    public function testFirstOrCreateUser($data)
    {
        if (Arr::has($data, 'name')) {
            $this->assertDatabaseMissing('users', [
                'name' => $data['name'],
                'email' => $data['email']
            ]);
        } else {
            $this->assertDatabaseMissing('users', [
                'email' => $data['email']
            ]);
        }

        $createdUser = $this->userService->firstOrCreate(Arr::except($data, 'password'), $data);

        if (Arr::has($data, 'name')) {
            $this->assertDatabaseHas('users', [
                'name' => $data['name'],
                'email' => $data['email']
            ]);
        } else {
            $this->assertDatabaseHas('users', [
                'name' => explode('@', $data['email'])[0],
                'email' => $data['email']
            ]);
        }

        $this->assertSame(
            $createdUser->id,
            $this->userService->firstOrCreate(Arr::except($data, 'password'), $data)->id
        );
    }

    /**
     * Provide data for creating Users with name given.
     *
     * @return array
     */
    public function createUserDataProvider()
    {
        $faker = \Faker\Factory::create(\Faker\Factory::DEFAULT_LOCALE);

        return [
            [
                [
                    'name' => $faker->name,
                    'email' => $faker->safeEmail,
                    'password' => $faker->password
                ]
            ],
            [
                [
                    'name' => $faker->name,
                    'email' => $faker->safeEmail,
                    'password' => $faker->password
                ]
            ],
            [
                [
                    'name' => $faker->name,
                    'email' => $faker->safeEmail,
                    'password' => $faker->password
                ]
            ],
            [
                [
                    'email' => $faker->safeEmail,
                    'password' => $faker->password
                ]
            ],
            [
                [
                    'email' => $faker->safeEmail,
                    'password' => $faker->password
                ]
            ],
        ];
    }
}
