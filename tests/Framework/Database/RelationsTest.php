<?php

declare(strict_types=1);

namespace Tests\Framework\Database;

use IronFlow\Database\Iron\Model;
use IronFlow\Database\Relations\BelongsTo;
use IronFlow\Database\Relations\BelongsToMany;
use IronFlow\Database\Relations\HasMany;
use IronFlow\Database\Relations\HasOne;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour les fonctionnalités de relation des modèles
 */
class RelationsTest extends TestCase
{
   protected $post;
   protected $user;
   protected $comment;
   protected $profile;
   protected $category;

   protected function setUp(): void
   {
      parent::setUp();

      $this->user = new UserModel(['id' => 1, 'name' => 'John Doe']);
      $this->profile = new ProfileModel(['id' => 1, 'user_id' => 1, 'bio' => 'Test bio']);
      $this->post = new PostModel(['id' => 1, 'user_id' => 1, 'title' => 'Test Post']);
      $this->comment = new CommentModel(['id' => 1, 'post_id' => 1, 'content' => 'Test comment']);
      $this->category = new CategoryModel(['id' => 1, 'name' => 'Test Category']);
   }

   public function testHasOneRelation(): void
   {
      $relation = $this->user->profile();

      $this->assertInstanceOf(HasOne::class, $relation);
   }

   public function testHasManyRelation(): void
   {
      $relation = $this->user->posts();

      $this->assertInstanceOf(HasMany::class, $relation);
   }

   public function testBelongsToRelation(): void
   {
      $relation = $this->post->user();

      $this->assertInstanceOf(BelongsTo::class, $relation);
   }

   public function testBelongsToManyRelation(): void
   {
      $relation = $this->post->categories();

      $this->assertInstanceOf(BelongsToMany::class, $relation);
   }

   public function testWithPivotMethod(): void
   {
      $relation = $this->post->categories()->withPivot(['created_at', 'is_featured']);

      $this->assertInstanceOf(BelongsToMany::class, $relation);
      // Vérification plus avancée nécessiterait un mock ou une implémentation de test
   }
}

/**
 * Modèles de test pour les tests de relation
 */
class UserModel extends Model
{
   protected static string $table = 'users';

   protected array $fillable = ['id', 'name', 'email'];

   public function profile()
   {
      return $this->hasOne(ProfileModel::class);
   }

   public function posts()
   {
      return $this->hasMany(PostModel::class, 'user_id');
   }
}

class ProfileModel extends Model
{
   protected static string $table = 'profiles';

   protected array $fillable = ['id', 'user_id', 'bio'];

   public function user()
   {
      return $this->belongsTo(UserModel::class);
   }
}

class PostModel extends Model
{
   protected static string $table = 'posts';

   protected array $fillable = ['id', 'user_id', 'title', 'content'];

   public function user()
   {
      return $this->belongsTo(UserModel::class);
   }

   public function comments()
   {
      return $this->hasMany(CommentModel::class);
   }

   public function categories()
   {
      return $this->belongsToMany(CategoryModel::class, 'post_categories');
   }
}

class CommentModel extends Model
{
   protected static string $table = 'comments';

   protected array $fillable = ['id', 'post_id', 'content'];

   public function post()
   {
      return $this->belongsTo(PostModel::class);
   }
}

class CategoryModel extends Model
{
   protected static string $table = 'categories';

   protected array $fillable = ['id', 'name'];

   public function posts()
   {
      return $this->belongsToMany(PostModel::class, 'post_categories');
   }
}
