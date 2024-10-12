<?php

namespace Ilias\Choir\Utilities;

class Utils
{
  public static function validatePhoneNumber(string $number): bool
  {
    // Check if the phone number is not empty
    if (empty($number)) {
      return false;
    }

    // Check if the phone number contains only digits and optional leading '+'
    if (!preg_match('/^\+?[0-9]+$/', $number)) {
      return false;
    }

    // Check the length of the phone number (10 to 14 digits)
    if (strlen(preg_replace('/\D/', '', $number)) < 10 || strlen(preg_replace('/\D/', '', $number)) > 14) {
      return false;
    }

    return true;
  }
}
