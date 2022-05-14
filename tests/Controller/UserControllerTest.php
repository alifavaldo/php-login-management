<?php




namespace Alifavaldo\Belajar\PHP\MVC\Controller {

    require_once __DIR__ . '/../Helper/helper.php';

    use Alifavaldo\Belajar\PHP\MVC\Config\Database;
    use Alifavaldo\Belajar\PHP\MVC\Domain\Session;
    use Alifavaldo\Belajar\PHP\MVC\Repository\UserRepository;
    use Alifavaldo\Belajar\PHP\MVC\Domain\User;
    use Alifavaldo\Belajar\PHP\MVC\Repository\SessionRepository;
    use Alifavaldo\Belajar\PHP\MVC\Service\SessionService;
    use PHPUnit\Framework\TestCase;

    class UserControllerTest extends TestCase
    {

        private UserController $userController;
        private UserRepository $userRepository;
        private SessionRepository $sessionRepository;

        protected function setUp(): void
        {
            $this->userController = new UserController();

            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->sessionRepository->deleteAll();

            $this->userRepository = new UserRepository(Database::getConnection());
            $this->userRepository->deleteAll();

            putenv("mode=test");
        }

        public function testRegister()
        {
            $this->userController->register();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[id]");
            $this->expectOutputRegex("[name]");
            $this->expectOutputRegex("[password]");
            $this->expectOutputRegex("[Register new User]");
        }

        public function testPostRegisterSuccess()
        {
            $_POST['id'] = "edo";
            $_POST['name'] = "Crayon";
            $_POST['password'] = "Rahasia";


            $this->userController->postRegister();

            $this->expectOutputRegex("[Location: /users/login]");
        }

        public function testPostRegisterValidationError()
        {
            $_POST['id'] = "";
            $_POST['name'] = "Crayon";
            $_POST['password'] = "Rahasia";

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[id]");
            $this->expectOutputRegex("[name]");
            $this->expectOutputRegex("[password]");
            $this->expectOutputRegex("[Register New User]");
            $this->expectOutputRegex("[Id, Name, Password can not blank]");
        }

        public function testPostRegisterDuplicate()
        {
            $user = new User();
            $user->id = "edo";
            $user->name = "Crayon";
            $user->password = "rahasia";


            $this->userRepository->save($user);

            $_POST['id'] = "edo";
            $_POST['name'] = "Crayon";
            $_POST['password'] = "Rahasia";

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[id]");
            $this->expectOutputRegex("[name]");
            $this->expectOutputRegex("[password]");
            $this->expectOutputRegex("[Register New User]");
            $this->expectOutputRegex("[User Id already exists]");
        }

        public function testLogin()
        {
            $this->userController->login();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
        }

        public function testLoginSuccess()
        {
            $user = new User();
            $user->id = "edo";
            $user->name = "Crayon";
            $user->password = password_hash("edo", PASSWORD_BCRYPT);


            $this->userRepository->save($user);

            $_POST['id'] = 'edo';
            $_POST['password'] = 'edo';

            $this->userController->postlogin();

            $this->expectOutputRegex("[Location: /]");
            $this->expectOutputRegex("[X-CRAYON-SESSION]");
        }

        public function testLoginValidationError()
        {
            $_POST['id'] = "";
            $_POST['password'] = "";

            $this->userController->postlogin();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id, Password can not blank]");
        }

        public function testLoginNotFound()
        {
            $_POST['id'] = "notfound";
            $_POST['password'] = "notfound";

            $this->userController->postlogin();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id or password is wrong]");
        }

        public function testLoginWrongPassword()
        {
            $user = new User();
            $user->id = "edo";
            $user->name = "Crayon";
            $user->password = password_hash("edo", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST['id'] = "edo";
            $_POST['password'] = "rahasia";

            $this->userController->postlogin();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id or password is wrong]");
        }

        public function testLogout()
        {
            $user = new User();
            $user->id = "edo";
            $user->name = "Crayon";
            $user->password = password_hash("edo", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->logout();

            $this->expectOutputRegex("[Location: /]");
            $this->expectOutputRegex("[X-CRAYON-SESSION: ]");
        }

        public function testUpdateProfile()
        {
            $user = new User();
            $user->id = "edo";
            $user->name = "Crayon";
            $user->password = password_hash("edo", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;
            $this->userController->updateProfile();
            $this->expectOutputRegex("[Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[edo]");
            $this->expectOutputRegex("[name]");
            $this->expectOutputRegex("[Crayon]");
        }

        public function testPostUpdateProfileSuccess()
        {
            $user = new User();
            $user->id = "edo";
            $user->name = "Crayon";
            $user->password = password_hash("edo", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = 'Alif';
            $this->userController->postUpdateProfile();

            $this->expectOutputRegex("[Location: /]");

            $result = $this->userRepository->findById("edo");
            self::assertEquals("Alif", $result->name);
        }

        public function testPostUpdateProfileValidation()
        {
            $user = new User();
            $user->id = "edo";
            $user->name = "Crayon";
            $user->password = password_hash("edo", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = '';
            $this->userController->postUpdateProfile();

            $this->expectOutputRegex("[Location: /]");

            $this->userController->updateProfile();
            $this->expectOutputRegex("[Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[edo]");
            $this->expectOutputRegex("[name]");
            $this->expectOutputRegex("[Id, Name, Password can not blank]");
        }

        public function testUpdatePassword()
        {
            $user = new User();
            $user->id = "edo";
            $user->name = "Crayon";
            $user->password = password_hash("edo", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[edo]");
        }
        public function testUpdatePasswordSuccess()
        {
            $user = new User();
            $user->id = "edo";
            $user->name = "Crayon";
            $user->password = password_hash("edo", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;


            $_POST['oldPassword'] = 'edo';
            $_POST['newPassword'] = 'rahasia';
            $this->userController->postUpdatePassword();


            $this->expectOutputRegex("[Location: /]");

            $result = $this->userRepository->findById($user->id);
            self::assertTrue(password_verify("rahasia", $result->password));
        }

        public function testUpdatePasswordValidationError()
        {
            $user = new User();
            $user->id = "edo";
            $user->name = "Crayon";
            $user->password = password_hash("edo", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;


            $_POST['oldPassword'] = '';
            $_POST['newPassword'] = '';
            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[edo]");
            $this->expectOutputRegex("[Id, Old Password, New Password can not blank]");
        }

        public function testUpdatePasswordWrongOldPassword()
        {
            $user = new User();
            $user->id = "edo";
            $user->name = "Crayon";
            $user->password = password_hash("edo", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;


            $_POST['oldPassword'] = 'salah';
            $_POST['newPassword'] = 'edo';
            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[edo]");
            $this->expectOutputRegex("[Old password is wrong]");
        }
    }
}
