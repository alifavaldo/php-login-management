<?php

namespace Alifavaldo\Belajar\PHP\MVC\Repository;

use Alifavaldo\Belajar\PHP\MVC\Config\Database;
use Alifavaldo\Belajar\PHP\MVC\Domain\Session;
use Alifavaldo\Belajar\PHP\MVC\Domain\User;
use PHPUnit\Framework\TestCase;


class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;
    private SessionRepository $sessionrepository;

    protected function setUp(): void
    {
        $this->sessionrepository = new SessionRepository(Database::getConnection());
        $this->sessionrepository->deleteAll();

        $this->userRepository = new UserRepository(Database::getConnection());
        $this->userRepository->deleteAll();
    }

    public function testSaveSuccess()
    {
        $user = new User();
        $user->id = "edo";
        $user->name = "Crayon";
        $user->password = "rahasia";

        $this->userRepository->save($user);

        $result = $this->userRepository->findById($user->id);

        self::assertEquals($user->id, $result->id);
        self::assertEquals($user->name, $result->name);
        self::assertEquals($user->password, $result->password);
    }

    public function testFindByIdNotFound()
    {
        $user = $this->userRepository->findById("notfound");

        self::assertNull($user);
    }

    public function testUpdate()
    {
        $user = new User();
        $user->id = "edo";
        $user->name = "Crayon";
        $user->password = "rahasia";
        $this->userRepository->save($user);

        $user->name = "Alif";
        $this->userRepository->update($user);
        $result = $this->userRepository->findById($user->id);
        self::assertEquals($user->id, $result->id);
        self::assertEquals($user->name, $result->name);
        self::assertEquals($user->password, $result->password);
    }
}
