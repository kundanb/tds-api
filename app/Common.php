<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * --------------------------------------------------------------------
 * Get image data URI
 * --------------------------------------------------------------------
 */

function getImageDataURI(string $imageFilename)
{
    $imageBase64 = base64_encode(file_get_contents($imageFilename));
    return 'data: ' . mime_content_type($imageFilename) . ';base64,' . $imageBase64;
}

/**
 * --------------------------------------------------------------------
 * Password Hashing
 * --------------------------------------------------------------------
 */

function hashPassword(string $password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword(string $password, string $hashedPassword)
{
    return password_verify($password, $hashedPassword);
}

/**
 * --------------------------------------------------------------------
 * JWT Authentication
 * --------------------------------------------------------------------
 */

function getJWTKey()
{
    return getenv('jwt.key');
}

function createJWTToken(int|string $duration = null)
{
    $dateTime = new DateTime;

    $payload = [
        'iss' => base_url(),
        'iat' => $dateTime->getTimestamp(),
    ];

    if ($duration != null) {
        if (is_string($duration)) {
            $dateTime->add(DateInterval::createFromDateString($duration));
        } else {
            $dateTime->add(new DateInterval($duration));
        }

        $payload['exp'] = $dateTime->getTimestamp();
    }

    return JWT::encode($payload, getJWTKey(), 'HS256');
}

function getJWTData(string $token)
{
    return JWT::decode($token, new Key(getJWTKey(), 'HS256'));
}

function isJWTTokenExpired(string $token)
{
    $payload = getJWTData($token);
    $datetime = new DateTime;

    return $payload->exp <= $datetime->getTimestamp();
}

/**
 * --------------------------------------------------------------------
 * Email
 * --------------------------------------------------------------------
 */

function sendMail(string $to, string $subject, string $message)
{
    $emailService = \Config\Services::email();
    $emailService->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
    $emailService->setMailType('html');
    $emailService->setTo($to);
    $emailService->setSubject($subject);
    $emailService->setMessage($message);
    $emailService->send();
}
