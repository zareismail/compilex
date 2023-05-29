<?php

namespace Zareismail\Compilex\Concerns;

use ArrayAccess;

/**
 * The CompilesEchos trait provides methods to compile and retrieve echoed values.
 */
trait InteractsWithAttributes
{
    /**
     * Retrieve the statement value from the attributes.
     */
    public function retrieveStatement(array $attributes, string $statement)
    {
        preg_match('/^(?<var>(?:\w+(?:\s+or\s)?)+)(?<default>(?<quot>[\'"])[^{}]+(?P=quot))?$/', trim($statement), $matches);

        foreach ((array) preg_split('/\s+or\s*/', $matches['var'] ?? '') as $variable) {
            if ($value = $this->getAttribute($attributes, $variable)) {
                return $value;
            }
        }

        if (! isset($matches['default'])) {
            return null;
        }

        return trim($matches['default'], $matches['quot']) ?? null;
    }

    /**
     * Retrieve the value from an array or object using "dot" notation.
     */
    public function getAttribute(array $target, string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        // We assume quoted string as a value
        if (preg_match('/^((?<quotation>[\'"]).+(?P=quotation))$/', $key)) {
            return preg_replace('/(?<quotation>[\'"])(.+)(?P=quotation)/', '${2}', $key);
        }

        $keys = is_array($key) ? $key : explode('.', $key);

        foreach ($keys as $i => $segment) {
            unset($keys[$i]);

            if (is_null($segment)) {
                return $target;
            }

            if ((is_array($target) || $target instanceof ArrayAccess) && $this->hasAttribute($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return $default;
            }
        }

        return $target;
    }

    /**
     * Determine if the given key exists in the provided array.
     */
    public static function hasAttribute($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        if (is_float($key)) {
            $key = (string) $key;
        }

        return array_key_exists($key, $array);
    }
}
