<?php

declare(strict_types=1);

namespace Thedustin\PhpTxtar;

class Archive implements \Stringable
{
    private const MARKER_START = '-- ';
    private const MARKER_END = " --";

    private string $comment;
    /** @var File[] */
    private array $files;

    public function __construct(string $comment, array $files)
    {
        $this->comment = $comment;
        $this->files = $files;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param File[] $files
     */
    public function setFiles(array $files): self
    {
        $this->files = [];
        $this->addFiles(...$files);

        return $this;
    }

    public function addFile(File $file): self
    {
        $this->files[] = $file;

        return $this;
    }

    public function addFiles(File ...$files): self
    {
        $this->files = array_merge($this->files, $files);

        return $this;
    }

    public function toString(): string
    {
        $str = self::fixNewLine($this->comment);
        foreach ($this->files as $file) {
            $str .= self::MARKER_START . $file->name . self::MARKER_END . "\n";
            $str .= self::fixNewLine($file->data);
        }

        return $str;
    }


    public static function createFromString(string $str): self
    {
        $fh = fopen('php://memory', 'r+');
        fwrite($fh, $str);
        rewind($fh);

        return self::createFromFile($fh);
    }

    /**
     * @param resource $fh
     */
    public static function createFromFile(mixed $fh): self
    {
        $comment = '';
        $files = [];

        // Read all lines as comment until we find the first marker
        while (false !== $line = fgets($fh)) {
            $marker = self::parseMarker($line);

            if ($marker !== null) {
                break;
            }

            $comment .= $line;
        }

        if (feof($fh)) {
            return new self($comment, $files);
        }

        do {
            $marker = self::parseMarker($line);
            $data = '';

            // Read all lines as data until we find the next marker
            while (false !== $line = fgets($fh)) {
                $nextMarker = self::parseMarker($line);

                if ($nextMarker !== null) {
                    break;
                }

                $data .= $line;
            }

            $files[] = new File($marker, $data);
        } while (!feof($fh));

        return new self($comment, $files);
    }


    private static function parseMarker(string $line): ?string
    {
        if (\strlen($line) < (\strlen(self::MARKER_START) + \strlen(self::MARKER_END . "\n"))
            || !str_starts_with($line, self::MARKER_START)
            || !str_ends_with($line, self::MARKER_END . "\n")
        ) {
            return null;
        }

        $marker = substr($line, \strlen(self::MARKER_START), -\strlen(self::MARKER_END . "\n"));

        return trim($marker);
    }

    /**
     * Ensures that the given string ends with a newline character.
     */
    private static function fixNewLine(string $str): string
    {
        if ($str === '' || str_ends_with($str, "\n")) {
            return $str;
        }

        return $str . "\n";
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
