<?php

namespace Tests\Feature;

use App\Enums\ParticipantRolesEnum;
use App\Mail\TodoListInvitation;
use App\Mail\TodoListRemovalNotification;
use App\Models\TodoList;
use App\Models\TodoListItem;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class TodoListFeatureTest
 *
 * Feature tests TodoList actions.
 *
 * @package Tests\Feature
 */
class TodoListFeatureTest extends TestCase
{
    use WithFaker;
    use DatabaseMigrations;
    use DatabaseTransactions;

    /**
     * Test Happy Path on Create TodoList
     *
     * @return void
     */
    public function testCreateTodoList()
    {
        $data = [
            'name' => $this->faker->sentence,
            'creator' => [
                'email' => $this->faker->safeEmail
            ],
            'participants' => [
                [
                    'email' => $this->faker->safeEmail
                ],
                [
                    'email' => $this->faker->safeEmail
                ],
                [
                    'email' => $this->faker->safeEmail
                ],
            ]
        ];

        $response = $this->postJson('/api/todolist', $data);

        $response
            ->assertStatus(201)
            ->assertJsonFragment($data['creator'])
            ->assertJsonFragment($data['participants'][0])
            ->assertJsonFragment($data['participants'][1])
            ->assertJsonFragment($data['participants'][2])
        ;

    }

    /**
     * Test TodoList can be created with no participants.
     *
     * @return void
     */
    public function testCreateTodoListWithNoParticipants()
    {
        $data = [
            'name' => $this->faker->sentence,
            'creator' => [
                'email' => $this->faker->safeEmail
            ],
        ];

        $response = $this->postJson('/api/todolist', $data);

        $response
            ->assertStatus(201)
            ->assertJsonFragment($data['creator'])
        ;
    }

    /**
     * Test creator is required when creating a TodoList.
     *
     * @return void
     */
    public function testRequiredCreatorOnCreateTodoList()
    {
        $data = [
            'name' => $this->faker->sentence,
        ];

        $response = $this->postJson('/api/todolist', $data);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('creator.email')
        ;
    }

    /**
     * Test name is required when creating TodoList
     */
    public function testRequiredNameFieldOnCreateTodoList()
    {
        $data = [
            'name' => '',
        ];

        $response = $this->postJson('/api/todolist', $data);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('name')
        ;
    }

    /**
     * Test name is not longer than admitted value when creating TodoList
     */
    public function testLongerNameFieldOnCreateTodoList()
    {
        $data = [
            'name' => str_repeat('a', 200),
        ];

        $response = $this->postJson('/api/todolist', $data);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('name')
        ;
    }

    /**
     * Test invitation emails are sent to each participant on TodoList creation.
     *
     * @return void
     */
    public function testMailSentOnTodoListCreation()
    {
        $data = [
            'name' => $this->faker->sentence,
            'creator' => [
                'email' => $this->faker->safeEmail
            ],
            'participants' => [
                [
                    'email' => $this->faker->safeEmail
                ],
                [
                    'email' => $this->faker->safeEmail
                ],
                [
                    'email' => $this->faker->safeEmail
                ],
            ]
        ];

        Mail::fake();

        $response = $this->postJson('/api/todolist', $data);

        $response->assertStatus(201);

        Mail::assertSent(TodoListInvitation::class, 4);

        foreach (array_slice(Arr::flatten($data), 1) as $email) {
            Mail::assertSent(TodoListInvitation::class, function($mail) use ($email) {
                return $mail->hasTo($email);
        });
        }
    }

