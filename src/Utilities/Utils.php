<?php

namespace Ilias\Choir\Utilities;

class Utils
{
  public static function validatePhoneNumber(string|null $number = null): array
  {
    $arErr = [];
    if (empty($number)) {
      $arErr[] = 'user_number_required_message';
    }
    if (!preg_match('/^\+?[0-9]+$/', $number) || strlen(preg_replace('/\D/', '', $number)) < 10 || strlen(preg_replace('/\D/', '', $number)) > 14) {
      $arErr[] = 'user_invalid_number_message';
    }
    return $arErr;
  }

  public static function validateEmail(string|null $email = null): array
  {
    $arErr = [];
    if (empty($email)) {
      $arErr[] = 'user_email_required_message';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $arErr[] = 'user_invalid_email_message';
    }
    return $arErr;
  }

  public static function validatePassword(string|null $password = null): array
  {
    $arErr = [];
    if (strlen($password) < 8) {
      $arErr[] = 'user_password_length_message';
    }
    if (!preg_match('/[A-Z]/', $password)) {
      $arErr[] = 'user_password_uppercase_message';
    }
    if (!preg_match('/[a-z]/', $password)) {
      $arErr[] = 'user_password_lowercase_message';
    }
    return $arErr;
  }
}
