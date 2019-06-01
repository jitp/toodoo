<?php

namespace Tests\Unit;

use App\Enums\ParticipantRolesEnum;
use App\Models\TodoList;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class TodoListTest
 *
 * Tests related to TodoList model.
 *
 * @package Tests\Unit
 */
class TodoListTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use WithoutMiddleware;
    use DatabaseMigrations;

    /**
     * Test creator instance is accessible.
     *
     * @return void
     */
    public function testHasCreatorInstance()
    {
        $todolist = factory(TodoList::class)->create();
        $user = factory(User::class)->create();

        $todolist->creators()->sync([$user->id => [
            'hash' => $this->faker->sha256,
            'role' => ParticipantRolesEnum::CREATOR
        ]]);

        $this->assertInstanceOf(User::class, $todolist->creator);
        $this->assertSame($user->id, $todolist->creator->id);
    }

    /**
     * Test todolist returns null when no creator is defined
     *
     * return @void
     */
    public function testHasNullCreator()
    {
        $todolist = factory(TodoList::class)->create();

        $this->assertNull($todolist->creator);
    }
}
