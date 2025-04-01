<?php

use IronFlow\Database\Migrations\Migration;
use IronFlow\Database\Schema\Anvil;
use IronFlow\Database\Schema\Schema;
use IronFlow\Support\Facades\DB;

return new class extends Migration
{
   public function up(): void
   {
      Schema::createTable('craftpanel_settings', function (Anvil $table) {
         $table->id();
         $table->string('key_name')->unique();
         $table->text('value')->nullable();
         $table->timestamps();
      });

      // Création des paramètres par défaut
      $settings = [
         [
            'key_name' => 'general.site_name',
            'value' => 'CraftPanel',
         ],
         [
            'key_name' => 'general.site_description',
            'value' => 'Panneau d\'administration CraftPanel',
         ],
         [
            'key_name' => 'general.timezone',
            'value' => 'Europe/Paris',
         ],
         [
            'key_name' => 'general.locale',
            'value' => 'fr',
         ],
         [
            'key_name' => 'security.password_min_length',
            'value' => '12',
         ],
         [
            'key_name' => 'security.require_2fa',
            'value' => 'true',
         ],
         [
            'key_name' => 'security.session_lifetime',
            'value' => '60',
         ],
         [
            'key_name' => 'security.max_login_attempts',
            'value' => '5',
         ],
         [
            'key_name' => 'security.lockout_time',
            'value' => '30',
         ],
         [
            'key_name' => 'mail.from_address',
            'value' => 'noreply@example.com',
         ],
         [
            'key_name' => 'mail.from_name',
            'value' => 'CraftPanel',
         ],
         [
            'key_name' => 'mail.mail_driver',
            'value' => 'smtp',
         ],
         [
            'key_name' => 'mail.smtp_host',
            'value' => '',
         ],
         [
            'key_name' => 'mail.smtp_port',
            'value' => '587',
         ],
         [
            'key_name' => 'mail.smtp_username',
            'value' => '',
         ],
         [
            'key_name' => 'mail.smtp_password',
            'value' => '',
         ],
         [
            'key_name' => 'mail.smtp_encryption',
            'value' => 'tls',
         ],
         [
            'key_name' => 'theme.default_theme',
            'value' => 'light',
         ],
         [
            'key_name' => 'theme.custom_css',
            'value' => '',
         ],
         [
            'key_name' => 'theme.custom_js',
            'value' => '',
         ],
      ];

      $db = DB::getInstance();
      foreach ($settings as $setting) {
         $db->insert('craftpanel_settings', [
            'key_name' => $setting['key_name'],
            'value' => $setting['value'],
            'created_at' => now(),
            'updated_at' => now(),
         ]);
      }
   }

   public function down(): void
   {
      Schema::dropTableIfExists('craftpanel_settings');
   }
};
