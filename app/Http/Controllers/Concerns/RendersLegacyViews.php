<?php

namespace App\Http\Controllers\Concerns;

trait RendersLegacyViews
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function render(string $view, array $data = []): never
    {
        $this->sendSessionCookie();

        extract($data);

        $viewPath = BASE_PATH . "/views/{$view}.php";

        if (! file_exists($viewPath)) {
            die("View {$view} not found at {$viewPath}");
        }

        $isHtmx = ! empty($_SERVER['HTTP_HX_REQUEST'])
            || ! empty($_SERVER['HTTP_HX_TARGET'])
            || ! empty($_SERVER['HTTP_HX_CURRENT_URL']);

        if ($isHtmx || str_contains($view, 'admin/layout')) {
            include $viewPath;
        } else {
            ob_start();
            include $viewPath;
            $content = ob_get_clean();

            if (str_starts_with($view, 'admin/') && $view !== 'admin/login') {
                include BASE_PATH . '/views/admin/layout.php';
            } else {
                echo $content;
            }
        }

        exit;
    }

    protected function redirect(string $url): never
    {
        session()->save();
        $this->sendSessionCookie();
        header("Location: {$url}");
        exit;
    }

    protected function sendPdfResponse(string $pdf, string $filename): never
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $pdf;
        exit;
    }

    protected function sendCsvResponse(string $csv, string $filename): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $csv;
        exit;
    }

    private function sendSessionCookie(): void
    {
        if (headers_sent()) {
            return;
        }

        $name = config('session.cookie', 'omnishop_session');
        $id = session()->getId();

        if ($id) {
            setcookie($name, $id, [
                'expires' => time() + (config('session.lifetime', 120) * 60),
                'path' => config('session.path', '/'),
                'secure' => config('session.secure', false),
                'httponly' => config('session.http_only', true),
                'samesite' => ucfirst(config('session.same_site', 'lax')),
            ]);
        }
    }
}
