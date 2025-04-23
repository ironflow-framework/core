<?php

namespace IronFlow\Tests\Database;

use IronFlow\Database\Connection;
use IronFlow\Database\Model;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
   private Connection $connection;
   private TestModel $model;

   protected function setUp(): void
   {
      $this->connection = new Connection([
         'driver' => 'sqlite',
         'database' => ':memory:',
         'prefix' => '',
      ]);

      // Créer la table de test
      $this->connection->query('
            CREATE TABLE test_models (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');

      // Configurer le modèle
      TestModel::setConnection($this->connection);
      $this->model = new TestModel();
   }

   /**
    * Test que le modèle peut être créé
    */
   public function testModelCanBeCreated(): void
   {
      $model = new TestModel([
         'name' => 'Test User',
         'email' => 'test@example.com',
      ]);

      $this->assertEquals('Test User', $model->name);
      $this->assertEquals('test@example.com', $model->email);
   }

   /**
    * Test que le modèle peut être sauvegardé
    */
   public function testModelCanBeSaved(): void
   {
      $model = new TestModel([
         'name' => 'Test User',
         'email' => 'test@example.com',
      ]);

      $result = $model->save();

      $this->assertTrue($result);
      $this->assertNotNull($model->id);
   }

   /**
    * Test que le modèle peut être trouvé par ID
    */
   public function testModelCanBeFoundById(): void
   {
      // Créer un modèle
      $model = new TestModel([
         'name' => 'Test User',
         'email' => 'test@example.com',
      ]);
      $model->save();

      // Trouver le modèle
      $found = TestModel::find($model->id);

      $this->assertNotNull($found);
      $this->assertEquals($model->id, $found->id);
      $this->assertEquals($model->name, $found->name);
      $this->assertEquals($model->email, $found->email);
   }

   /**
    * Test que le modèle peut être mis à jour
    */
   public function testModelCanBeUpdated(): void
   {
      // Créer un modèle
      $model = new TestModel([
         'name' => 'Test User',
         'email' => 'test@example.com',
      ]);
      $model->save();

      // Mettre à jour le modèle
      $model->name = 'Updated User';
      $result = $model->save();

      $this->assertTrue($result);

      // Vérifier que le modèle a été mis à jour
      $found = TestModel::find($model->id);

      $this->assertEquals('Updated User', $found->name);
   }

   /**
    * Test que le modèle peut être supprimé
    */
   public function testModelCanBeDeleted(): void
   {
      // Créer un modèle
      $model = new TestModel([
         'name' => 'Test User',
         'email' => 'test@example.com',
      ]);
      $model->save();

      // Supprimer le modèle
      $result = $model->delete();

      $this->assertTrue($result);

      // Vérifier que le modèle a été supprimé
      $found = TestModel::find($model->id);

      $this->assertNull($found);
   }

   /**
    * Test que le modèle peut être trouvé par critères
    */
   public function testModelCanBeFoundByCriteria(): void
   {
      // Créer des modèles
      $model1 = new TestModel([
         'name' => 'Test User 1',
         'email' => 'test1@example.com',
      ]);
      $model1->save();

      $model2 = new TestModel([
         'name' => 'Test User 2',
         'email' => 'test2@example.com',
      ]);
      $model2->save();

      // Trouver les modèles
      $found = TestModel::where('name', 'LIKE', 'Test User%')->get();

      $this->assertCount(2, $found);
   }

   /**
    * Test que le modèle peut être compté
    */
   public function testModelCanBeCounted(): void
   {
      // Créer des modèles
      $model1 = new TestModel([
         'name' => 'Test User 1',
         'email' => 'test1@example.com',
      ]);
      $model1->save();

      $model2 = new TestModel([
         'name' => 'Test User 2',
         'email' => 'test2@example.com',
      ]);
      $model2->save();

      // Compter les modèles
      $count = TestModel::count();

      $this->assertEquals(2, $count);
   }
}

/**
 * Classe de modèle de test
 */
class TestModel extends Model
{
   /**
    * Nom de la table
    *
    * @var string
    */
   protected $table = 'test_models';

   /**
    * Attributs remplissables
    *
    * @var array<string>
    */
   protected $fillable = [
      'name',
      'email',
   ];
}
