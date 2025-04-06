<?php

namespace IronFlow\Channel\Providers;

class SocketIOProvider extends WebSocketProvider
{
   public function __construct()
   {
      parent::__construct();
   }

   public function connect(): bool
   {
      return parent::connect();
   }

   public function disconnect(): bool
   {
      return parent::disconnect();
   }
}
