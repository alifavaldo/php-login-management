<?php

namespace Alifavaldo\Belajar\PHP\MVC\Service;

use Alifavaldo\Belajar\PHP\MVC\Config\Database;
use Alifavaldo\Belajar\PHP\MVC\Domain\User;
use Alifavaldo\Belajar\PHP\MVC\Exception\ValidationException;
use Alifavaldo\Belajar\PHP\MVC\Model\UserLoginRequest;
use Alifavaldo\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use Alifavaldo\Belajar\PHP\MVC\Model\UserRegisterRequest;
use Alifavaldo\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use Alifavaldo\Belajar\PHP\MVC\Repository\SessionRepository;
use Alifavaldo\Belajar\PHP\MVC\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;

    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $connection = Database::getConnection();

        $this->userRepository = new UserRepository($connection);
        $this->userService = new UserService($this->userRepository);

        $this->sessionRepository = new SessionRepository($connection);
        $this->sessionRepository->deleteAll();

        $this->userRepository->deleteAll();
    }

    public function testregisterSuccess()
    {

        $request = new UserRegisterRequest();
        $request->id = "eko";
        $request->name = "Crayon";
        $request->password = "rahasia";

        $response = $this->userService->register($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertEquals($request->name, $response->user->name);
        self::assertNotEquals($request->password, $response->user->password);

        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testRegisterFailed()
    {
        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();

        $request->id = "";
        $request->name = "";
        $request->password = "";

        $this->userService->register($request);
        // assertions

    }

    /** @test */
    public function testRegisterDuplicated()
    {
        $user = new User();
        $user->id = "edo";
        $user->name = "Crayon";
        $user->password = "rahasia";

        $this->userRepository->save($user);

        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();

        $request->id = "edo";
        $request->name = "Crayon";
        $request->password = "rahasia";

        $this->userService->register($request);
        // assertions
    }

    public function testloginNotFound()
    {
        $this->expectException(ValidationException::class);
        $request = new UserLoginRequest();

        $request->id = "edo";
        $request->password = "edo";

        $this->userService->login($request);
    }

    public function testLoginWrongPassword()
    {
        $user = new User();

        $user->id = "edo";
        $user->name = "Crayon";
        $this->password = password_hash("edo", PASSWORD_BCRYPT);
        $this->expectException(ValidationException::class);
        $request = new UserLoginRequest();

        $request->id = "edo";
        $request->password = "salah";

        $this->userService->login($request);
    }

    public function testLoginSuccess()
    {
        $user = new User();

        $user->id = "edo";
        $user->name = "Crayon";
        $this->password = password_hash("edo", PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = "edo";
        $request->password = "edo";

        $response = $this->userService->login($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testUpdateSuccess()
    {
        $user = new User();

        $user->id = "edo";
        $user->name = "Crayon";
        $user->password = password_hash("edo", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserProfileUpdateRequest();
        $request->id = "edo";
        $request->name = "Alif";

        $this->userService->updateProfile($request);

        $result = $this->userRepository->findById($user->id);

        self::assertEquals($request->name, $result->name);
    }

    public function testUpdateValidationError()
    {
        $this->expectException(ValidationException::class);
        $request = new UserProfileUpdateRequest();
        $request->id = "";
        $request->name = "";
        $this->userService->updateProfile($request);
    }

    public function testUpdateNotFound()
    {

        $this->expectException(ValidationException::class);
        $request = new UserProfileUpdateRequest();
        $request->id = "edo";
        $request->name = "Alif";

        $this->userService->updateProfile($request);
    }

    public function testUpdatePasswordSuccess()
    {
        $user = new User();

        $user->id = "edo";
        $user->name = "Crayon";
        $user->password = password_hash("edo", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = 'edo';
        $request->oldPassword = 'edo';
        $request->newPassword = 'new';

        $this->userService->updatePassword($request);

        $result = $this->userRepository->findById($user->id);

        self::assertTrue(password_verify($request->newPassword, $result->password));
    }
    public function testUpdatePasswordValidationError()
    {
        $this->expectException(ValidationException::class);
        $request = new UserPasswordUpdateRequest();
        $request->id = 'edo';
        $request->oldPassword = '';
        $request->newPassword = '';

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordWrongPassword()
    {

        $this->expectException(ValidationException::class);
        $user = new User();

        $user->id = "edo";
        $user->name = "Crayon";
        $user->password = password_hash("edo", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = 'edo';
        $request->oldPassword = 'salah';
        $request->newPassword = 'new';

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordNotFound()
    {

        $this->expectException(ValidationException::class);



        $request = new UserPasswordUpdateRequest();
        $request->id = 'edo';
        $request->oldPassword = 'salah';
        $request->newPassword = 'new';

        $this->userService->updatePassword($request);
    }
}
