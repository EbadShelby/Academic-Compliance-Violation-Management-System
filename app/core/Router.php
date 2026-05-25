<?php

/**
 * Router
 *
 * Simple, fast router that maps URI + method patterns to controller actions.
 *
 * Usage (in routes/web.php):
 *   $router->get('/',                    'HomeController@index');
 *   $router->post('/login',              'AuthController@login');
 *   $router->get('/violations/{id}',     'ViolationController@show');
 */

class Router
{
    private array $routes = [];

    // ── Route registration ───────────────────────────────────────────────────

    public function get(string $uri, string $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, string $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, string $action): void
    {
        $this->addRoute('PUT', $uri, $action);
    }

    public function delete(string $uri, string $action): void
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    private function addRoute(string $method, string $uri, string $action): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'uri'     => $uri,
            'action'  => $action,
            'pattern' => $this->buildPattern($uri),
        ];
    }

    // ── Dispatch ─────────────────────────────────────────────────────────────

    /**
     * Resolve the current request against registered routes.
     */
    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        // Support method spoofing via hidden _method field (PUT / DELETE forms)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = $this->parseUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Named capture groups become route parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->callAction($route['action'], array_values($params));
                return;
            }
        }

        // No route matched
        $this->handleNotFound();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Parse the current request URI, stripping the base path and query string.
     */
    private function parseUri(): string
    {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']); // e.g. /techxp-team10/public
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Strip the subdirectory prefix so routes are always relative to "/"
        if ($scriptDir !== '/' && str_starts_with($requestUri, $scriptDir)) {
            $requestUri = substr($requestUri, strlen($scriptDir));
        }

        return '/' . ltrim($requestUri, '/');
    }

    /**
     * Convert a URI pattern like /violations/{id} to a regex.
     */
    private function buildPattern(string $uri): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . rtrim($pattern, '/') . '/?$#';
    }

    /**
     * Resolve "ControllerClass@method" and invoke it.
     */
    private function callAction(string $action, array $params = []): void
    {
        [$controllerName, $method] = explode('@', $action, 2);

        $controllerPath = BASE_PATH . '/app/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerPath)) {
            $this->handleNotFound("Controller not found: {$controllerName}");
        }

        require_once $controllerPath;

        if (!class_exists($controllerName)) {
            $this->handleNotFound("Class not found: {$controllerName}");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            $this->handleNotFound("Method not found: {$controllerName}@{$method}");
        }

        call_user_func_array([$controller, $method], $params);
    }

    private function handleNotFound(string $message = 'Page not found'): never
    {
        http_response_code(404);
        $errorView = BASE_PATH . '/views/errors/404.php';

        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo '<h1>404 — Not Found</h1><p>' . htmlspecialchars($message) . '</p>';
        }
        exit;
    }
}
