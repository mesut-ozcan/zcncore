<?php
namespace Core\Validation;

final class Validator
{
    /** @return array{valid:bool, data:array, errors:array<string,string>} */
    public static function make(array $data, array $rules): array
    {
        $errors = [];
        $clean  = [];

        foreach ($rules as $field => $ruleStr) {
            $rulesArr = is_array($ruleStr) ? $ruleStr : explode('|', (string)$ruleStr);
            $value = $data[$field] ?? null;

            foreach ($rulesArr as $rule) {
                [$name, $param] = self::parseRule($rule);

                if ($name === 'required') {
                    if ($value === null || $value === '') {
                        $errors[$field] = 'Bu alan zorunludur.'; break;
                    }
                }

                if ($value === null || $value === '') {
                    // required değilse boş değere diğer kuralları uygulama
                    continue;
                }

                if ($name === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Geçerli bir e-posta girin.'; break;
                }

                if ($name === 'string' && !is_string($value)) {
                    $errors[$field] = 'Metin olmalı.'; break;
                }

                if ($name === 'min' && is_string($value) && mb_strlen($value) < (int)$param) {
                    $errors[$field] = "En az {$param} karakter olmalı."; break;
                }

                if ($name === 'max' && is_string($value) && mb_strlen($value) > (int)$param) {
                    $errors[$field] = "En fazla {$param} karakter olmalı."; break;
                }

                if ($name === 'confirmed') {
                    $confirm = $data[$field.'_confirmation'] ?? null;
                    if ((string)$confirm !== (string)$value) {
                        $errors[$field] = 'Onaylanan değer eşleşmiyor.'; break;
                    }
                }
            }

            $clean[$field] = is_string($value) ? trim((string)$value) : $value;
        }

        return ['valid' => empty($errors), 'data' => $clean, 'errors' => $errors];
    }

    private static function parseRule(string $r): array
    {
        $r = trim($r);
        if (strpos($r, ':') !== false) {
            [$name, $param] = explode(':', $r, 2);
            return [trim($name), trim($param)];
        }
        return [$r, null];
    }
}
