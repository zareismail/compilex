<?php

it('detects blocs', fn () => compiler()->compile('{% directive statement %} expresion {% enddirective %}', []))->throws(\Exception::class);
it('detects empty blocs', fn () => compiler()->compile('{% directive statement %}{% enddirective %}', []))->throws(\Exception::class);

it('ignores blocs without statement', function () {
    $pattern = '{% directive %} expresion {% enddirective %}';

    expect(compiler()->compile($pattern, []))->toBe($pattern);
});

it('ignores unclosed blocks', function () {
    $pattern = '{% directive %} expresion';

    expect(compiler()->compile($pattern, []))->toBe($pattern);
});

it('detects statement', function () {
    $statement = 'MyStatement';
    $directive = 'MyDirective';
    $copiler = compiler()->extends($directive, fn ($statement) => $statement);

    expect($copiler->compile("{% {$directive} {$statement} %}{% end{$directive} %}", []))->toBe($statement);
});

it('detects expression', function () {
    $statement = 'MyStatement';
    $directive = 'MyDirective';
    $experssion = strval(rand());
    $copiler = compiler()->extends($directive, fn ($statement, $experssion) => $experssion);

    expect($copiler->compile("{% {$directive} {$statement} %}{$experssion}{% end{$directive} %}", []))->toBe($experssion);
});

it('possible to extend', function () {
    $directive = 'MyDirective';
    $copiler = compiler()->extends($directive, fn () => $directive);

    expect($copiler->compile("{% {$directive} MyStatement %}{% end{$directive} %}", []))->toBe($directive);
});

// echo variabes
it('echo variables', function () {
    expect(compiler()->compile('{{ key }}', ['key' => 'value']))->toBe('value');
});
it('echo simple default values', function () {
    expect(compiler()->compile("{{ key or 'value' }}", []))->toBe('value');
});
it('echo variable default values', function () {
    expect(compiler()->compile('{{ key or default }}', ['default' => 'value']))->toBe('value');
});
it('echo default values with missing default variable values', function () {
    expect(compiler()->compile("{{ key or default or 'value' }}", []))->toBe('value');
});

// conditionals
it('detects conditional statements without values', function () {
    foreach (conditionalDirectives() as $directive => $statement) {
        expect(fn () => compiler()->compile("{% {$directive} is %}{$statement}{% end{$directive} %}", []))->toThrow(\Exception::class);
    }
});

it('detects conditional statements without valid comnparison', function () {
    foreach (conditionalDirectives() as $directive => $statement) {
        expect(fn () => compiler()->compile("{% {$directive} val comparison value %}sdfsd{% end{$directive} %}", []))->toThrow(\Exception::class);
    }
});

test('conditional statements comparison with equal values', function () {
    foreach (conditionalDirectives() as $directive => $statement) {
        foreach (equalityComparators() as $comparison) {
            $pattern = "{% {$directive} a {$comparison} a %}{$directive}-{$comparison}{% end{$directive} %}";

            expect(compiler()->compile($pattern, ['a' => true]))->toBe(
                $statement === 'positive' ? "{$directive}-{$comparison}" : ''
            );
        }

    }
});

test('conditional statements comparison with unequal values', function () {
    foreach (conditionalDirectives() as $directive => $statement) {
        foreach (fullyEqualityComparators() as $comparison) {
            $pattern = "{% {$directive} a {$comparison} b %}{$directive}-{$comparison}{% end{$directive} %}";

            expect(compiler()->compile($pattern, ['a' => true, 'b' => false]))->toBe(
                $statement === 'negetive' ? "{$directive}-{$comparison}" : ''
            );
        }

    }
});

test('conditional statements comparison with equal values and inequal operators', function () {
    foreach (conditionalDirectives() as $directive => $statement) {
        foreach (fullyInequalityComparators() as $comparison) {
            $pattern = "{% {$directive} a {$comparison} a %}{$directive}-{$comparison}{% end{$directive} %}";

            expect(compiler()->compile($pattern, ['a' => true]))->toBe(
                $statement === 'negetive' ? "{$directive}-{$comparison}" : ''
            );
        }

    }
});

