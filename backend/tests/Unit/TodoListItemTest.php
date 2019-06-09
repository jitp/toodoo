<?php

namespace Tests\Unit;

use App\Enums\TodoListItemStatusEnum;
use App\Models\TodoList;
use App\Models\TodoListItem;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class TodoListItemTest
 *
 * Tests related to TodoListItem
 *
 * @package Tests\Unit
 */
class TodoListItemTest extends TestCase
{
    use WithFaker;
    use WithoutMiddleware;
    use DatabaseMigrations;
    use DatabaseTransactions;

    /**
     * Test a TodoItemList is expired.
     *
     * @return TodoListItem
     */
    public function testIsExpired()
    {
        $todoList = factory(TodoList::class)->create();
        $user = factory(User::class)->create();

        $todoList->addParticipants($user);

        $todoListItem = factory(TodoListItem::class)->create([
            'todo_list_id' => $todoList->id,
            'user_id' => $user->id,
            'deadline' => Carbon::today()->subMonths(5)
        ]);

        $this->assertTrue($todoListItem->isExpired());

        return $todoListItem->first();
    }

    /**
     * Test a TodoListItem is not expired when deadline is null
     *
     * @return void
     */
    public function testIsNotExpiredWhenNoDeadlineIsSet()
    {
        $todoList = factory(TodoList::class)->create();
        $user = factory(User::class)->create();

        $todoList->addParticipants($user);

        $todoListItem = factory(TodoListItem::class)->create([
            'todo_list_id' => $todoList->id,
            'user_id' => $user->id,
            'deadline' => null
        ]);

        $this->assertNull($todoListItem->deadline);

        $this->assertFalse($todoListItem->isExpired());
    }

    /**
     * Test a TodoListItem is not expired.
     *
     * @return TodoListItem
     */
    public function testIsNotExpired()
    {
        $todoList = factory(TodoList::class)->create();
        $user = factory(User::class)->create();

        $todoList->addParticipants($user);

        $todoListItem = factory(TodoListItem::class)->create([
            'todo_list_id' => $todoList->id,
            'user_id' => $user->id,
            'deadline' => Carbon::today()->addMonths(5)
        ]);

        $this->assertFalse($todoListItem->isExpired());

        return $todoListItem->first();
    }

    /**
     * Test marking item as done.
     *
     * @depends testIsNotExpired
     * @param TodoListItem $item
     * @return TodoListItem
     */
    public function testMarkingItemAsDone($item)
    {
        $this->assertEquals(TodoListItemStatusEnum::DONE, $item->markDone(false)->status);

        return $item;
    }

    /**
     * Test marking item as done and persisting.
     *
     * @depends testIsNotExpired
     * @param TodoListItem $item
     * @return TodoListItem
     */
    public function testMarkingItemAsDoneAndPersisting($item)
    {
        $this->assertEquals(TodoListItemStatusEnum::DONE, $item->markDone()->status);

        return $item;
    }

    /**
     * Test marking item as pending.
     *
     * @depends testMarkingItemAsDone
     * @param TodoListItem $item
     * @return void
     */
    public function testMarkingItemAsPending($item)
    {
        $this->assertEquals(TodoListItemStatusEnum::PENDING, $item->markPending(false)->status);
    }

    /**
     * Test marking item as pending and persisting.
     *
     * @depends testMarkingItemAsDoneAndPersisting
     * @param TodoListItem $item
     * @return void
     */
    public function testMarkingItemAsPendingAndPersisting($item)
    {
        $this->assertEquals(TodoListItemStatusEnum::PENDING, $item->markPending()->status);
    }
}
