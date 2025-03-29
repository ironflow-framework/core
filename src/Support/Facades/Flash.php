<?php

namespace IronFlow\Support\Facades;

class Flash
{
   public static function success(string $message): void
   {
      session()->flash('flash_message', $message);
      session()->flash('flash_type', 'success');
   }

   public static function error(string $message): void
   {
      session()->flash('flash_message', $message);
      session()->flash('flash_type', 'error');
   }

   public static function info(string $message): void
   {
      session()->flash('flash_message', $message);
      session()->flash('flash_type', 'info');
   }

   public static function warning(string $message): void
   {
      session()->flash('flash_message', $message);
      session()->flash('flash_type', 'warning');
   }

   public static function message(string $message, string $type = 'info'): void
   {
      session()->flash('flash_message', $message);
      session()->flash('flash_type', $type);
   }

   public static function getMessages(): array
   {
      return session()->get('flash_messages', []);
   }

   public static function clearMessages(): void
   {
      session()->forget('flash_messages');
      session()->forget('flash_type');
   }
   
}