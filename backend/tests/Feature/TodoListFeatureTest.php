<?php

namespace Tests\Feature;

use App\Enums\ParticipantRolesEnum;
use App\Mail\TodoListInvitation;
use App\Models\TodoList;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
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
}
