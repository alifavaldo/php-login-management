<?php

namespace Alifavaldo\Belajar\PHP\MVC\Middleware;

use Alifavaldo\Belajar\PHP\MVC\App\View;
use Alifavaldo\Belajar\PHP\MVC\Config\Database;
use Alifavaldo\Belajar\PHP\MVC\Repository\SessionRepository;
use Alifavaldo\Belajar\PHP\MVC\Repository\UserRepository;
use Alifavaldo\Belajar\PHP\MVC\Service\SessionService;

class MustLoginMiddleware implements Middleware
{
    private SessionService $sessionService;

    public function __construct()
    {
        $sessionRepository = new SessionRepository(Database::getConnection());
        $userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    function before(): void
    {
        $user = $this->sessionService->current();
        if ($user == null) {
            View::redirect("/users/login");
        }
    }
}
