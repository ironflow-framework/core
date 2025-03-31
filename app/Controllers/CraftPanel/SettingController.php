<?php

namespace App\Http\Controllers\CraftPanel;

use App\Http\Controllers\Controller;
use IronFlow\Support\Facades\View;
use IronFlow\Support\Facades\Redirect;
use IronFlow\Support\Facades\Request;
use IronFlow\Support\Facades\Config;
use IronFlow\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
   /**
    * Affiche la page des paramètres
    *
    * @return \IronFlow\Support\Facades\View
    */
   public function index()
   {
      $settings = $this->getSettings();
      return View::make('craftpanel.settings.index', compact('settings'));
   }

   /**
    * Met à jour les paramètres
    *
    * @return \IronFlow\Support\Facades\Redirect
    */
   public function update()
   {
      $validator = $this->validateSettings(Request::all());

      if ($validator->fails()) {
         return Redirect::back()
            ->withErrors($validator)
            ->withInput();
      }

      $settings = Request::all();
      $this->updateSettings($settings);

      Cache::forget('craftpanel.settings');

      return Redirect::route('craftpanel.settings')
         ->with('success', 'Les paramètres ont été mis à jour avec succès.');
   }

   /**
    * Récupère les paramètres actuels
    *
    * @return array
    */
   protected function getSettings()
   {
      return Cache::remember('craftpanel.settings', 3600, function () {
         return [
            'general' => [
               'site_name' => Config::get('craftpanel.name', 'CraftPanel'),
               'site_description' => Config::get('craftpanel.description', ''),
               'timezone' => Config::get('app.timezone', 'UTC'),
               'locale' => Config::get('app.locale', 'fr'),
            ],
            'security' => [
               'password_min_length' => Config::get('craftpanel.security.password_min_length', 12),
               'require_2fa' => Config::get('craftpanel.security.require_2fa', true),
               'session_lifetime' => Config::get('craftpanel.security.session_lifetime', 60),
               'max_login_attempts' => Config::get('craftpanel.security.max_login_attempts', 5),
               'lockout_time' => Config::get('craftpanel.security.lockout_time', 30),
            ],
            'mail' => [
               'from_address' => Config::get('craftpanel.notifications.mail.from.address', 'noreply@example.com'),
               'from_name' => Config::get('craftpanel.notifications.mail.from.name', 'CraftPanel'),
               'mail_driver' => Config::get('mail.driver', 'smtp'),
               'smtp_host' => Config::get('mail.smtp.host', ''),
               'smtp_port' => Config::get('mail.smtp.port', 587),
               'smtp_username' => Config::get('mail.smtp.username', ''),
               'smtp_password' => Config::get('mail.smtp.password', ''),
               'smtp_encryption' => Config::get('mail.smtp.encryption', 'tls'),
            ],
            'theme' => [
               'default_theme' => Config::get('craftpanel.theme.default', 'light'),
               'custom_css' => Config::get('craftpanel.theme.custom_css', ''),
               'custom_js' => Config::get('craftpanel.theme.custom_js', ''),
            ],
         ];
      });
   }

   /**
    * Valide les paramètres
    *
    * @param  array  $data
    * @return \IronFlow\Support\Facades\Validator
    */
   protected function validateSettings($data)
   {
      return Validator::make($data, [
         'general.site_name' => 'required|string|max:255',
         'general.site_description' => 'nullable|string',
         'general.timezone' => 'required|string|timezone',
         'general.locale' => 'required|string|in:fr,en',
         'security.password_min_length' => 'required|integer|min:8',
         'security.require_2fa' => 'boolean',
         'security.session_lifetime' => 'required|integer|min:1',
         'security.max_login_attempts' => 'required|integer|min:1',
         'security.lockout_time' => 'required|integer|min:1',
         'mail.from_address' => 'required|email',
         'mail.from_name' => 'required|string|max:255',
         'mail.mail_driver' => 'required|string|in:smtp,mailgun,ses',
         'mail.smtp_host' => 'required_if:mail.mail_driver,smtp|string',
         'mail.smtp_port' => 'required_if:mail.mail_driver,smtp|integer',
         'mail.smtp_username' => 'required_if:mail.mail_driver,smtp|string',
         'mail.smtp_password' => 'required_if:mail.mail_driver,smtp|string',
         'mail.smtp_encryption' => 'required_if:mail.mail_driver,smtp|string|in:tls,ssl',
         'theme.default_theme' => 'required|string|in:light,dark',
         'theme.custom_css' => 'nullable|string',
         'theme.custom_js' => 'nullable|string',
      ]);
   }

   /**
    * Met à jour les paramètres dans la configuration
    *
    * @param  array  $settings
    * @return void
    */
   protected function updateSettings($settings)
   {
      foreach ($settings as $group => $values) {
         foreach ($values as $key => $value) {
            $configKey = "craftpanel.{$group}.{$key}";
            Config::set($configKey, $value);
         }
      }

      // Sauvegarder les paramètres dans la base de données
      foreach ($settings as $group => $values) {
         foreach ($values as $key => $value) {
            $this->saveSetting($group . '.' . $key, $value);
         }
      }
   }

   /**
    * Sauvegarde un paramètre dans la base de données
    *
    * @param  string  $key
    * @param  mixed  $value
    * @return void
    */
   protected function saveSetting($key, $value)
   {
      DB::table('craftpanel_settings')->updateOrInsert(
         ['key_name' => $key],
         ['value' => is_array($value) ? json_encode($value) : $value]
      );
   }
}
