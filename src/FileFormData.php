<?php

namespace Websyspro;

class FileFormData
extends UtilFormData
{
  private string $key;
  public string $file;
  public string $type;
  public float $size;
  public string $body;

  public function __construct(
    private array $formData
  ){
    $this->ready();
    $this->clear();
  }

  public function getKey(
  ): string {
    return $this->key;
  }

  private function ready(
  ): void {
    [ $contentDisposition, 
      $contentType
    ] = $this->formData;

    [ $this->body ] = [
      implode( 
        "\r\n", 
        array_slice(
          $this->formData,
          3
        )
      )
    ];

    [ $this->size ] = [
      (float)number_format(
        strlen(
          $this->body
        ) / 1024,
        6,
        ".", 
        ""
      )
    ];

    [ $this->key, $this->file, $this->type ] = [
      $this->getValue( "#.*\bname=\"([^\"]+)\".*#", $contentDisposition ),
      $this->getValue( "#.*filename=\"([^\"]+)\".*#", $contentDisposition ),
      $this->getValue( "#^Content-Type:\s#", $contentType )
    ];
  }

  private function clear(
  ): void {
    unset($this->formData);
  }  
}