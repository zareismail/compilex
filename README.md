# Introduction

- [Welcome](#introduction)
- [Getting Started](#getting-started)

# Displaying Data

- [Display Variables](#display-variables)
- [Multiple Variables](#multiple-variables)
- [Default Values](#default-values)

# Directives

- [Basic Of Directives](#basic-of-directives)
- [Conditional Statements](#conditional-statements)
- [Positive Statements](#positive-statements)
- [Loop](#loop)
- [Nested Statements](#nested-statements)

# Customization
- [Custom Directives](#custom-directives)
## Introduction

Welcome to the documentation for **Compilex** - a powerful PHP view compilation package designed to streamline the process of rendering dynamic templates. With Compilex, PHP developers can effortlessly compile templates, replace variables, and handle logical statements, all within a string context. This package aims to provide a flexible and efficient solution for generating dynamic content, allowing developers to focus on crafting exceptional user experiences.

In this documentation, you will find comprehensive information, examples, and guidelines to help you understand and harness the capabilities of Compilex. Whether you are new to view compilation or seeking to enhance your existing workflows, this documentation will serve as a valuable resource to navigate through the features and unleash the full potential of Compilex.

Let's get started and embark on a journey to simplify view compilation in PHP!

## Getting Started

You can install the `compilex` via Composer by running the following command:

```bash
composer require zareismail/compilex
```

After installation, you can use the compiler as follows:

```php
$compiler = new \Zareismail\Compilex\Compilex();

$result = $compiler->compile('complex string containing patterns', [/* Your variables */]);
```

## Display Variables

You may display data that is passed to your Compilex patterns by wrapping the variable in curly braces. You can display the contents of the `name` variable like so:

```
Hello, {{ name }}.
```

For exmaple:

```
echo $compiler->compile('Hello, {{ name }}.', ['name' => 'COMPILEX']);
// Output: Hello, COMPILEX.
```

### Multiple Variables

You can also display valid value of multiple variables by using the `or` operator:

```
Hello, {{ name or firstname or lastname }}.
```

For exmaple:

```php
echo $compiler->compile('Hello, {{ name or firstname or lastname }}.', ['firstname' => 'COMPILEX']);
// Output: Hello, COMPILEX.
```

### Default Values

If a variable is missing, you can display a default value using a `quoted string`:

```
Hello, {{ name or '--' }}.
```

For exmaple:

```php
echo $compiler->compile('Hello, {{ name or "--" }}.');
// Output: Hello, --.
```

## Basic Of Directives

By default, `Compilex` supports essential directives. All of the `Compilex` directives have the following syntax:

```
{% directive statement %} expression {% enddirective %}
```

Where the `directive` can be one of the default directives or any of the [custom directives](#custom-directives). The `statement` should satisfy the directive requirements, and the `expression` can be any renderable string.

### Conditional Statements

The conditional statements are useful to `render` or `hide` expressions based on a condition. By default, we have two conditional statements: `if` and `unless`. You can use the `if` directive to render an enclosed expression if the condition is true, and the `unless` directive to render an enclosed expression if the condition is false.

The conditional statements follow the below structure:

```
{% if leftOperand comparator rightOperand %} expression {% endif %}
```

```
{% unless leftOperand comparator rightOperand %} expression {% endunless %}
```

In addition, the conditional statements support the following comparison operators:

- Equality Operators:`=`, `==`, `eq`, `equal`, `is`
- Inequality Operators: `>`, `gt`, `greater than`
- Partial Equality Operators: `>=`, `gte`, `greater than or equal`

You can generate other comparisons by changing the directive (`if`/`unless`). Here are some examples:

```php
echo $compiler->compile('{% if a > b %} a is greater than b {% endif %}', ['a' => 1, 'b' => 2]);
// Output: a is greater than b

echo $compiler->compile('{% unless b > a %} a is greater than b {% endunless %}', ['a' => 1, 'b' => 2]);
// Output: a is greater than b

echo $compiler->compile('{% if a == b %} a is equal to b {% endif %}', ['a' => 1, 'b' => 1]);
// Output: a is equal to b

echo $compiler->compile('{% unless a == b %} a is not equal to b {% endunless %}', ['a' => 1, 'b' => 2]);
// Output: a is not equal to b
```

### Positive Statements

Sometimes you need to render conditional statements only if a variable has a valid value. For this situation, you can change the structure of the conditional statements as follows:

```
// to render enclosed epression for valid conditions
{% if variableName %} expression {% endif %}
```

```
// to render enclosed epression for invalid conditions
{% unless variableName %} expression {% endunless %}
```

for exmaple:

```php
echo $compiler->compile('{% if a %} a has a valid value {% endif %}', ['a' => true]);
// Output: a has a valid value

echo $compiler->compile('{% unless b %} a doesn't have a valid value {% endunless %}', ['a' => false]);
// Output: a doesn't have a valid value
```

### Loop

Compilex also supports `loop` statements with the following structure:

```
{% each valueName, indexName of/in variableName %} expression {% endeach %}
```

The `valueName` and `indexName` in the loop structure hold the `value` and `index` of your iterative variable. You can access the loop item and index inside the loop expression using these names. The `variableName` is the name of the attribute that holds your loop data, and `of` and `in` are static keywords of the loop structure. You can also omit passing the `index` name, in which case you can access the loop `index` using the `index` keyword.

Here are some examples:

```php
echo $compiler->compile('{% each item, key of items %} index {{ key }} holds {{ item }}, {% endeach %}', ['items' => [1,2]]);
// Output: index 0 holds 1, index 1 holds 2,

echo $compiler->compile('{% each name in names %} The {{ index }} name is: \'{{ name }}\', {% endeach %}', ['names' => ['Jack', 'Joe']]);
// Output: The 0 name is: 'Jack', The 1 name is: 'Joe',
```

## Nested Statements

One of the great features of `Compilex` is supporting nested directives. This means you can use any of the statements inside other statements, and you can even use statements inside themselves. Here are some examples:

```php
echo $compiler->compile('{% each item, key of items %} {% if key == 0 %} {{ item }} {% endif %} {% endeach %}', ['items' => [1,2]]);
// Output: 1

echo $compiler->compile('{% each numbers of groupedNumbers %} {% each number of numbers %} {{ number }}, {% endeach %} {% endeach %}', ['groupedNumbers' => [[1,2], [3,4]]]);
// Output: 1, 2, 3, 4,

echo $compiler->compile('{% if a > b %} {% if c > d %} I'm here {% endif %} {% endif %}', ['a' => 2, 'b' => 1, 'c' => 3, 'd' => 2]);
// Output: I'm here
```

## Custom Directives

If you need additional directives, you can easily define custom directives using the `extend` method. Here's an example:

```php
$compiler->extend('any', function ($operand, $expression, $attributes = []) {
    foreach ((array) explode(',', $operand) as $attribute) {
        if ($this->hasAttribute($attribute) && $this->getAttribute($attribute)) {
            return $expression;
        }
    }
    return null;
});
```

You can use your custom directive like this:

```php
echo $compiler->compile('{% any a,b %} my directive is working {% endany %}', ['a' => false, 'b' => true]);
// Output: my directive is working
```

That's it! With these directives and examples, you should be able to harness the power of `Compilex` in your PHP view compilation.

# Happy coding!
