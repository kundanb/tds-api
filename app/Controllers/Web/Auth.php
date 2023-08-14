<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    public function register()
    {
        // extract request values
        $username = $this->request->getVar('username');
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        // create validation
        $validation = \Config\Services::validation();

        // set validation rules
        $validation->setRule('username', 'Username', 'required|min_length[6]|max_length[30]');
        $validation->setRule('email', 'Email', 'required|valid_email');
        $validation->setRule('password', 'Password', 'required|min_length[8]');

        // validate request
        $validation->withRequest($this->request)->run();

        // respond with error (if validation fails)
        if (!empty($validation->getErrors())) {
            return $this->respondWith(
                success: 0,
                message: $validation->getErrors()
            );
        }

        // return view('emails\user_registration', [
        //     'title' => 'Welcome to TDS Era',
        //     'logoImage' => getImageDataURI('logo-dark.png'),
        //     'username' => $username
        // ]);

        // send notification email to user
        sendMail(
            $email,
            'Welcome to TDS Era',

            view('emails\user_registration', [
                'title' => 'Welcome to TDS Era',
                'logoImage' => base_url('images/logo-dark.png'),
                'username' => $username
            ])
        );

        // respond with error (if username already taken)
        if ($this->usersModel->where('username', $username)->withDeleted()->countAllResults()) {
            return $this->respondWith(
                success: 0,
                message: 'Username already taken.'
            );
        }

        // respond with error (if email already exists)
        if ($this->usersModel->where('email', $email)->withDeleted()->countAllResults()) {
            return $this->respondWith(
                success: 0,
                message: 'Email already registered.'
            );
        }

        // hash password
        $hashedPassword = hashPassword($password);

        // insert user data into db
        $inserted = $this->usersModel->insert([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword
        ], false);

        // respond with error (if couldn't insert user data into db)
        if (!$inserted) {
            return $this->respondWith(
                success: 0,
                message: 'Couldn\'t register your account.'
            );
        }

        // create auth token
        $token = createJWTToken('30 days');

        // insert auth token into db
        $inserted = $this->loginsModel->insert([
            'user_id' => $this->usersModel->getInsertID(),
            'token' => $token
        ], false);

        // respond with success (without auth token) (if couldn't insert login token into db)
        if (!$inserted) {
            return $this->respondWith(
                message: 'Registered successfully.'
            );
        }

        // respond with success (with login token)
        return $this->respondWith(
            message: 'Registered successfully.',
            data: [
                'token' => $token
            ]
        );
    }
}
