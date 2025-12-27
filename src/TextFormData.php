<?php

namespace Websyspro;

class TextFormData
extends UtilFormData
{
  private string $key;
  public string $value;

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
    [ $contentDisposition, $_, $value 
    ] = $this->formData;

    [ $this->key, $this->value ] = [
      $this->getValue( 
        "#.*\bname=\"([^\"]+)\".*#", 
        $contentDisposition 
      ), $value
    ];
  }

  private function clear(
  ) : void {
    unset($this->formData);
  }
}