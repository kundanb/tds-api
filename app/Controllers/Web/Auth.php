<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Utilities\HTTPResponseCodes;

class Auth extends BaseController
{
    public function createAuthToken(int $userId)
    {
        $token = createJWTToken('30 days');

        $insertedId = $this->loginsModel->insert([
            'user_id' => $userId,
            'token' => $token
        ]);

        return $insertedId ? $this->loginsModel->find($insertedId) : null;
    }

    public function register()
    {
        if (!$this->isDBConnected())
            return $this->respondWithUnknownError();


        $username = $this->request->getVar('username');
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');


        if (
            !$this->validate([
                'username' => 'required|min_length[6]|max_length[30]',
                'email' => 'required|valid_email',
                'password' => 'required|min_length[8]'
            ])
        ) {
            return $this->respondWith(
                success: 0,
                message: $this->validator->getErrors(),
                status: HTTPResponseCodes::BAD_REQUEST
            );
        }


        if ($this->usersModel->where('username', $username)->withDeleted()->countAllResults()) {
            return $this->respondWith(
                success: 0,
                message: 'Username already taken',
                status: HTTPResponseCodes::NOT_ACCEPTABLE
            );
        }

        if ($this->usersModel->where('email', $email)->withDeleted()->countAllResults()) {
            return $this->respondWith(
                success: 0,
                message: 'Email already registered',
                status: HTTPResponseCodes::NOT_ACCEPTABLE
            );
        }


        $hashedPassword = hashPassword($password);


        $insertedId = $this->usersModel->insert([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword
        ]);

        if (!$insertedId) {
            return $this->respondWith(
                success: 0,
                message: 'Couldn\'t register your account',
                status: HTTPResponseCodes::INTERNAL_SERVER_ERROR
            );
        }


        sendMail(
            $email,
            'Welcome to TDS Era',

            view('emails\user_registration', [
                'title' => 'Welcome to TDS Era',
                'logoImage' => base_url('images/logo-dark.png'),
                'username' => $username
            ])
        );


        $loginData = $this->createAuthToken($insertedId);

        if (!$loginData['token']) {
            return $this->respondWith(
                message: 'Registered successfully.'
            );
        }


        return $this->respondWith(
            message: 'Registered successfully',
            data: [
                'token' => $loginData['token']
            ]
        );
    }

    public function login()
    {
        if (!$this->isDBConnected())
            return $this->respondWithUnknownError();


        $unique = $this->request->getVar('unique');
        $password = $this->request->getVar('password');


        $user = $this->usersModel->where('username', $unique)->orWhere('email', $unique)->first();

        if (!$user) {
            return $this->respondWith(
                success: 0,
                message: 'Username or Email does not exist',
                status: HTTPResponseCodes::NOT_ACCEPTABLE
            );
        }


        if (!verifyPassword($password, $user['password'])) {
            return $this->respondWith(
                success: 0,
                message: 'Invalid credentials',
                status: HTTPResponseCodes::NOT_ACCEPTABLE
            );
        }


        $loginData = $this->createAuthToken($user['id']);

        if (!$loginData)
            return $this->respondWithUnknownError();


        sendMail(
            to: $user['email'],
            subject: 'Login Alert',

            message: view('emails/login_alert', [
                'title' => 'Login Alert',
                'logoImage' => base_url('images/logo-dark.png'),
                'time' => date_format(date_create($loginData['created_at']), 'M jS, Y \a\t H:ia')
            ])
        );


        return $this->respondWith(
            message: 'Logged in successfully',
            data: [
                'token' => $loginData['token']
            ]
        );
    }

    public function user()
    {
        $authToken = $this->request->header('Authorization');

        if (!$authToken) {
            return $this->respondWith(
                success: 0,
                message: 'No authorization provided',
                status: HTTPResponseCodes::UNAUTHORIZED
            );
        }

        $authToken = $authToken->getValue();
        $authToken = is_string($authToken) ? str_replace('Bearer ', '', $authToken) : null;


        $loginData = $this->loginsModel->where('token', $authToken)->first();

        if (!$loginData) {
            return $this->respondWith(
                success: 0,
                message: 'Authentication failed',
                status: HTTPResponseCodes::UNAUTHORIZED
            );
        }

        if (isJWTTokenExpired($loginData['token'])) {
            return $this->respondWith(
                success: 0,
                message: 'Token expired',
                status: HTTPResponseCodes::UNAUTHORIZED
            );
        }


        $userData = $this->usersModel->find($loginData['user_id']);

        if (!$userData) {
            return $this->respondWith(
                success: 0,
                message: 'User you are trying to login either does not exist or has been deleted',
                status: HTTPResponseCodes::UNAUTHORIZED
            );
        }

        $this->usersModel->removePrivateFields($userData);


        return $this->respondWith(
            data: $userData
        );
    }
}
