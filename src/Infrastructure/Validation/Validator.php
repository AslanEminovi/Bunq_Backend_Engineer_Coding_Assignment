<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation;

class Validator
{
    public static function validateUsername(string $username): array
    {
        $errors = [];
        $username = trim($username);

        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long';
        } elseif (strlen($username) > 50) {
            $errors[] = 'Username must not exceed 50 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, underscores, dots, and hyphens';
        }

        return $errors;
    }

    public static function validateGroupName(string $name): array
    {
        $errors = [];
        $name = trim($name);

        if (empty($name)) {
            $errors[] = 'Group name is required';
        } elseif (strlen($name) < 3) {
            $errors[] = 'Group name must be at least 3 characters long';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Group name must not exceed 100 characters';
        }

        return $errors;
    }

    public static function validateGroupDescription(?string $description): array
    {
        $errors = [];

        if ($description !== null && strlen($description) > 500) {
            $errors[] = 'Group description must not exceed 500 characters';
        }

        return $errors;
    }

    public static function validateMessageContent(string $content): array
    {
        $errors = [];
        $content = trim($content);

        if (empty($content)) {
            $errors[] = 'Message content is required';
        } elseif (strlen($content) > 2000) {
            $errors[] = 'Message content must not exceed 2000 characters';
        }

        return $errors;
    }

    public static function validateToken(string $token): array
    {
        $errors = [];
        $token = trim($token);

        if (empty($token)) {
            $errors[] = 'Token is required';
        } elseif (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            $errors[] = 'Invalid token format';
        }

        return $errors;
    }

    public static function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeInput(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = self::sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
