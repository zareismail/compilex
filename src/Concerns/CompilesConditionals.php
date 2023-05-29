<?php

namespace Zareismail\Compilex\Concerns;

/**
 * The CompilesConditionals trait provides methods to compile and evaluate conditional statements.
 */
trait CompilesConditionals
{
    use InteractsWithAttributes;

    /**
     * Compile the if statements into valid PHP.
     *
     * @return mixed|null The compiled expression or null.
     */
    protected function compileIf($operand, $expression, $attributes = [])
    {
        // Attribute found and has valid value
        if ($this->hasAttribute($attributes, $operand)) {
            return boolval($this->getAttribute($attributes, $operand)) ? $expression : null;
        }

        return $this->evaluateStatement($operand, $attributes) ? $expression : null;
    }

    /**
     * Compile the unless statements into valid PHP.
     *
     * @return mixed|null The compiled expression or null.
     */
    protected function compileUnless($operand, $expression, $attributes = [])
    {
        return $this->compileIf($operand, $expression, $attributes) ? null : $expression;
    }

    /**
     * Evaluate the condition statement.
     *
     * @throws \Exception If the statement is invalid.
     */
    public function evaluateStatement(string $condition, $attributes): bool
    {
        [$comparison, $left, $right] = $this->compileConditionStatement($condition, $attributes);

        return $this->compare($left, $right, $comparison);
    }

    /**
     * Compile the condition statement.
     */
    public function compileConditionStatement(string $statement, array $attributes = []): array
    {
        $pattern = "(?<var>(?:\w+|\d+|['\"])+(:(?<cast>\w+))*)\s+(?<comparison>(?:\w+\s?)+|[=>]+)\s+(?<value>(?:\w+|\d+|['\"])+)";

        preg_match("/{$pattern}/", $statement, $matches);

        if (isset($matches['value'])) {
            return [
                trim($matches['comparison']),
                $this->getAttribute($attributes, trim($matches['var'])),
                $this->getAttribute($attributes, trim($matches['value'])),
            ];
        }

        throw new \Exception('Invalid statement '.$statement.PHP_EOL.__METHOD__);
    }

    /**
     * Compare the operands based on the comparison operator.
     */
    public function compare(mixed $leftOperand, mixed $rightOperand, string $comparison): bool
    {
        switch ($comparison) {
            case '=':
            case '==':
            case 'eq':
            case 'equal':
            case 'is':
                return $leftOperand === $rightOperand;

            case '>':
            case 'gt':
            case 'greater than':
                return $leftOperand > $rightOperand;

            case '>=':
            case 'gte':
            case 'greater than or equal':
                return $leftOperand >= $rightOperand;
        }

        throw new \Exception('Invalid comparison '.$comparison.PHP_EOL.__METHOD__);
    }
}
