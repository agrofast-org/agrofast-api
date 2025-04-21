<?php

namespace App\Models;

use App\Models\System\ErrorLog;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class Error
{
    public readonly string $message;

    public readonly null|array|string $errors;
    public readonly ?array $data;
    public readonly int $code;

    public function __construct(
        string $message,
        null|array|string $errors = null,
        ?array $data = null,
        int $code = 400
    ) {
        $this->message = $message;
        $this->errors = self::translateErrors($errors);
        $this->data = $data;
        $this->code = $code;

        ErrorLog::create([
            'url' => request()->url(),
            'error_message' => $message,
            'stack_trace' => '',
        ]);
    }

    public static function translateErrors(null|array|string $errors): null|array|string
    {
        if (is_null($errors) || is_string($errors)) {
            $key = $errors;
            if (!$key) {
                return $key;
            }
            if (Lang::has("validation.{$key}")) {
                return trans("validation.{$key}");
            }
            if (Lang::has($key)) {
                return trans($key);
            }

            return ucfirst(str_replace('_', ' ', $key));
        }

        $translated = [];

        foreach ($errors as $field => $rulesOrKey) {
            $attribute = self::getFriendlyAttribute($field);

            $rules = is_array($rulesOrKey) ? $rulesOrKey : [$rulesOrKey];

            foreach ($rules as $rule) {
                if (Lang::has("validation.{$rule}")) {
                    $msg = trans("validation.{$rule}", ['attribute' => $attribute]);
                } elseif (Lang::has($rule)) {
                    $msg = trans($rule, ['attribute' => $attribute]);
                } else {
                    $msg = ucfirst(str_replace('_', ' ', $rule));
                }

                $translated[$field][] = $msg;
            }
        }

        return $translated;
    }

    protected static function getFriendlyAttribute(string $field): string
    {
        if (Lang::has("validation.attributes.{$field}")) {
            return trans("validation.attributes.{$field}");
        }

        return Str::of($field)
            ->snake()
            ->replace('_', ' ')
            ->ucfirst()
            ->toString()
        ;
    }
}
