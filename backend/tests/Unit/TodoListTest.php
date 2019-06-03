<?php

namespace Tests\Unit;

use App\Enums\ParticipantRolesEnum;
use App\Enums\TodoListItemStatusEnum;
use App\Models\TodoList;
use App\Models\TodoListItem;
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
     * Test users are related to todolist through participants relation.
     *
     */
    public function testAddParticipants()
    {
        $todolist = factory(TodoList::class)->create();

        $users = factory(User::class, 5)->create();

        $todolist->addParticipants($users->all());

        foreach ($users as $user) {
            $this->assertDatabaseHas('participants', [
                'user_id' => $user->id,
                'todo_list_id' => $todolist->id,
                'role' => ParticipantRolesEnum::PARTICIPANT,
            ]);
        }
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

    /**
     * Test a given user is the creator of the todolist.
     *
     * @return void
     */
    public function testUserIsTodoListCreator()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user, ParticipantRolesEnum::CREATOR);

        $this->assertTrue($todoList->isCreator($user));
        $this->assertTrue($todoList->isCreator($user->id));
    }

    /**
     * Test a given user is not the creator of the todolist.
     *
     * @return void
     */
    public function testUserIsNotTodoListCreator()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user, ParticipantRolesEnum::PARTICIPANT);

        $this->assertFalse($todoList->isCreator($user));
        $this->assertFalse($todoList->isCreator($user->id));
    }

    /**
     * Test single item addition to TodoList
     *
     * @return void
     */
    public function testAddSingleItemToList()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $this->assertDatabaseMissing('todo_list_items', [
            'todo_list_id' => $todoList->id,
            'user_id' => $user->id
        ]);

        $todoListItem = $todoList->addItems([
            [
                'name' => $this->faker->sentence,
                'user_id' => $user->id,
                'status' => TodoListItemStatusEnum::PENDING,
                'deadline' => $this->faker->dateTimeBetween('now', '+1 month')
            ]
        ]);

        $this->assertDatabaseHas('todo_list_items', [
            'todo_list_id' => $todoList->id,
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(TodoListItem::class, $todoListItem->first());
        $this->assertEquals($user->id, $todoListItem->first()->user_id);
        $this->assertEquals($todoList->id, $todoListItem->first()->todo_list_id);
    }

    /**
     * Test adding multiple items to TodoList
     *
     * @return void
     */
    public function testAddMultipleItemsToList()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $items = $todoListItem = $todoList->addItems([
            [
                'name' => $this->faker->sentence,
                'user_id' => $user->id,
                'status' => TodoListItemStatusEnum::PENDING,
                'deadline' => $this->faker->dateTimeBetween('now', '+1 month')
            ],
            [
                'name' => $this->faker->sentence,
                'user_id' => $user->id,
                'status' => TodoListItemStatusEnum::PENDING,
                'deadline' => $this->faker->dateTimeBetween('now', '+1 month')
            ],
            [
                'name' => $this->faker->sentence,
                'user_id' => $user->id,
                'status' => TodoListItemStatusEnum::PENDING,
                'deadline' => $this->faker->dateTimeBetween('now', '+1 month')
            ],
            [
                'name' => $this->faker->sentence,
                'user_id' => $user->id,
                'status' => TodoListItemStatusEnum::PENDING,
                'deadline' => $this->faker->dateTimeBetween('now', '+1 month')
            ],
        ]);

        $this->assertCount(4, $items);
    }

    /**
     * Test a User is participant of the list.
     *
     * @return void
     */
    public function testUserIsParticipant()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user);

        $this->assertTrue($todoList->isParticipant($user));
    }

    /**
     * Test a User is not participant of the list.
     *
     * @return void
     */
    public function testUserIsNotAParticipant()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $this->assertFalse($todoList->isParticipant($user));
    }

    /**
     * Test the given set of ids is the right TodoList complete items' ids
     *
     * @return TodoList
     */
    public function testIsWholeSetOfItemIds()
    {
        $user = factory(User::class)->create();
        $todoList = factory(TodoList::class)->create();

        $todoList->addParticipants($user);

        $items = factory(TodoListItem::class, 5)->create([
            'todo_list_id' => $todoList->id,
            'user_id' => $user->id,
        ]);

        $this->assertTrue($todoList->isWholeSetOfItemIds($items->pluck('id')->all()));

        return $todoList;
    }

    /**
     * Test more ids than real ones in a TodoList are given to isWholeSetOfItemIds.
     *
     * @depends testIsWholeSetOfItemIds
     * @param TodoList $todoList
     * @return void
     */
    public function testMoreItemsThanRealsGivenToIsWholeSetOfItemIds($todoList)
    {
        $this->assertFalse($todoList->isWholeSetOfItemIds([1,2,3,4,5,6]));
    }

    /**
     * Test less ids than real ones in a TodoList are given to isWholeSetOfItemIds.
     *
     * @depends testIsWholeSetOfItemIds
     * @param TodoList $todoList
     * @return void
     */
    public function testLessItemsThanRealsGivenToIsWholeSetOfItemIds($todoList)
    {
        $this->assertFalse($todoList->isWholeSetOfItemIds([1,2,3,4]));
    }
}
