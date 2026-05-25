<?php

/**
 * Base Controller
 *
 * All controllers extend this class.
 * Provides view rendering and model loading helpers.
 */

abstract class Controller
{
    /**
     * Render a view file.
     *
     * @param string $view   Dot-notation path relative to /views  e.g. 'auth.login'
     * @param array  $data   Variables to extract into the view scope
     * @param string $layout Layout file name inside views/layouts/ (without .php)
     *                       Pass an empty string to render without a layout.
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        // Convert dot-notation to directory path
        $viewPath = BASE_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            $this->abort(404, "View not found: {$view}");
        }

        // Make data variables available inside the view
        extract($data, EXTR_SKIP);

        if ($layout === '') {
            // Render view directly without wrapping layout
            require $viewPath;
            return;
        }

        $layoutPath = BASE_PATH . '/views/layouts/' . $layout . '.php';

        if (!file_exists($layoutPath)) {
            // Fallback: render without layout
            require $viewPath;
            return;
        }

        // Capture view output so the layout can inject it
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require $layoutPath;
    }

    /**
     * Load and instantiate a model class.
     *
     * @param string $model  Model class name (e.g. 'UserModel')
     */
    protected function model(string $model): object
    {
        $modelPath = BASE_PATH . '/app/models/' . $model . '.php';

        if (!file_exists($modelPath)) {
            throw new \RuntimeException("Model not found: {$model}");
        }

        require_once $modelPath;
        return new $model();
    }

    /**
     * Redirect to a URL.
     */
    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Return a JSON response and exit.
     */
    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Abort with an HTTP error page.
     */
    protected function abort(int $code = 404, string $message = ''): never
    {
        http_response_code($code);
        $errorView = BASE_PATH . '/views/errors/' . $code . '.php';

        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo "<h1>Error {$code}</h1><p>" . htmlspecialchars($message) . "</p>";
        }
        exit;
    }
}
