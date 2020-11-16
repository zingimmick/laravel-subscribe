<?php

declare(strict_types=1);

namespace Zing\LaravelSubscribe\Tests\Concerns;

use Zing\LaravelSubscribe\Subscription;
use Zing\LaravelSubscribe\Tests\Models\Channel;
use Zing\LaravelSubscribe\Tests\Models\User;
use Zing\LaravelSubscribe\Tests\TestCase;

class SubscriberTest extends TestCase
{
    public function testSubscribe(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->subscribe($channel);
        $this->assertDatabaseHas(
            Subscription::query()->getModel()->getTable(),
            [
                'user_id' => $user->getKey(),
                'subscribable_type' => $channel->getMorphClass(),
                'subscribable_id' => $channel->getKey(),
            ]
        );
    }

    public function testUnsubscribe(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->subscribe($channel);
        $this->assertDatabaseHas(
            Subscription::query()->getModel()->getTable(),
            [
                'user_id' => $user->getKey(),
                'subscribable_type' => $channel->getMorphClass(),
                'subscribable_id' => $channel->getKey(),
            ]
        );
        $user->unsubscribe($channel);
        $this->assertDatabaseMissing(
            Subscription::query()->getModel()->getTable(),
            [
                'user_id' => $user->getKey(),
                'subscribable_type' => $channel->getMorphClass(),
                'subscribable_id' => $channel->getKey(),
            ]
        );
    }

    public function testToggleSubscribe(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->toggleSubscribe($channel);
        $this->assertDatabaseHas(
            Subscription::query()->getModel()->getTable(),
            [
                'user_id' => $user->getKey(),
                'subscribable_type' => $channel->getMorphClass(),
                'subscribable_id' => $channel->getKey(),
            ]
        );
        $user->toggleSubscribe($channel);
        $this->assertDatabaseMissing(
            Subscription::query()->getModel()->getTable(),
            [
                'user_id' => $user->getKey(),
                'subscribable_type' => $channel->getMorphClass(),
                'subscribable_id' => $channel->getKey(),
            ]
        );
    }

    public function testSubscriptions(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->toggleSubscribe($channel);
        self::assertSame(1, $user->subscriptions()->count());
        self::assertSame(1, $user->subscriptions->count());
    }

    public function testHasSubscribed(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->toggleSubscribe($channel);
        self::assertTrue($user->hasSubscribed($channel));
        $user->toggleSubscribe($channel);
        $user->load('subscriptions');
        self::assertFalse($user->hasSubscribed($channel));
    }

    public function testHasNotSubscribed(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        $user->toggleSubscribe($channel);
        self::assertFalse($user->hasNotSubscribed($channel));
        $user->toggleSubscribe($channel);
        self::assertTrue($user->hasNotSubscribed($channel));
    }
}