<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\Fixer\AttributeNotation;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\AttributeNotation\GeneralAttributeRemoveFixer
 *
 * @requires PHP 8.0
 *
 * @phpstan-import-type _AutogeneratedInputConfiguration from \PhpCsFixer\Fixer\AttributeNotation\GeneralAttributeRemoveFixer
 *
 * @phpstan-type _InputConfiguration array{
 *    attributes?: list<string>,
 *   }
 *
 * @extends AbstractFixerTestCase<\PhpCsFixer\Fixer\AttributeNotation\GeneralAttributeRemoveFixer>
 *
 * @author Raffaele Carelle <raffaele.carelle@gmail.com>
 */
final class GeneralAttributeRemoveFixerTest extends AbstractFixerTestCase
{
    /**
     * @param _AutogeneratedInputConfiguration $configuration
     *
     * @dataProvider provideFixCases
     */
    public function testFix(string $expected, ?string $input = null, array $configuration = []): void
    {
        $this->fixer->configure($configuration);

        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<string, array{0: string, 1?: string, 2?: _InputConfiguration}>
     */
    public static function provideFixCases(): iterable
    {
        yield 'Explicit in namespace' => [
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[Foo(4, \'baz qux\')]
            #[AB\Baz(prop: \'baz\')]
            #[\A\B\Qux()]
            #[Corge]
            function f() {}
            ',
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[Foo(4, \'baz qux\')]
            #[BarAlias(3)]
            #[AB\Baz(prop: \'baz\')]
            #[\A\B\Qux()]
            #[A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\')]
            #[Corge]
            function f() {}
            ',
            [
                'attributes' => ['A\B\Bar', 'Test\A\B\Quux'],
            ],
        ];

        yield 'Explicit in global namespace' => [
            '<?php
            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\')]
            #[AB\Baz(prop: \'baz\')]
            #[Foo(4, \'baz qux\')]
            function f() {}
            ',
            '<?php
            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\')]
            #[BarAlias(3)]
            #[AB\Baz(prop: \'baz\')]
            #[Foo(4, \'baz qux\')]
            #[\A\B\Qux()]
            #[Corge]
            function f() {}
            ',
            [
                'attributes' => ['\A\B\Qux', '\Corge', 'A\B\Bar'],
            ],
        ];

        yield 'Multiple namespaces' => [
            '<?php
            namespace Test
            {
                use A\B\Foo;
                use A\B\Bar as BarAlias;

                function f() {}
            }

            namespace Test2
            {
                use A\B\Bar as BarAlias;
                use A\B as AB;

                #[\A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\')]
                function f() {}
            }

            namespace Test
            {
                use A\B\Foo;
                use A\B\Bar as BarAlias2;

                function f2() {}
            }

            namespace
            {
                use A\B\Foo;
                use A\B\Bar as BarAlias3;

                function f() {}
            }
            ',
            '<?php
            namespace Test
            {
                use A\B\Foo;
                use A\B\Bar as BarAlias;

                #[AB\Baz(prop: \'baz\')]
                #[Foo(4, \'baz qux\')]
                #[BarAlias(3)]
                function f() {}
            }

            namespace Test2
            {
                use A\B\Bar as BarAlias;
                use A\B as AB;

                #[BarAlias(3)]
                #[AB\Baz(prop: \'baz\')]
                #[\A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\')]
                function f() {}
            }

            namespace Test
            {
                use A\B\Foo;
                use A\B\Bar as BarAlias2;

                #[AB\Baz(prop: \'baz\')]
                #[Foo(4, \'baz qux\')]
                #[BarAlias2(3)]
                function f2() {}
            }

            namespace
            {
                use A\B\Foo;
                use A\B\Bar as BarAlias3;

                #[AB\Baz(prop: \'baz\')]
                #[Foo(4, \'baz qux\')]
                #[BarAlias3(3)]
                function f() {}
            }
            ',
            [
                'attributes' => ['A\B\Bar', 'Test\AB\Baz', 'A\B\Quux', 'A\B\Baz', 'A\B\Foo', '\AB\Baz'],
            ],
        ];

        yield 'With whitespaces' => [
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[   \A\B\Qux()   ]
            #[   BarAlias   (3)   ]
            #[   Corge   ]
            function f() {}
            ',
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[   AB\Baz   (prop: \'baz\')   ]
            #[   A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\')   ]
            #[   \A\B\Qux()   ]
            #[   BarAlias   (3)   ]
            #[   Corge   ]
            #[   Foo   (4, \'baz qux\')   ]
            function f() {}
            ',
            [
                'attributes' => ['A\B\Foo', 'Test\A\B\Quux', 'A\B\Baz'],
            ],
        ];

        yield 'With docblock' => [
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            /**
             * Start docblock
             */
            /**
             * AB\Baz docblock
             */
            #[AB\Baz(prop: \'baz\')]
            #[A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\')]
            #[BarAlias(3)]
            #[Foo(4, \'baz qux\')]
            /**
             * End docblock
             */
            class X
            {}

            function f2(/** Start docblock */#[Foo(4, \'baz qux\')] #[BarAlias(3)] /** End docblock */string $param) {}
            ',
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            /**
             * Start docblock
             */
            /**
             * AB\Baz docblock
             */
            #[AB\Baz(prop: \'baz\')]
            #[A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\')]
            #[\A\B\Qux()]
            #[BarAlias(3)]
            /**
             * Corge docblock
             */
            #[Corge]
            #[Foo(4, \'baz qux\')]
            /**
             * End docblock
             */
            class X
            {}

            function f2(/** Start docblock */#[Foo(4, \'baz qux\')] #[BarAlias(3)] #[\A\B\Qux()] /** Corge docblock */#[Corge] /** End docblock */string $param) {}
            ',
            [
                'attributes' => ['Test\Corge', '\A\B\Qux'],
            ],
        ];

        yield 'With comments' => [
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            function f() {}
            ',
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[/* comment */A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\') /* comment */]
            #[ /* comment */ BarAlias/* comment */(3)/* comment */]
            #[/* comment */ Corge/* comment */]
            #[/* comment */AB\Baz /* comment */ (prop: \'baz\') /* comment */ ]
            #[/* comment */Foo/* comment */(4, \'baz qux\') /* comment */ ]
            #[   /* comment */   \A\B\Qux()/* comment */]
            function f() {}
            ',
            [
                'attributes' => ['Test\A\B\Quux', 'A\B\Bar', 'Test\Corge', 'A\B\Baz', 'A\B\Foo', '\A\B\Qux'],
            ],
        ];

        yield 'With multiple attributes' => [
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[
                AB\Baz(prop: \'baz\'),
                A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\'),
                BarAlias(3),
                Corge
            ]
            class X
            {
                #[ AB\Baz(prop: \'baz\'), A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\'), BarAlias(3), Corge]
                public function y() {}
            }
            ',
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[
                AB\Baz(prop: \'baz\'),
                A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\'),
                \A\B\Qux(),
                BarAlias(3),
                Corge,
                Foo(4, \'baz qux\'),
            ]
            class X
            {
                #[ AB\Baz(prop: \'baz\'), A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\'), \A\B\Qux(), BarAlias(3), Corge,Foo(4, \'baz qux\')]
                public function y() {}
            }
            ',
            [
                'attributes' => ['A\B\Foo', '\A\B\Qux'],
            ],
        ];

        yield 'Multiline with no trailing comma' => [
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            class X
            {}
            ',
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[
                AB\Baz(prop: \'baz\'),
                A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\'),
                \A\B\Qux(),
                BarAlias(3),
                Corge,
                Foo(4, \'baz qux\')
            ]
            class X
            {}
            ',
            [
                'attributes' => ['A\B\Foo', '\A\B\Qux', 'A\B\Baz', 'Test\A\B\Quux', 'A\B\Bar', 'Test\Corge'],
            ],
        ];

        yield 'Multiple with comments' => [
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[
                /*
                 * AB\Baz comment
                 */
                AB\Baz(prop: \'baz\'),
                A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\'),
                \A\B\Qux(),
                BarAlias(3)
            ]
            class X
            {
                #[ /* AB\Baz comment */AB\Baz(prop: \'baz\'), A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\'), \A\B\Qux(), BarAlias(3)]
                public function y() {}
            }
            ',
            '<?php
            namespace Test;

            use A\B\Foo;
            use A\B\Bar as BarAlias;
            use A\B as AB;

            #[
                /*
                 * AB\Baz comment
                 */
                AB\Baz(prop: \'baz\'),
                A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\'),
                \A\B\Qux(),
                BarAlias(3),
                /*
                 * Corge comment
                 */
                Corge,
                /**
                 * Foo docblock
                 */
                Foo(4, \'baz qux\'),
            ]
            class X
            {
                #[ /* AB\Baz comment */AB\Baz(prop: \'baz\'), A\B\Quux(prop1: [1, 2, 4], prop2: true, prop3: \'foo bar\'), \A\B\Qux(), BarAlias(3), /* Corge comment */Corge,/** Foo docblock */Foo(4, \'baz qux\')]
                public function y() {}
            }
            ',
            [
                'attributes' => ['A\B\Foo', 'Test\Corge'],
            ],
        ];
    }
}
