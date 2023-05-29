<?php

namespace Zareismail\Compilex\Concerns;

trait CompilesLoops
{
    use InteractsWithAttributes;

    /**
     * Compile the if statements into valid PHP.
     */
    protected function compileEach($operand, $expression, $attributes = []): string
    {

        [$items, $name, $index] = $this->compileLoopStatement($operand, $attributes);

        $value = '';
        foreach ($items as $key => $item) {
            $attributes[$name] = $item;
            $attributes[$index] = $key;
            $attributes['parent'] = [
                $name => $item,
                $index => $key,
            ];

            $value .= $this->compileString(
                $this->restoreRecursiveBlocks($expression, $attributes, false),
                $attributes
            );
        }

        return strval($value);
    }

    /**
     * Retrive loop data from statement.
     */
    public function compileLoopStatement(string $statement, array $attributes = []): array
    {
        preg_match('/(?<name>\w+)(?:\s*,\s*(?<index>\w+))?\s+(?:of|in)\s+(?<items>\w+)/', $statement, $matches);

        if (isset($matches['name']) && isset($matches['items'])) {
            return [
                (array) $this->getAttribute($attributes, trim($matches['items'])),
                trim($matches['name']),
                trim($matches['index'] ?: 'index'),
            ];
        }

        throw new \Exception('Invalid loop statement.');
    }
}
