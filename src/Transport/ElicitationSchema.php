<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

/** The flat, primitive JSON Schema subset used by MCP elicitation forms. */
final class ElicitationSchema
{
    /** @param array<string, mixed> $schema */
    public static function supports(array $schema): bool
    {
        if (($schema['type'] ?? null) !== 'object' || ! is_array($schema['properties'] ?? null)) {
            return false;
        }

        if (array_diff(array_keys($schema), ['$schema', 'type', 'properties', 'required']) !== []) {
            return false;
        }

        $required = $schema['required'] ?? [];
        if (! is_array($required) || ! array_is_list($required)) {
            return false;
        }
        foreach ($required as $name) {
            if (! is_string($name) || ! array_key_exists($name, $schema['properties'])) {
                return false;
            }
        }

        foreach ($schema['properties'] as $field) {
            if (! is_array($field) || ! in_array($field['type'] ?? null, ['string', 'integer', 'number', 'boolean', 'array'], true)) {
                return false;
            }
            $allowed = match ($field['type']) {
                'string' => ['minLength', 'maxLength', 'format', 'enum', 'enumNames', 'oneOf'],
                'integer', 'number' => ['minimum', 'maximum'],
                'array' => ['items', 'minItems', 'maxItems'],
                default => [],
            };
            if (array_diff(array_keys($field), [...$allowed, 'type', 'title', 'description', 'default']) !== []) {
                return false;
            }
            foreach (['title', 'description'] as $key) {
                if (isset($field[$key]) && ! is_string($field[$key])) {
                    return false;
                }
            }
            foreach (['minLength', 'maxLength', 'minItems', 'maxItems'] as $key) {
                if (isset($field[$key]) && (! is_int($field[$key]) || $field[$key] < 0)) {
                    return false;
                }
            }
            foreach (['minimum', 'maximum'] as $key) {
                if (isset($field[$key]) && ! is_int($field[$key]) && ! is_float($field[$key])) {
                    return false;
                }
            }
            if (isset($field['format']) && ! in_array($field['format'], ['email', 'uri', 'date', 'date-time'], true)) {
                return false;
            }
            $choices = $field['type'] === 'array' ? ($field['items'] ?? null) : $field;
            if (! is_array($choices)) {
                return false;
            }
            if ($field['type'] === 'array' && array_diff(array_keys($choices), ['type', 'enum', 'anyOf']) !== []) {
                return false;
            }
            if (isset($choices['enum'])) {
                if (! is_array($choices['enum']) || ! array_is_list($choices['enum']) || $choices['enum'] === []) {
                    return false;
                }
                foreach ($choices['enum'] as $choice) {
                    if (! is_string($choice)) {
                        return false;
                    }
                }
            }
            foreach (['oneOf', 'anyOf'] as $key) {
                if (! isset($choices[$key])) {
                    continue;
                }
                if (! is_array($choices[$key]) || ! array_is_list($choices[$key]) || $choices[$key] === []) {
                    return false;
                }
                foreach ($choices[$key] as $choice) {
                    if (! is_array($choice) || ! is_string($choice['const'] ?? null) || ! is_string($choice['title'] ?? null)) {
                        return false;
                    }
                }
            }
            if (isset($field['enumNames'])) {
                if (! is_array($field['enumNames']) || count($field['enumNames']) !== count($field['enum'] ?? [])) {
                    return false;
                }
                foreach ($field['enumNames'] as $label) {
                    if (! is_string($label)) {
                        return false;
                    }
                }
            }
            if ($field['type'] === 'array' && ! isset($choices['enum']) && ! isset($choices['anyOf'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return list<array{value: string, label: string}>
     */
    public static function options(array $field): array
    {
        $choices = $field['type'] === 'array' ? $field['items'] : $field;
        $options = [];
        foreach ($choices['enum'] ?? [] as $index => $value) {
            $options[] = ['value' => $value, 'label' => $field['enumNames'][$index] ?? $value];
        }
        foreach ($choices['oneOf'] ?? $choices['anyOf'] ?? [] as $choice) {
            $options[] = ['value' => $choice['const'], 'label' => $choice['title']];
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $content
     *
     * @throws \InvalidArgumentException
     */
    public static function validate(array $schema, array $content): void
    {
        if (! self::supports($schema)) {
            throw new \InvalidArgumentException('This form is not supported.');
        }
        if (array_diff(array_keys($content), array_keys($schema['properties'])) !== []) {
            throw new \InvalidArgumentException('The answer contains unknown fields.');
        }
        foreach ($schema['properties'] as $name => $field) {
            $label = $field['title'] ?? (string) $name;
            if (! array_key_exists($name, $content)) {
                if (in_array((string) $name, $schema['required'] ?? [], true)) {
                    throw new \InvalidArgumentException('Answer '.$label.'.');
                }

                continue;
            }
            $value = $content[$name];
            $valid = match ($field['type']) {
                'string' => is_string($value),
                'boolean' => is_bool($value),
                'integer' => is_int($value),
                'number' => (is_int($value) || is_float($value)) && is_finite((float) $value),
                'array' => is_array($value) && array_is_list($value),
            };
            if (! $valid) {
                throw new \InvalidArgumentException('Enter a valid '.$field['type'].' for '.$label.'.');
            }
            $options = self::options($field);
            if ($options !== []) {
                foreach (is_array($value) ? $value : [$value] as $choice) {
                    if (! in_array($choice, array_column($options, 'value'), true)) {
                        throw new \InvalidArgumentException('Choose an available option for '.$label.'.');
                    }
                }
            }
            $size = is_string($value) ? preg_match_all('/./us', $value) : (is_array($value) ? count($value) : $value);
            foreach (['minLength', 'minItems', 'minimum'] as $key) {
                if (isset($field[$key]) && $size < $field[$key]) {
                    throw new \InvalidArgumentException($label.' must be at least '.$field[$key].'.');
                }
            }
            foreach (['maxLength', 'maxItems', 'maximum'] as $key) {
                if (isset($field[$key]) && $size > $field[$key]) {
                    throw new \InvalidArgumentException($label.' must be at most '.$field[$key].'.');
                }
            }
            if (is_string($value) && isset($field['format']) && ! self::matchesFormat($value, $field['format'])) {
                throw new \InvalidArgumentException('Enter a valid '.$field['format'].' for '.$label.'.');
            }
        }
    }

    private static function matchesFormat(string $value, string $format): bool
    {
        return match ($format) {
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'uri' => preg_match('/^[a-z][a-z0-9+.-]*:[^\s]+$/iD', $value) === 1,
            'date' => self::validDate($value),
            'date-time' => preg_match('/^\d{4}-\d{2}-\d{2}[Tt]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[Zz]|[+-]\d{2}:\d{2})$/D', $value) === 1
                && self::validDate(substr($value, 0, 10))
                && date_parse($value)['warning_count'] === 0 && date_parse($value)['error_count'] === 0,
            default => false,
        };
    }

    private static function validDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
