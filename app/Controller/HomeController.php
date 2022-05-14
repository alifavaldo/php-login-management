<?php

namespace Alifavaldo\Belajar\PHP\MVC\Controller;

use Alifavaldo\Belajar\PHP\MVC\App\View;
use Alifavaldo\Belajar\PHP\MVC\Config\Database;
use Alifavaldo\Belajar\PHP\MVC\Repository\SessionRepository;
use Alifavaldo\Belajar\PHP\MVC\Repository\UserRepository;
use Alifavaldo\Belajar\PHP\MVC\Service\SessionService;

class HomeController
{
    private SessionService $sessionService;

    public function __construct()
    {
        $connection = Database::getConnection();
        $sessionRepository = new SessionRepository($connection);
        $userRepository = new UserRepository($connection);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }
    function index()
    {
        $user = $this->sessionService->current();
        if ($user == null) {
            View::render('Home/index', [
                'title' => "PHP Login Management"
            ]);
        } else {
            View::render('Home/dashboard', [
                'title' => "Dashboard",
                'user' => [
                    'name' => $user->name
                ]
            ]);
        }
    }
}
