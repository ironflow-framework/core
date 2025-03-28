<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use IronFlow\Database\Iron\Model;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour les fonctionnalités de base du modèle
 */
class ModelTest extends TestCase
{
   /**
    * @var TestModel
    */
   protected $model;

   protected function setUp(): void
   {
      parent::setUp();
      $this->model = new TestModel();
   }

   public function testNewModelHasNoAttributes(): void
   {
      $this->assertEmpty($this->model->getAttributes());
   }

   public function testModelCanFillAttributes(): void
   {
      $attributes = [
         'name' => 'Test Name',
         'email' => 'test@example.com'
      ];

      $this->model->fill($attributes);

      $this->assertEquals($attributes['name'], $this->model->name);
      $this->assertEquals($attributes['email'], $this->model->email);
   }

   public function testModelCanCastAttributes(): void
   {
      $this->model->fill([
         'is_active' => '1',
         'count' => '10',
         'price' => '99.99'
      ]);

      $this->assertIsBool($this->model->is_active);
      $this->assertTrue($this->model->is_active);

      $this->assertIsInt($this->model->count);
      $this->assertEquals(10, $this->model->count);

      $this->assertIsFloat($this->model->price);
      $this->assertEquals(99.99, $this->model->price);
   }

   public function testModelCanSetAndGetAttributes(): void
   {
      $this->model->name = 'Test Name';
      $this->assertEquals('Test Name', $this->model->name);
   }

   public function testModelCanCheckIfAttributeExists(): void
   {
      $this->model->name = 'Test Name';
      $this->assertTrue(isset($this->model->name));
      $this->assertFalse(isset($this->model->nonexistent));
   }

   public function testModelCanUnsetAttribute(): void
   {
      $this->model->name = 'Test Name';
      unset($this->model->name);
      $this->assertFalse(isset($this->model->name));
   }

   public function testModelHandlesDateCasting(): void
   {
      $now = new \DateTime();
      $this->model->created_at = $now->format('Y-m-d H:i:s');

      $this->assertInstanceOf(\DateTime::class, $this->model->created_at);
   }

   public function testModelUsesSetMutator(): void
   {
      $this->model->password = 'password123';

      // Le mutateur devrait hasher le mot de passe
      $this->assertNotEquals('password123', $this->model->password);
      $this->assertTrue(password_verify('password123', $this->model->password));
   }

   public function testModelUsesGetMutator(): void
   {
      $this->model->first_name = 'John';
      $this->model->last_name = 'Doe';

      $this->assertEquals('John Doe', $this->model->full_name);
   }
}

/**
 * Modèle de test pour les tests unitaires
 */
class TestModel extends Model
{
   protected static string $table = 'test_models';

   protected array $fillable = [
      'name',
      'email',
      'password',
      'first_name',
      'last_name',
      'is_active',
      'count',
      'price',
      'created_at'
   ];

   protected array $casts = [
      'is_active' => 'boolean',
      'count' => 'integer',
      'price' => 'float',
      'created_at' => 'datetime'
   ];

   protected array $dates = [
      'created_at',
      'updated_at'
   ];

   // Mutateur pour le mot de passe
   public function setPasswordAttribute(string $value): string
   {
      return password_hash($value, PASSWORD_DEFAULT);
   }

   // Accesseur pour le nom complet
   public function getFullNameAttribute(): string
   {
      return $this->getAttribute('first_name') . ' ' . $this->getAttribute('last_name');
   }

   // Méthode pour obtenir les attributs (pour les tests)
   public function getAttributes(): array
   {
      return $this->attributes;
   }
}
