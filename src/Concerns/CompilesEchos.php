<?php

namespace Zareismail\Compilex\Concerns;

/**
 * The CompilesEchos trait provides methods to compile and retrieve echoed values.
 */
trait CompilesEchos
{
    use InteractsWithAttributes;

    /**
     * Compile Compilex echos into corresponding values.
     */
    public function compileEchos(string $expression, array $attributes): string
    {
        $pattern = "/{$this->patterns['echo']}/";

        try {
            return (string) preg_replace_callback($pattern, fn ($matches) => $this->retrieveStatement($attributes, $matches['expression']), $expression);
        } catch (\Exception $e) {
            return "{$e->getMessage()} at {$expression}";
        }
    }
}
