<?php

namespace Ilias\Choir\Utilities;

class FileReader
{
  private string $filePath;

  public function __construct(string $filePath)
  {
    $this->filePath = $filePath;

    if (!file_exists($this->filePath)) {
      throw new \RuntimeException("File {$this->filePath} as not found");
    }
    if (!is_readable($this->filePath)) {
      throw new \RuntimeException("File {$this->filePath} could not be read");
    }
  }

  /**
   * Read the entire file and return its contents as a string.
   *
   * @return string
   * @throws \RuntimeException
   */
  public function readFile(): string
  {
    $content = file_get_contents($this->filePath);

    if ($content === false) {
      throw new \RuntimeException("Failed to read the file: {$this->filePath}");
    }

    return $content;
  }

  /**
   * Read the file line by line and return an array of lines.
   *
   * @return array
   * @throws \RuntimeException
   */
  public function readFileByLines(): array
  {
    $lines = file($this->filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
      throw new \RuntimeException("Failed to read the file: {$this->filePath}");
    }

    return $lines;
  }

  /**
   * Check if the file exists and is readable.
   *
   * @return bool
   */
  public function isFileReadable(): bool
  {
    return file_exists($this->filePath) && is_readable($this->filePath);
  }
}
