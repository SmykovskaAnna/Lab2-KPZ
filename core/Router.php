<?php

class Router
{
    public static function route()
    {
        $action = $_GET['action'] ?? 'login';
        switch ($action) {
            case 'register':
                require '../controllers/UserController.php';
                (new UserController())->register();
                break;
            case 'login':
                require '../controllers/UserController.php';
                (new UserController())->login();
                break;
            case 'logout':
                require '../controllers/UserController.php';
                (new UserController())->logout();
                break;
            case 'start_game':
                require '../controllers/GameController.php';
                (new GameController())->start();
                break;
            case 'play':
                require '../controllers/GameController.php';
                (new GameController())->play();
                break;
            case 'result':
                require '../controllers/GameController.php';
                (new GameController())->result();
                break;
                case 'create_multiplayer_game':
                    require '../controllers/GameController.php';
                    (new GameController())->createMultiplayerGame();
                    break;
                case 'join_multiplayer_game':
                    require '../controllers/GameController.php';
                    (new GameController())->joinMultiplayerGame();
                    break;
                case 'open_games':
                    require '../controllers/GameController.php';
                    (new GameController())->openGames();
                    break;
                    case 'stats':
                        require '../controllers/GameController.php';
                        (new GameController())->stats();
                        break;
                        case 'send_message':
                            require '../controllers/GameController.php';
                            (new GameController())->sendMessage();
                            break;
                            case 'clear_chat':
                                require '../controllers/GameController.php';
                                (new GameController())->clearChat();
                                break;
                    
            default:
                header("HTTP/1.0 404 Not Found");
                echo "404 Not Found";
        }
    }
}

?>