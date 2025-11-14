<?php

namespace App\Services;

use Mews\Purifier\Facades\Purifier;

class InputSanitizationService
{
    /**
     * Sanitize user input to prevent XSS attacks
     *
     * @param string|null $input
     * @return string|null
     */
    public function sanitize(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        // Use HTML Purifier to clean the input
        return Purifier::clean($input);
    }

    /**
     * Sanitize customer notes (allows basic formatting)
     *
     * @param string|null $notes
     * @return string|null
     */
    public function sanitizeNotes(?string $notes): ?string
    {
        if ($notes === null) {
            return null;
        }

        // Allow basic text formatting but remove dangerous tags
        $config = [
            'HTML.Allowed' => 'p,br,strong,em,u',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => true,
        ];

        return Purifier::clean($notes, $config);
    }

    /**
     * Sanitize address input (strict - no HTML allowed)
     *
     * @param string|null $address
     * @return string|null
     */
    public function sanitizeAddress(?string $address): ?string
    {
        if ($address === null) {
            return null;
        }

        // Strip all HTML tags for addresses
        return strip_tags($address);
    }

    /**
     * Sanitize array of inputs
     *
     * @param array $data
     * @param array $fields Fields to sanitize
     * @return array
     */
    public function sanitizeArray(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->sanitize($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Validate and sanitize JSON data
     *
     * @param string|array|null $json
     * @return array|null
     */
    public function sanitizeJson($json): ?array
    {
        if ($json === null) {
            return null;
        }

        if (is_string($json)) {
            $decoded = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }
            $json = $decoded;
        }

        if (!is_array($json)) {
            return null;
        }

        // Recursively sanitize array values
        return $this->sanitizeArrayRecursive($json);
    }

    /**
     * Recursively sanitize array values
     *
     * @param array $array
     * @return array
     */
    private function sanitizeArrayRecursive(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->sanitizeArrayRecursive($value);
            } elseif (is_string($value)) {
                $array[$key] = $this->sanitize($value);
            }
        }

        return $array;
    }

    /**
     * Check if input contains suspicious patterns
     *
     * @param string|null $input
     * @return bool
     */
    public function containsSuspiciousPatterns(?string $input): bool
    {
        if ($input === null) {
            return false;
        }

        $suspiciousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i', // Event handlers like onclick=, onload=
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/eval\s*\(/i',
            '/expression\s*\(/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}