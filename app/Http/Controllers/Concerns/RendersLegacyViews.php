<?php

namespace App\Http\Controllers\Concerns;

trait RendersLegacyViews
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function render(string $view, array $data = []): never
    {
        extract($data);

        $viewPath = BASE_PATH . "/views/{$view}.php";

        if (! file_exists($viewPath)) {
            die("View {$view} not found at {$viewPath}");
        }

        $isHtmx = ! empty($_SERVER['HTTP_HX_REQUEST']);

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
        header("Location: {$url}");
        exit;
    }
}