it('detects nested conditional statements', function () {
    foreach (conditionalDirectives() as $directive => $statement) {
        foreach (conditionalDirectives() as $directiveNested => $statementNested) {
            foreach (equalityComparators() as $comparison) {
                $nestedPattern = "{% {$directiveNested} b {$comparison} b %}{$directiveNested}-{$comparison}{% end{$directiveNested} %}";
                $pattern = "{% {$directive} a {$comparison} a %}{$nestedPattern}{% end{$directive} %}";

                expect(compiler()->compile($pattern, ['a' => true, 'b' => true]))->toBe(
                    $statement === 'positive' && $statementNested === 'positive' ? "{$directive}-{$comparison}" : ''
                );
            }
        }
    }
});

it('show value nested in conditional statement', function () {
    foreach (conditionalDirectives() as $directive => $statement) {
        foreach (equalityComparators() as $comparison) {
            $pattern = "{% {$directive} a {$comparison} a %}{{ value }}{% end{$directive} %}";

            expect(compiler()->compile($pattern, ['a' => true, 'value' => 'value']))->toBe(
                $statement === 'positive' ? 'value' : ''
            );
        }

    }
});

// loops
it('detects invalid each statements', function () {
    expect(fn () => compiler()->compile('{% each statement %}expression{% endeach %}', []))->toThrow(\Exception::class);
});

it('detects valid each statements', function () {
    foreach (loops() as $loop) {

        expect(compiler()->compile("{% each item {$loop} items %}expression{% endeach %}", []))->toBe('');
    }
});

it('detects loop values', function () {
    $items = [rand()];
    foreach (loops() as $loop) {
        expect(compiler()->compile("{% each item {$loop} items %}{{ item }}{% endeach %}", compact('items')))->toBe(strval($items[0]));
    }
});

it('detects loop keys', function () {
    $items = [rand() => rand()];
    $key = array_keys($items)[0];

    foreach (loops() as $loop) {
        expect(compiler()->compile("{% each item,key {$loop} items %}{{ key }}{% endeach %}", compact('items')))->toBe(strval($key));
    }
});

it('detects nested loops', function () {
    $items = [rand()];
    $nestedItems = [rand()];
    foreach (loops() as $loop) {
        foreach (loops() as $nestedLoop) {
            $nestedPattern = "{% each item {$nestedLoop} nestedItems %}{{ item }}{% endeach %}";
            $pattern = "{% each item {$loop} items %}{$nestedPattern}{% endeach %}";

            expect(compiler()->compile($pattern, compact('items', 'nestedItems')))->toBe(strval($nestedItems[0]));
        }
    }
});

it('has access to parent values', function () {
    $items = [[rand()]];
    foreach (loops() as $loop) {
        foreach (loops() as $nestedLoop) {
            $pattern = "{% each item {$loop} items %}{% each value {$loop} item %}{{ value }}{% endeach %}{% endeach %}";

            expect(compiler()->compile($pattern, compact('items')))->toBe(strval($items[0][0]));
        }
    }
});

// nesteds

it('detects loops inside the conditional statements', function () {
    foreach (conditionalDirectives() as $directive => $statement) {
        foreach (equalityComparators() as $comparison) {
            $items = [rand()];
            foreach (loops() as $loop) {
                $loop = "{% each value {$loop} items %}{{ value }}{% endeach %}";
                $pattern = "{% {$directive} a {$comparison} a %}{$loop}{% end{$directive} %}";

                expect(compiler()->compile($pattern, ['a' => true, 'items' => $items]))->toBe(
                    $statement === 'positive' ? strval($items[0]) : ''
                );
            }
        }

    }
});

it('detects conditional statements inside the loops', function () {
    $items = [rand()];
    foreach (loops() as $loop) {
        foreach (conditionalDirectives() as $directive => $statement) {
            foreach (equalityComparators() as $comparison) {
                $conditionalStatemnt = "{% {$directive} a {$comparison} a %}{{ value }}{% end{$directive} %}";
                $pattern = "{% each value {$loop} items %}{$conditionalStatemnt}{% endeach %}";

                expect(compiler()->compile($pattern, ['a' => true, 'items' => $items]))->toBe(
                    $statement === 'positive' ? strval($items[0]) : ''
                );
            }
        }
    }
});
