<?php

namespace IronFlow\Channel\Traits;

use IronFlow\Support\Facades\Channel;

trait HasChannels
{
    public function broadcast(string $channel): Channel
    {
        return Channel::broadcast($channel);
    }

    public function subscribe(string $channel): void
    {
        Channel::subscribe($channel);
    }

    public function unsubscribe(string $channel): void
    {
        Channel::unsubscribe($channel);
    }
}
