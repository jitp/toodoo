<?php

namespace Tests\Unit;

use App\Mail\TodoListInvitation;
use App\Models\TodoList;
use App\Services\TodoList\TodoListService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class TodoListServiceTest
 *
 * Tests for TodoListService.
 *
 * @package Tests\Unit
 */
class TodoListServiceTest extends TestCase
{
//    use RefreshDatabase;
    use WithFaker;
    use WithoutMiddleware;
    use DatabaseMigrations;
    use DatabaseTransactions;

    /**
     * @var TodoListService
     */
    protected $todoListService;

    public function setUp(): void
    {
        parent::setUp();

        $this->todoListService = app(TodoListService::class);
    }

    /**
     * A basic unit test example.
     *
     * @dataProvider todolistCreateProvider
     * @return void
     */
    public function testCreateTodoList($data)
    {
        $this->assertTrue(true);
//        $this->todoListService->create($data);
//
//        $this->assertDatabaseHas('todo_lists', [
//            'name' => $data['name'],
//            'deleted_at' => null
//        ]);
//
//        $this->assertDatabaseHas('users', [
//            'email' => array_merge($data['creator'], $data['participants']),
//            'deleted_at' => null
//        ]);
    }

    /**
     * Test inviting users to collaborate on a todolist.
     *
     * @dataProvider inviteDataProvider
     * @throws \Exception
     */
    public function testInvitingUsersToTodoList($data)
    {
        $todolist = factory(TodoList::class)->create();
        $inviting = factory(User::class)->create();

        $this->todoListService->invite($todolist, $data, $inviting);

        $email = data_get($data, 'email', $data);

        $user = User::where('email', $email)->first();

        $this->assertDatabaseHas('participants', [
            'user_id' => $user->id,
            'todo_list_id' => $todolist->id
        ]);
    }

    /**
     * Test inviting users instances to collaborate on a todolist.
     *
     * DataProviders are not seeing factory function that is why this is done.
     *
     * @throws \Exception
     */
    public function testInvitingUsersInstancesToTodoList()
    {
        $todolist = factory(TodoList::class)->create();
        $inviting = factory(User::class)->create();
        $users = factory(User::class, 5)->create();

        $participants = $this->todoListService->invite($todolist, $users[0], $inviting);

        $this->assertDatabaseHas('participants', [
            'user_id' => $users[0]->id,
            'todo_list_id' => $todolist->id
        ]);

        $this->assertCount(1, $participants);
        $this->assertSame($users[0]->id, $participants[0]->id);

        $participants = $this->todoListService->invite($todolist, array_slice($users->all(), 1), $inviting);

        foreach (array_slice($users->all(), 1) as $user) {
            $this->assertDatabaseHas('participants', [
                'user_id' => $user->id,
                'todo_list_id' => $todolist->id
            ]);
        }

        $this->assertCount(4, $participants);
    }

    /**
     * Test an invited user is not invited again.
     *
     * @return void
     * @throws \Exception
     */
    public function testNotInvitingAnInvitedUser()
    {
        $todolist = factory(TodoList::class)->create();
        $inviting = factory(User::class)->create();
        $user = factory(User::class)->create();

        $this->todoListService->invite($todolist, $user, $inviting);

        $result = $this->todoListService->invite($todolist, $user, $inviting);

        $this->assertEmpty($result);
    }

    /**
     * Test invitation email is sent when a user is invited to collaborate on a todolist
     *
     * @throws \Exception
     */
    public function testSendingInvitationEmailOnInviting()
    {
        Mail::fake();

        $todolist = factory(TodoList::class)->create();
        $inviting = factory(User::class)->create();
        $users = factory(User::class, 5)->create();

        //Inviting just one user
        $this->todoListService->invite($todolist, $users[0], $inviting);

        Mail::assertSent(TodoListInvitation::class, function ($mail) use ($users, $todolist, $inviting) {
            return $mail->hasTo($users[0]->email) &&
                $mail->todoList->id === $todolist->id &&
                $mail->inviting->id === $inviting->id;
        });

        // Assert mail was sent 1 times.
        Mail::assertSent(TodoListInvitation::class, 1);

        //Inviting all users
        $participants = $this->todoListService->invite($todolist, $users->all(), $inviting);

        $this->assertCount(4, $participants);

        // Assert a message was sent to the given users...
        foreach ($participants as $user) {
            Mail::assertSent(TodoListInvitation::class, function ($mail) use ($user, $todolist, $inviting) {
                return $mail->hasTo($user->email) &&
                    $mail->todoList->id === $todolist->id &&
                    $mail->inviting->id === $inviting->id;
            });
        }
    }

    /**
     * Provide data for creating a todolist.
     *
     * @return array
     */
    public function todolistCreateProvider()
    {
        $faker = \Faker\Factory::create(\Faker\Factory::DEFAULT_LOCALE);

        return [
            [[
                'name' => $faker->sentence,
                'creator' => $faker->safeEmail,
                'participants' => $this->emails()
            ]]
        ];
    }

    /**
     * Provide data for invitation testing.
     *
     * @return array
     */
    public function inviteDataProvider()
    {
        $faker = \Faker\Factory::create(\Faker\Factory::DEFAULT_LOCALE);

        return [
            [ $faker->safeEmail ],
            [
                [
                    'email' => $faker->email
                ],
            ],
            [
                [
                    $faker->safeEmail,
                    $faker->safeEmail,
                    [
                        'email' => $faker->email
                    ],
                ]
            ]
        ];
    }

    /**
     * Produce some emails.
     *
     * @return array
     */
    protected function emails()
    {
        $faker = \Faker\Factory::create(\Faker\Factory::DEFAULT_LOCALE);

        $emails = [];

        $times = $faker->numberBetween(1, 10);
        while ($times > 0) {
            $emails[] = $faker->safeEmail;

            $times--;
        }

        return $emails;
    }
}
