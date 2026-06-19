<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Message;
use App\Models\Reaction;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Пользователи
        $alice = User::firstOrCreate(
            ['email' => 'alice@meetly.test'],
            ['name' => 'Alice', 'password' => Hash::make('password')],
        );

        $bob = User::firstOrCreate(
            ['email' => 'bob@meetly.test'],
            ['name' => 'Bob', 'password' => Hash::make('password')],
        );

        // Пространства
        $public = Workspace::create([
            'owner_id'     => $alice->id,
            'name'         => 'General',
            'visibility'   => 'public',
            'invite_token' => null,
        ]);

        $private = Workspace::create([
            'owner_id'     => $alice->id,
            'name'         => 'Team Alice',
            'visibility'   => 'private',
            'invite_token' => Str::random(32),
        ]);

        // Участники
        $public->members()->attach($alice->id);
        $private->members()->attach($alice->id);

        $public->members()->attach($bob->id);

        // Каналы
        $general  = $public->channels()->create(['name' => 'general']);
        $random   = $public->channels()->create(['name' => 'random']);
        $teamChan = $private->channels()->create(['name' => 'planning']);

        // Сообщения
        $m1 = $general->messages()->create(['user_id' => $alice->id, 'body' => 'Hello there!']);
        $m2 = $general->messages()->create(['user_id' => $bob->id,   'body' => 'Hi, everyone!']);
        $general->messages()->create(['user_id' => $alice->id, 'body' => 'General chat']);
        $random->messages()->create(['user_id' => $bob->id,    'body' => 'Casual chat']);
        $teamChan->messages()->create(['user_id' => $alice->id, 'body' => 'Our private chat']);

        // Реакции
        Reaction::create(['message_id' => $m1->id, 'user_id' => $alice->id, 'emoji' => 'like']);
        Reaction::create(['message_id' => $m1->id, 'user_id' => $bob->id,   'emoji' => 'party']);
        Reaction::create(['message_id' => $m2->id, 'user_id' => $alice->id, 'emoji' => 'love']);
    }
}