<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\TodoListItem;
use Faker\Generator as Faker;
use App\Enums\TodoListItemStatusEnum;

$factory->define(TodoListItem::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'order' => 1,
        'status' => TodoListItemStatusEnum::PENDING,
        'deadline' => $faker->dateTimeBetween('now', '+1 month')
    ];
});

$factory->state(TodoListItem::class, TodoListItemStatusEnum::DONE, [
    'status' => TodoListItemStatusEnum::DONE
]);

$factory->state(TodoListItem::class, TodoListItemStatusEnum::PENDING, [
    'status' => TodoListItemStatusEnum::PENDING
]);

$factory->state(TodoListItem::class, TodoListItemStatusEnum::EXPIRED, [
    'status' => TodoListItemStatusEnum::EXPIRED
]);
