<?php

namespace Zareismail\Compilex;

class Compilex
{
    use Concerns\CompilesConditionals;
    use Concerns\CompilesEchos;
    use Concerns\CompilesLoops;

    /**
     * Basic patterns used for matching.
     */
    protected $patterns = [
        'block' => '{%\s*(?<directive>\w+)\s+(?<statement>[^%}]+)\s*%}(?<expression>(\R|.(?!(?P=directive)[^%}]*%}))*){%\s*end(?P=directive)\s*%}',
        'echo' => '{{(?<expression>[^{}]+)}}',
    ];

    /**
     * Array to temporarily store the raw blocks found in the template.
     */
    protected $rawBlocks = [];

    /**
     * List of custom compilers.
     */
    protected static $directives = [];

    /**
     * Indicates whether debug mode is enabled or not.
     */
    protected $debugging = false;

    /**
     * Compile given string by the given attributes.
     */
    public function compile(string $value, array $attributes = []): string
    {
        $value = $this->storeRecursiveBlocks($value);

        $value = $this->compileString($value, $attributes);

        return $this->restoreRecursiveBlocks($value, $attributes);
    }

    /**
     * Store the identical nested blocks.
     */
    protected function storeRecursiveBlocks(string $value): string
    {
        $pattern = "/{$this->patterns['block']}/";

        while (preg_match($pattern, $value)) {
            $value = preg_replace_callback($pattern, fn ($matches) => $this->storeRawBlock(($matches[0])), $value);
        }

        return $value;
    }

    /**
     * Store a raw block and return a unique raw placeholder.
     */
    protected function storeRawBlock($value): string
    {
        return $this->getRawPlaceholder(
            array_push($this->rawBlocks, $value) - 1
        );
    }

    /**
     * Replace the raw placeholders with the original code stored in the raw blocks.
     */
    protected function restoreRecursiveBlocks(string $result, array $attributes = [], $reset = true): string
    {
        $pattern = '/'.$this->getRawPlaceholder('(\d+)').'/';

        while (preg_match($pattern, $result)) {
            $result = preg_replace_callback(
                $pattern,
                fn ($matches) => $this->compileString($this->rawBlocks[$matches[1]], $attributes),
                $result
            );
        }

        if ($reset) {
            $this->rawBlocks = [];
        }

        return $result;
    }

    /**
     * Get a placeholder to temporarily mark the position of raw blocks.
     */
    protected function getRawPlaceholder($replace)
    {
        return str_replace('#', $replace, '@__raw_block_#__@');
    }

    /**
     * Compile the given regex template contents.
     */
    public function compileString(string $value, array $attributes = []): string
    {
        $pattern = "/{$this->patterns['block']}/";

        do {
            $value = preg_replace_callback(
                $pattern,
                fn ($matches) => call_user_func($this->directive($matches['directive']), trim($matches['statement']), $matches['expression'], $attributes),
                $value
            );
        } while (preg_match($pattern, $value));

        return $this->compileEchos($value, $attributes);
    }

    /**
     * Find callback for given directive.
     */
    public function directive(string $directive)
    {
        $directiveCallback = 'compile'.(mb_strtoupper(mb_substr($directive, 0, 1))).mb_substr($directive, 1);

        if (method_exists($this, $directiveCallback)) {
            return [$this, $directiveCallback];
        }

        if (isset(static::$directives[$directive]) && is_callable(static::$directives[$directive])) {
            return static::$directives[$directive];
        }

        throw new \Exception("Directive `{$directive}` not found.");
    }

    /**
     * Register custom compiler.
     */
    public static function extends(string $directive, callable $compiler): static
    {
        static::$directives[$directive] = $compiler;

        return new static();
    }
}
