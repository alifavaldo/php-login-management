<?php

namespace Alifavaldo\Belajar\PHP\MVC\Controller;

use Alifavaldo\Belajar\PHP\MVC\Config\Database;
use Alifavaldo\Belajar\PHP\MVC\Domain\Session;
use Alifavaldo\Belajar\PHP\MVC\Domain\User;
use Alifavaldo\Belajar\PHP\MVC\Repository\SessionRepository;
use Alifavaldo\Belajar\PHP\MVC\Repository\UserRepository;
use Alifavaldo\Belajar\PHP\MVC\Service\SessionService;
use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    private HomeController $homeController;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $this->homeController = new HomeController();
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    public function testGuest()
    {
        $this->homeController->index();

        $this->expectOutputRegex("[Login Management]");
    }

    public function testUserLogin()
    {
        $user = new User();

        $user->id = "edo";
        $user->name = "Crayon";
        $user->password = "edo";
        $this->userRepository->save($user);


        $session = new Session();
        $session->id = uniqid();
        $session->userId = $user->id;
        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $this->homeController->index();

        $this->expectOutputRegex("[Hello Crayon]");
    }
}
