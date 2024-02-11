<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use Thedustin\PhpTxtar\Archive;
use PHPUnit\Framework\TestCase;
use Thedustin\PhpTxtar\File;

class ArchiveTest extends TestCase
{
    public static function dataToString(): iterable
    {
        yield 'empty' => [
            new Archive('', []),
            '',
        ];

        yield 'comment' => [
            new Archive('comment', []),
            "comment\n",
        ];

        yield 'file' => [
            new Archive('', [new File('name', 'data')]),
            <<<TXT
            -- name --
            data

            TXT,
        ];

        yield 'comment and file' => [
            new Archive('comment', [new File('name', 'data')]),
            <<<TXT
            comment
            -- name --
            data

            TXT,
        ];

        yield 'basic (taken from go package)' => [
            new Archive("comment1\ncomment2", [
                new File('file1', "File 1 text.\n-- foo ---\nMore file 1 text.\n"),
                new File('file 2', "File 2 text.\n"),
                new File('empty', ''),
                new File('noNL', 'hello world'),
            ]),
            <<<TXT
            comment1
            comment2
            -- file1 --
            File 1 text.
            -- foo ---
            More file 1 text.
            -- file 2 --
            File 2 text.
            -- empty --
            -- noNL --
            hello world

            TXT,
        ];
    }

    #[DataProvider('dataToString')]
    public function testToString(Archive $archive, string $expected): void
    {
        self::assertSame($expected, (string) $archive);
    }

    public static function dataFromString(): iterable
    {
        yield 'empty' => [
            '',
            new Archive('', []),
        ];

        yield 'comment' => [
            "comment\n",
            new Archive("comment\n", []),
        ];

        yield 'file' => [
            <<<TXT
            -- name --
            data

            TXT,
            new Archive('', [new File('name', "data\n")]),
        ];

        yield 'comment and file' => [
            <<<TXT
            comment
            -- name --
            data

            TXT,
            new Archive("comment\n", [new File('name', "data\n")]),
        ];

        yield 'basic (taken from go package)' => [
            <<<TXT
            comment1
            comment2
            -- file1 --
            File 1 text.
            -- foo ---
            More file 1 text.
            -- file 2 --
            File 2 text.
            -- empty --
            -- noNL --
            hello world
            -- empty filename line --
            some content
            -- --

            TXT,
            new Archive("comment1\ncomment2\n", [
                new File('file1', "File 1 text.\n-- foo ---\nMore file 1 text.\n"),
                new File('file 2', "File 2 text.\n"),
                new File('empty', ''),
                new File('noNL', "hello world\n"),
                new File('empty filename line', "some content\n-- --\n"),
            ]),
        ];
    }

    #[DataProvider('dataFromString')]
    public function testFromString(string $str, Archive $expected): void
    {
        self::assertEquals($expected, Archive::createFromString($str));
    }
}
