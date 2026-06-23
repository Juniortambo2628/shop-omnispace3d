<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingsService
{
    /**
     * Bootstrap-safe loader (runs before Laravel DB is available).
     *
     * @return array<string, string>
     */
    public function loadFromDatabase(): array
    {
        try {
            $settings = DB::select('SELECT * FROM settings');
            $config = [];

            foreach ($settings as $setting) {
                $config[$setting->key] = $setting->value;
            }

            return $config;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @return array<string, string>
     */
    public function getAll(): array
    {
        try {
            return Setting::query()
                ->pluck('value', 'key')
                ->all();
        } catch (\Exception $e) {
            return $this->loadFromDatabase();
        }
    }

    /**
     * @param  array<string, mixed>  $post
     */
    public function saveFromPost(array $post): void
    {
        foreach ($post as $key => $value) {
            if ($key === 'action') {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $config
     *
     * @throws \Exception
     */
    public function sendTestEmail(array $config): void
    {
        require_once BASE_PATH . '/core/Mailer.php';

        $to = $config['gmail_address'] ?? '';

        if (empty($to)) {
            throw new \Exception('Gmail address is not configured.');
        }

        $subject = 'OmniShop Connection Test';
        $body = \Mailer::buildBaseHtml(
            'Connection Success',
            'Hello! This is a test email from OmniShop. If you are reading this, your Gmail SMTP settings are correctly configured and the system can send notifications successfully.',
            'OmniShop Admin'
        );

        $success = \Mailer::send($to, $subject, $body);

        if (! $success) {
            throw new \Exception('Mailer returned false. Check logs for details.');
        }
    }
}
