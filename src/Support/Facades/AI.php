<?php

namespace IronFlow\Support\Facades;

/**
 * @method static \IronFlow\Framework\AI\AIProvider provider(?string $provider = null)
 * @method static string generate(string $prompt, array $options = [], ?string $provider = null)
 * @method static array completion(string $prompt, array $options = [], ?string $provider = null)
 * @method static array chat(array $messages, array $options = [], ?string $provider = null)
 */
class AI extends Facade
{
   /**
    * Get the registered name of the component.
    *
    * @return string
    */
   protected static function getFacadeAccessor(): string
   {
      return 'ai';
   }

}