    /**
     * Test getting a TodoList resource.
     *
     * @return void
     */
    public function testShowTodoList()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user, ParticipantRolesEnum::CREATOR);
        $hash = $todoList->participants->first()->participant->hash;

        $response = $this->getJson('/api/todolist/' . $hash);

        $response
            ->assertStatus(200)
        ;
    }

    /**
     * Test Login User when showing TodoList.
     *
     * @return void
     */
    public function testLoginOnShowTodoList()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user, ParticipantRolesEnum::CREATOR);
        $hash = $todoList->participants->first()->participant->hash;

        $response = $this->getJson('/api/todolist/' . $hash);

        $response
            ->assertStatus(200)
        ;

        $this->assertNotNull(Auth::user());
        $this->assertEquals($user->id, Auth::user()->id);
    }

    /**
     * Test TodoList deletion with email notification included.
     *
     * @return void
     */
    public function testTodoListDelete()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user, ParticipantRolesEnum::CREATOR);

        $users = factory(User::class, 5)->create();

        $todoList->addParticipants($users->all());

        $hash = $todoList->participants->first()->participant->hash;

        Mail::fake();

        $this->actingAs($user, 'api')
            ->deleteJson('/api/todolist/' . $hash)
            ->assertStatus(200)
        ;

        Mail::assertSent(TodoListRemovalNotification::class, 5);

        $users = $users->all();

        foreach ($users as $user) {
            Mail::assertSent(TodoListRemovalNotification::class, function($mail) use ($user) {
                return $mail->hasTo($user->email);
            });
        }
    }

    /**
     * Test invitation to new user to collaborate.
     *
     * @return void
     */
    public function testInviteUserToCollaborate()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user, ParticipantRolesEnum::CREATOR);

        $hash = $todoList->participants->first()->participant->hash;

        $newEmail = $this->faker->safeEmail;

        Mail::fake();

        $this->assertDatabaseMissing('users', [
            'email' => $newEmail
        ]);

        $this->actingAs($user, 'api')
            ->postJson('/api/todolist/' . $hash . '/invite', [
                'participant' => $newEmail
                ])
            ->assertStatus(200)
        ;

        Mail::assertSent(TodoListInvitation::class, 1);

        Mail::assertSent(TodoListInvitation::class, function($mail) use ($newEmail) {
            return $mail->hasTo($newEmail);
        });

        $this->assertNotNull($todoList->participants()->get()->firstWhere('email', $newEmail));

        $this->assertDatabaseHas('users', [
            'email' => $newEmail
        ]);
    }

    /**
     * Test participant can add items to the list.
     *
     * @return void
     */
    public function testParticipantCanAddItemToList()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user);

        $hash = $todoList->participants->first()->participant->hash;

        $this->assertDatabaseMissing('todo_list_items', []);

        $name = $this->faker->sentence;

        $this->actingAs($user, 'api')
            ->postJson('/api/todolist/' . $hash . '/items', [
                'name' => $name
            ])
            ->assertStatus(201)
        ;

        $this->assertDatabaseHas('todo_list_items', [
            'name' => $name,
            'user_id' => $user->id,
            'todo_list_id' => $todoList->id
        ]);
    }

    /**
     * Test 404 error response when deleting an item not belonging to list.
     *
     * @return void
     */
    public function testDeletingItemNotBelongingToList()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user);

        $hash = $todoList->participants->first()->participant->hash;

        $todoListItem = factory(TodoListItem::class)->create([
            'todo_list_id' => 10,
            'user_id' => 10,
        ]);

        $this->assertCount(0, $todoList->items()->get());
        $this->assertDatabaseHas('todo_list_items', [
            'todo_list_id' => 10,
            'user_id' => 10,
        ]);

        $this->actingAs($user, 'api')
            ->deleteJson('/api/todolist/' . $hash . '/items/' . $todoListItem->id)
            ->assertStatus(404)
        ;
    }

    /**
     * Test deleting a list item.
     *
     * @return void
     */
    public function testDeletingItemFromList()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user);

        $hash = $todoList->participants->first()->participant->hash;

        $todoListItem = factory(TodoListItem::class)->create([
            'todo_list_id' => $todoList->id,
            'user_id' => $user->id,
        ]);

        $this->assertCount(1, $todoList->items()->get());

        $this->actingAs($user, 'api')
            ->deleteJson('/api/todolist/' . $hash . '/items/' . $todoListItem->id)
            ->assertStatus(200)
        ;

        $this->assertCount(0, $todoList->items()->get());
    }
}
