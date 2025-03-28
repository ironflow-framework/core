<?php

declare(strict_types=1);

namespace Tests\Hammer;

use PHPUnit\Framework\TestCase;
use IronFlow\Hammer\Hammer;
use IronFlow\Hammer\Drivers\FileDriver;

class HammerTest extends TestCase
{
    private string $testCachePath;
    private Hammer $hammer;

    protected function setUp(): void
    {
        // Créer un répertoire de cache temporaire pour les tests
        $this->testCachePath = sys_get_temp_dir() . '/ironflow_hammer_test_' . uniqid();
        mkdir($this->testCachePath, 0777, true);
        
        // Initialiser Hammer avec le FileDriver pour les tests
        $driver = new FileDriver($this->testCachePath);
        $this->hammer = Hammer::getInstance($driver);
    }

    protected function tearDown(): void
    {
        // Nettoyer le répertoire de cache temporaire
        $this->hammer->flush();
        
        // Supprimer le répertoire de test
        $files = glob($this->testCachePath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->testCachePath);
    }

    public function testPutAndGet(): void
    {
        // Tester la mise en cache d'une valeur simple
        $this->hammer->put('test_key', 'test_value');
        $this->assertEquals('test_value', $this->hammer->get('test_key'));
        
        // Tester la mise en cache d'un tableau
        $array = ['name' => 'John', 'age' => 30];
        $this->hammer->put('test_array', $array);
        $this->assertEquals($array, $this->hammer->get('test_array'));
        
        // Tester la valeur par défaut
        $this->assertEquals('default', $this->hammer->get('non_existent_key', 'default'));
    }

    public function testHas(): void
    {
        $this->hammer->put('test_key', 'test_value');
        $this->assertTrue($this->hammer->has('test_key'));
        $this->assertFalse($this->hammer->has('non_existent_key'));
    }

    public function testForget(): void
    {
        $this->hammer->put('test_key', 'test_value');
        $this->assertTrue($this->hammer->has('test_key'));
        
        $this->hammer->forget('test_key');
        $this->assertFalse($this->hammer->has('test_key'));
    }

    public function testForever(): void
    {
        $this->hammer->forever('test_key', 'test_value');
        $this->assertEquals('test_value', $this->hammer->get('test_key'));
    }

    public function testIncrementAndDecrement(): void
    {
        $this->hammer->put('counter', 5);
        
        $this->assertEquals(6, $this->hammer->increment('counter'));
        $this->assertEquals(6, $this->hammer->get('counter'));
        
        $this->assertEquals(3, $this->hammer->decrement('counter', 3));
        $this->assertEquals(3, $this->hammer->get('counter'));
        
        // Tester l'incrémentation d'une clé non existante
        $this->assertEquals(1, $this->hammer->increment('new_counter'));
    }

    public function testRemember(): void
    {
        $callCount = 0;
        $callback = function () use (&$callCount) {
            $callCount++;
            return 'generated_value';
        };
        
        // Premier appel, devrait exécuter le callback
        $this->assertEquals('generated_value', $this->hammer->remember('test_key', 10, $callback));
        $this->assertEquals(1, $callCount);
        
        // Deuxième appel, devrait utiliser la valeur en cache
        $this->assertEquals('generated_value', $this->hammer->remember('test_key', 10, $callback));
        $this->assertEquals(1, $callCount); // Le callback ne devrait pas être appelé à nouveau
    }

    public function testRememberForever(): void
    {
        $callCount = 0;
        $callback = function () use (&$callCount) {
            $callCount++;
            return 'generated_value';
        };
        
        // Premier appel, devrait exécuter le callback
        $this->assertEquals('generated_value', $this->hammer->rememberForever('test_key', $callback));
        $this->assertEquals(1, $callCount);
        
        // Deuxième appel, devrait utiliser la valeur en cache
        $this->assertEquals('generated_value', $this->hammer->rememberForever('test_key', $callback));
        $this->assertEquals(1, $callCount); // Le callback ne devrait pas être appelé à nouveau
    }

    public function testPull(): void
    {
        $this->hammer->put('test_key', 'test_value');
        
        // Récupérer et supprimer la valeur
        $this->assertEquals('test_value', $this->hammer->pull('test_key'));
        
        // Vérifier que la clé n'existe plus
        $this->assertFalse($this->hammer->has('test_key'));
        
        // Tester avec une valeur par défaut
        $this->assertEquals('default', $this->hammer->pull('non_existent_key', 'default'));
    }

    public function testExpiration(): void
    {
        // Mettre en cache avec une expiration très courte (1 seconde)
        $this->hammer->put('test_key', 'test_value', 1);
        $this->assertEquals('test_value', $this->hammer->get('test_key'));
        
        // Attendre que la valeur expire
        sleep(2);
        
        // La valeur devrait maintenant être expirée
        $this->assertNull($this->hammer->get('test_key'));
    }

    public function testTouch(): void
    {
        // Mettre en cache avec une expiration très courte
        $this->hammer->put('test_key', 'test_value', 1);
        
        // Prolonger la durée de vie
        $this->hammer->touch('test_key', 10);
        
        // Attendre que l'expiration initiale soit dépassée
        sleep(2);
        
        // La valeur devrait toujours être en cache
        $this->assertEquals('test_value', $this->hammer->get('test_key'));
    }

    public function testFlush(): void
    {
        $this->hammer->put('key1', 'value1');
        $this->hammer->put('key2', 'value2');
        
        $this->assertTrue($this->hammer->has('key1'));
        $this->assertTrue($this->hammer->has('key2'));
        
        // Vider le cache
        $this->hammer->flush();
        
        $this->assertFalse($this->hammer->has('key1'));
        $this->assertFalse($this->hammer->has('key2'));
    }
}
