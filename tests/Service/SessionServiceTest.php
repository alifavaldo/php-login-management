<?php

namespace Alifavaldo\Belajar\PHP\MVC\Service;

require_once __DIR__ . '/../Helper/helper.php';

use Alifavaldo\Belajar\PHP\MVC\Config\Database;
use Alifavaldo\Belajar\PHP\MVC\Domain\Session;
use Alifavaldo\Belajar\PHP\MVC\Domain\User;
use Alifavaldo\Belajar\PHP\MVC\Repository\SessionRepository;
use Alifavaldo\Belajar\PHP\MVC\Repository\UserRepository;
use PHPUnit\Framework\TestCase;



class SessionServiceTest extends TestCase
{
    private SessionService $sessionService;
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    public function setUp(): void
    {
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();


        $user = new User();
        $user->id = "edo";
        $user->name = "Crayon";
        $user->password = "edo";
        $this->userRepository->save($user);
    }



    public function testCreate()
    {
        $session = $this->sessionService->create("edo");

        $this->expectOutputRegex("[X-CRAYON-SESSION: $session->id]");

        $result = $this->sessionRepository->findById($session->id);

        self::assertEquals("edo", $result->userId);
    }

    public function testDestroy()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "edo";

        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
        $this->sessionService->destroy();

        $this->expectOutputRegex("[X-CRAYON-SESSION: ]");

        $result = $this->sessionRepository->findById($session->id);
        self::assertNull($result);
    }

    public function testCurrent()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "edo";

        $this->sessionRepository->save($session);
        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
        $user = $this->sessionService->current();

        self::assertEquals($session->userId, $user->id);
    }

    // public function testCurrentNotFound()
    // {
    //     $session = new Session();
    //     $session->id = uniqid();
    //     $session->userId = "edo";

    //     $this->sessionRepository->save($session);
    //     $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
    //     $user = $this->sessionService->current();

    //     self::assertEquals($session->userId, $user->id);
    // }
}
