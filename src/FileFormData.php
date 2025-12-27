<?php

namespace Websyspro;

class FileFormData
{
  public string $fieldName;
  public string $fileName;
  public string $contentType;
  public float $fileSize;
  public string $content;

  public function __construct(
    private string $contentDisposition,
    private array $formData
  ){
    $this->ready();
  }

  private function readyFieldName(
  ): void {
    $this->fieldName = preg_replace(
      "#.*\bname=\"([^\"]+)\".*#", 
      "$1",
      $this->contentDisposition
    );
  }  

  private function readyFileName(
  ): void {
    $this->fileName = preg_replace(
      "#.*filename=\"([^\"]+)\".*#", 
      "$1",
      $this->contentDisposition
    );
  }

  private function readyContentType(
  ): void {
    [ $_, $this->contentType ] = $this->formData;
    $this->contentType = preg_replace(
      "#^Content-Type:\s#",
      "",
      $this->contentType
    );
  }

  private function readyContent(
  ): void {
    $this->content = implode( 
        "\r\n", 
        array_slice(
          $this->formData, 3
      )
    );

    file_put_contents("D:/Uploads/{$this->fileName}", $this->content);
  }

  private function readyFileSize(
  ): void {
    $this->fileSize = (
      (float)number_format(
        strlen(
          $this->content
        ) / 1024,
        6,
        ".", 
        ""
      )
    );
  }

  private function readyClear(
  ): void {
    unset($this->contentDisposition);
    unset($this->formData);
  }

  private function ready(
  ): void {
    $this->readyFieldName();
    $this->readyFileName();
    $this->readyContentType();
    $this->readyContent();
    $this->readyFileSize();
    $this->readyClear();
  }
}