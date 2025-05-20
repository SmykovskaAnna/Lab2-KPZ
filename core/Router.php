<?php

class Router
{
    private static $actions = [
        'register' => ['UserController', 'register'],
        'login' => ['UserController', 'login'],
        'logout' => ['UserController', 'logout'],
        'start_game' => ['GameController', 'start'],
        'play' => ['GameController', 'play'],
        'result' => ['GameController', 'result'],
        'create_multiplayer_game' => ['GameController', 'createMultiplayerGame'],
        'join_multiplayer_game' => ['GameController', 'joinMultiplayerGame'],
        'open_games' => ['GameController', 'openGames'],
        'stats' => ['GameController', 'stats'],
        'send_message' => ['GameController', 'sendMessage'],
        'clear_chat' => ['GameController', 'clearChat']
    ];

    public static function route()
    {
        $action = $_GET['action'] ?? 'login';

        if (array_key_exists($action, self::$actions)) {
            list($controller, $method) = self::$actions[$action];
            require "../controllers/{$controller}.php";
            (new $controller())->$method();
        } else {
            self::handleNotFound();
        }
    }

    private static function handleNotFound()
    {
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
    }
}

?>