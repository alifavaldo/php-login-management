<?php

namespace Alifavaldo\Belajar\PHP\MVC\Service;

use Alifavaldo\Belajar\PHP\MVC\Config\Database;
use Alifavaldo\Belajar\PHP\MVC\Domain\User;
use Alifavaldo\Belajar\PHP\MVC\Exception\ValidationException;
use Alifavaldo\Belajar\PHP\MVC\Model\UserLoginRequest;
use Alifavaldo\Belajar\PHP\MVC\Model\UserloginResponse;
use Alifavaldo\Belajar\PHP\MVC\Model\userPasswordUpdateResponse;
use Alifavaldo\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use Alifavaldo\Belajar\PHP\MVC\Model\UserProfileUpdateResponse;
use Alifavaldo\Belajar\PHP\MVC\Model\UserRegisterRequest;
use Alifavaldo\Belajar\PHP\MVC\Model\UserRegisterResponse;
use Alifavaldo\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use Alifavaldo\Belajar\PHP\MVC\Repository\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function register(UserRegisterRequest $request): UserRegisterResponse
    {
        $this->validateUserRegistrationRequest($request);

        try {
            Database::beginTransaction();
            $user = $this->userRepository->findById($request->id);
            if ($user != null) {
                throw new ValidationException("User Id already exists");
            }

            $user = new User();
            $user->id = $request->id;
            $user->name = $request->name;
            $user->password = password_hash($request->password, PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $response = new UserRegisterResponse();
            $response->user = $user;

            Database::commitTransaction();

            return $response;
        } catch (\Exception $exception) {
            Database::rollbackTransaction();
            throw $exception;
        }
    }

    private function validateUserRegistrationRequest(UserRegisterRequest $request)
    {
        if (
            $request->id == null || $request->name == null || $request->password == null ||
            trim($request->id) == "" || trim($request->name) == "" || trim($request->password) == ""
        ) {
            throw new ValidationException("Id, Name, Password can not blank");
        }
    }

    public function login(UserLoginRequest $request): UserloginResponse
    {
        $this->validateloginRequest($request);

        $user = $this->userRepository->findById($request->id);
        if ($user == null) {
            throw new ValidationException("Id or password is wrong");
        }

        if (password_verify($request->password, $user->password)) {
            $response = new UserloginResponse();
            $response->user = $user;

            return $response;
        } else {
            throw new ValidationException("Id or password is wrong");
        }
    }

    private function validateloginRequest(UserLoginRequest $request)
    {
        if (
            $request->id == null || $request->password == null ||
            trim($request->id) == ""  || trim($request->password) == ""
        ) {
            throw new ValidationException("Id, Password can not blank");
        }
    }

    public function updateProfile(UserProfileUpdateRequest $request): UserProfileUpdateResponse
    {
        $this->validateUpdateProfileUserRequest($request);

        try {
            Database::beginTransaction();

            $user = $this->userRepository->findById($request->id);

            if ($user == null) {
                throw new ValidationException("User Not Found");
            }

            $user->name = $request->name;
            $this->userRepository->update($user);

            Database::commitTransaction();

            $response = new UserProfileUpdateResponse();
            $response->user = $user;
            return $response;
        } catch (\Exception $exception) {
            Database::rollBackTransaction();
            throw $exception;
        }
    }

    public function validateUpdateProfileUserRequest(UserProfileUpdateRequest $request)
    {
        if (
            $request->id == null || $request->name == null ||
            trim($request->id) == "" || trim($request->name) == ""
        ) {
            throw new ValidationException("Id, Name, Password can not blank");
        }
    }

    public function updatePassword(UserPasswordUpdateRequest $request): userPasswordUpdateResponse
    {

        $this->validateUpdatePasswordRequest($request);

        try {
            Database::beginTransaction();

            $user = $this->userRepository->findById($request->id);
            if ($user == null) {
                throw new ValidationException("User is not found");
            }

            if (!password_verify($request->oldPassword, $user->password)) {
                throw new ValidationException("Old password is wrong");
            }

            $user->password = password_hash($request->newPassword, PASSWORD_BCRYPT);
            $this->userRepository->update($user);

            Database::commitTransaction();

            $response = new userPasswordUpdateResponse();
            $response->user = $user;
            return $response;
        } catch (\Exception $exception) {
            Database::rollBackTransaction();
            throw $exception;
        }
    }

    public function validateUpdatePasswordRequest(UserPasswordUpdateRequest $request)
    {
        if (
            $request->id == null || $request->oldPassword == null || $request->newPassword == null ||
            trim($request->id) == "" || trim($request->oldPassword) == "" || trim($request->newPassword) == ""
        ) {
            throw new ValidationException("Id, Old Password, New Password can not blank");
        }
    }
}
