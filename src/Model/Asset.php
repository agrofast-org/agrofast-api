<?php

namespace Ilias\Choir\Model;

use Ilias\Opherator\Request\Response;
use Ilias\Choir\Utilities\FileReader;
use RuntimeException;

class Asset
{
  private const ASSET_TRANSLATE = [
    "" => "/",
    "image" => "/img"
  ];

  private string $assetFolderPath;

  public function __construct()
  {
    $this->assetFolderPath = rtrim($_SERVER["DOCUMENT_ROOT"], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "public";
  }

  public function loadAsset(string $assetType, string|int $assetIdentifier)
  {
    $assetType = self::ASSET_TRANSLATE[$assetType];
    $assetPath = $this->assetFolderPath . DIRECTORY_SEPARATOR . $assetType . DIRECTORY_SEPARATOR . $assetIdentifier;

    try {
      $fileReader = new FileReader($assetPath);
      header('Content-Type: ' . $this->getMimeType($assetPath));
      echo $fileReader->readFile();
    } catch (RuntimeException $e) {
      http_response_code(404);
      Response::appendResponse("message", "Asset {$assetIdentifier} type {$assetType} was not found: " . $e->getMessage());
    }
  }

  private function getMimeType(string $filePath): string
  {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    return $this->mimeTypes()[$extension] ?? 'application/octet-stream';
  }

  private function mimeTypes(): array
  {
    return [
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'png' => 'image/png',
      'gif' => 'image/gif',
      'ico' => 'image/x-icon',
      'svg' => 'image/svg+xml',
      'pdf' => 'application/pdf',
      'txt' => 'text/plain',
      'html' => 'text/html',
      'css' => 'text/css',
      'js' => 'application/javascript',
    ];
  }
}
