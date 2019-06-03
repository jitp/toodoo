<?php

namespace Tests\Unit;

use App\Enums\ParticipantRolesEnum;
use App\Enums\TodoListItemStatusEnum;
use App\Exceptions\TodoListException;
use App\Mail\TodoListInvitation;
use App\Mail\TodoListRemovalNotification;
use App\Models\TodoList;
use App\Models\TodoListItem;
use App\Services\TodoList\TodoListService;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Carbon;
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
     * Test todolist creation.
     *
     * @dataProvider todolistCreateProvider
     * @return void
     * @throws \Exception
     */
    public function testCreatingATodoList($data)
    {
        $this->assertDatabaseMissing('todo_lists', [
            'name' => $data['name']
        ]);

        if (isset($data['creator'])) {
            if (is_array($data['creator'])) {
                $this->assertDatabaseMissing('users', $data['creator']);
            } else {
                $this->assertDatabaseMissing('users', [
                    'email' => $data['creator']
                ]);
            }
        }

        if (isset($data['participants'])) {
            if (isset($data['participants'][0])) {
                if (is_array($data['participants'][0])) {
                    foreach ($data['participants'] as $participantData) {
                        $this->assertDatabaseMissing('users', $participantData);
                    }
                } else {
                    foreach ($data['participants'] as $participantData) {
                        $this->assertDatabaseMissing('users', [
                            'email' => $participantData
                        ]);
                    }
                }
            }
        }

        $todoList = $this->todoListService->create($data);

        $this->assertInstanceOf(TodoList::class, $todoList);

        $this->assertDatabaseHas('todo_lists', [
            'name' => $data['name']
        ]);

        if (isset($data['creator'])) {
            if (is_array($data['creator'])) {
                $this->assertDatabaseHas('users', $data['creator']);
                $this->assertSame($data['creator']['email'], $todoList->creator->email);
            } else {
                $this->assertDatabaseHas('users', [
                    'email' => $data['creator']
                ]);
                $this->assertSame($data['creator'], $todoList->creator->email);
            }
        }

        if (isset($data['participants'])) {
            if (isset($data['participants'][0])) {
                if (is_array($data['participants'][0])) {
                    foreach ($data['participants'] as $participantData) {
                        $this->assertDatabaseHas('users', $participantData);
                        $this->assertNotNull($todoList->participants->firstWhere('email', $participantData['email']));
                    }
                } else {
                    foreach ($data['participants'] as $participantData) {
                        $this->assertDatabaseHas('users', [
                            'email' => $participantData
                        ]);
                        $this->assertNotNull($todoList->participants->firstWhere('email', $participantData));
                    }
                }
            }
        }
    }

    /**
     * Test only one creator is allowed when creating a todolist.
     *
     * @return void
     * @throws \Exception
     */
    public function testOneCreatorOnly()
    {
        $this->expectException(TodoListException::class);

        $this->todoListService->create([
            'name' => $this->faker->sentence,
            'creator' => [
                $this->faker->safeEmail,
                $this->faker->safeEmail,
            ]
        ]);
    }

    /**
     * Test repeated participants are only inserted once.
     *
     * @return void
     * @throws \Exception
     */
    public function testNotInsertingDuplicatedParticipants()
    {
        $participantEmail = $this->faker->safeEmail;

        $todoList = $this->todoListService->create([
            'name' => $this->faker->sentence,
            'creator' => [
                $this->faker->safeEmail,
            ],
            'participants' => [
                $participantEmail,
                $participantEmail,
                $participantEmail,
                $participantEmail
            ]
        ]);

        $this->assertCount(2, $todoList->participants);
    }

    /**
     * Test notification emails are sent to participants on todolist creation.
     *
     * @return void
     * @throws \Exception
     */
    public function testEmailNotificationOnTodoListCreation()
    {
        Mail::fake();

        $this->todoListService->create([
            'name' => $this->faker->sentence,
            'creator' => [
                $this->faker->safeEmail,
            ],
            'participants' => [
                $this->faker->safeEmail,
                $this->faker->safeEmail,
            ]
        ]);

        // Assert mail was sent 1 times.
        Mail::assertSent(TodoListInvitation::class, 3);
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
     * Test a todolist is deleted
     *
     * @return void
     * @throws \Exception
     */
    public function testTodoListIsDeleted()
    {
        $todoList = factory(TodoList::class)->create();

        //Assert todolist is deleted given as an instance parameter
        $result = $this->todoListService->delete($todoList);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('todo_lists', [
            'id' => $todoList->id,
            'deleted_at' => null
        ]);

        $todoList = factory(TodoList::class)->create();

        //Assert todolist is deleted given as an integer
        $result = $this->todoListService->delete($todoList->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('todo_lists', [
            'id' => $todoList->id,
            'deleted_at' => null
        ]);
    }

    /**
     * Test an exception is thrown if no valid todolist is given when deleting.
     *
     * @return void
     * @throws \Exception
     */
    public function testTodoListIsNotDeleted()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->todoListService->delete(-1);
    }

    /**
     * Test an email is sent to participants when a todolist is removed.
     *
     * @return void
     * @throws \Exception
     */
    public function testEventNotificationOnTodoListRemoval()
    {
        $todoList = factory(TodoList::class)->create();
        $user = factory(User::class)->create();

        $todoList->addParticipants($user, ParticipantRolesEnum::PARTICIPANT);

        Mail::fake();

        $this->todoListService->delete($todoList);

        // Assert mail was sent 0 times.
        Mail::assertSent(TodoListRemovalNotification::class, 1);

        Mail::assertSent(TodoListRemovalNotification::class, function ($mail) use ($user, $todoList) {
            return $mail->hasTo($user->email) &&
                $mail->todoList->id === $todoList->id;
        });
    }

    /**
     * Test participant removing the todolist is not notified by email.
     *
     * @return void
     */
    public function testNotNotifyingTodoListDeleterOnTodoListRemoval()
    {
        $todoList = factory(TodoList::class)->create();
        $user = factory(User::class)->create();

        $todoList->addParticipants($user, ParticipantRolesEnum::PARTICIPANT);

        Mail::fake();

        $this->todoListService->notifyTodoListRemoval($todoList, $user);

        // Assert mail was sent 0 times.
        Mail::assertSent(TodoListRemovalNotification::class, 0);
    }

    /**
     * Test a non participant User can add items to a todolist.
     *
     * @return void
     */
    public function testNonParticipantUserCanAddItemToList()
    {
        $todoList = factory(TodoList::class)->create();
        $user = factory(User::class)->create();

        $this->expectException(TodoListException::class);

        $this->todoListService->addItemToList($todoList, [
            'name' => $this->faker->sentence
        ], $user);
    }

    /**
     * Test a participant can add an item to the todolist.
     *
     * @return mixed
     */
    public function testParticipantCanAddItemToList()
    {
        $todoList = factory(TodoList::class)->create();
        $user = factory(User::class)->create();

        $todoList->addParticipants($user);

        $this->assertDatabaseMissing('todo_list_items', [
            'todo_list_id' => $todoList->id,
            'user_id' => $user->id
        ]);

        $item = $this->todoListService->addItemToList($todoList, [
            'name' => $this->faker->sentence,
            'status' => TodoListItemStatusEnum::DONE,
            'deadline' => Carbon::yesterday()
        ], $user);

        $this->assertDatabaseHas('todo_list_items', [
            'todo_list_id' => $todoList->id,
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(TodoListItem::class, $item->first());

        return $item;
    }

    /**
     * Test defaults are not changed when adding an item to the todolist.
     *
     * @depends testParticipantCanAddItemToList
     * @param $item
     */
    public function testDefaultsToAddItemToListArentChanged($item)
    {
        $this->assertEquals(TodoListItemStatusEnum::PENDING, $item->first()->status);
        $this->assertEquals(Carbon::today()->addMonth(), $item->first()->deadline);
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
            [
                [
                    'name' => $faker->sentence,
                ]
            ],
            [
                [
                    'name' => $faker->sentence,
                    'creator' => $faker->safeEmail,
                ]
            ],
            [
                [
                    'name' => $faker->sentence,
                    'creator' => [
                        'email' => $faker->safeEmail
                    ],
                ]
            ],
            [
                [
                    'name' => $faker->sentence,
                    'creator' => $faker->safeEmail,
                    'participants' => $this->emails()
                ]
            ],
            [
                [
                    'name' => $faker->sentence,
                    'creator' => $faker->safeEmail,
                    'participants' => [
                        [
                            'email' => $faker->safeEmail
                        ],
                        [
                            'email' => $faker->safeEmail
                        ]
                    ]
                ]
            ],
            [
                [
                    'name' => $faker->sentence,
                    'creator' => $faker->safeEmail,
                    'participants' => []
                ]
            ]
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
