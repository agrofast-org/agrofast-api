<?php

namespace Ilias\Choir\Utilities;

class DirectoryReader
{
  private string $rootPath;

  public function __construct(string $rootPath)
  {
    $this->rootPath = rtrim($rootPath, DIRECTORY_SEPARATOR);
    if (!is_dir($this->rootPath) || !is_readable($this->rootPath)) {
      throw new \RuntimeException("Directory not found or not readable: {$this->rootPath}");
    }
  }

  /**
   * Reads the directory structure and returns it as a tree.
   *
   * @return array
   */
  public function readDirectory(): array
  {
    return $this->readDirectoryRecursive($this->rootPath);
  }

  /**
   * Recursively reads a directory and returns its structure.
   *
   * @param string $directory
   * @return array
   */
  private function readDirectoryRecursive(string $directory): array
  {
    $result = [];
    $items = scandir($directory);

    foreach ($items as $item) {
      if ($item === '.' || $item === '..') {
        continue;
      }

      $path = $directory . DIRECTORY_SEPARATOR . $item;
      if (is_dir($path)) {
        $result[$item] = $this->readDirectoryRecursive($path);
      } else {
        $result[] = $item;
      }
    }

    return $result;
  }
}
