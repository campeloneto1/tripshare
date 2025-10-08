<?php

class GeneratePasswordUtil
{
    public static function generate(int $length = 12): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=';
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }
}