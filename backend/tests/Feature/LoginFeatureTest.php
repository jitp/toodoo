<?php

namespace Tests\Feature;

use App\Enums\ParticipantRolesEnum;
use App\Models\TodoList;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class LoginFeatureTest
 *
 * Test login cases. Actual login happens when accessing TodoList show action. A user hash is provided and then user
 * is authenticated
 *
 * @package Tests\Feature
 */
class LoginFeatureTest extends TestCase
{
    use WithFaker;
    use DatabaseMigrations;
    use DatabaseTransactions;

    /**
     * Test a participant can authenticate by providing TodoList unique personal hash
     *
     * @return array
     */
    public function testAuthenticateParticipant()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user, ParticipantRolesEnum::CREATOR);
        $hash = $todoList->participants->first()->participant->hash;

        $response = $this->getJson('/api/todolist/' . $hash);

        $response
            ->assertStatus(200);

        $this->assertArrayHasKey('authorization', $response->headers->all());
    }
}
