<?php

namespace Websyspro;

use Websyspro\Commons\Collection;
use Websyspro\Commons\Utils;

class Request
{
  public array|object|null $body = null;
  public array|object|null $files = null;
  public array|object|null $query = null;
  public array|object|null $params = null;

  public function __construct(
    private AcceptHeader $acceptHeader   
  ){
    $this->ready();
  }

  private function isApplicationJson(
  ): bool {
    if($this->acceptHeader->getContentType() === null){
      return false;
    }

    return preg_match(
      "#^application/json#", 
      $this->acceptHeader->getContentType()
    ) === 1;
  }

  private function isMultipartFormData(
  ): bool {
    if($this->acceptHeader->getContentType() === null){
      return false;
    }

    return preg_match(
      "#^multipart/form-data#",
      $this->acceptHeader->getContentType()
    ) === 1;
  }

  private function isFormUrlEncoded(
  ): bool {
    if($this->acceptHeader->getContentType() === null){
      return false;
    }

    return preg_match(
      "#^application/x-www-form-urlencoded#",
      $this->acceptHeader->getContentType()
    ) === 1;
  }

  private function queryDecode(
  ): void {
    $hasQuery = Utils::isNotNull(
      $this->acceptHeader->getRequestQuery()
    );

    if( $hasQuery ){
      parse_str(
        $this->acceptHeader->getRequestQuery(), 
        $this->query
      );
    }
  }  
  
  private function setBodyFromJsonDecode(
  ): void {
    $this->body = json_decode(
      $this->acceptHeader->getContentBody()
    );
  }

  private function isFileContentDisposition(
    string $contentDisposition,
  ): bool {
    return preg_match(
      "#filename#",
      $contentDisposition
    ) === 1;
  }
  
  private function formdDataDecode(
    string $formData
  ): FileFormData|TextFormData {
    $formData = preg_split(
      "#\r\n#", 
      preg_replace(
        "#(^\r\n)|(\r\n$)#", 
        "", 
        $formData
      )
    );

    [ $contentDisposition ] = $formData;
    return $this->isFileContentDisposition(
      $contentDisposition
    ) ? new FileFormData( $formData ) 
      : new TextFormData( $formData );
  }

  private function formDataToList(
  ): array {
    return preg_split(
      "#\-{28}\d{24}#",
      preg_replace(
        "#--\r\n$#",
        "",
        $this->acceptHeader->getContentBody()
      ),
      -1, 
      PREG_SPLIT_NO_EMPTY
    );
  }

  private function setBodyFromFormData(
  ): void {
    $formDataItems = new Collection(
      $this->formDataToList()
    );

    $formDataItems
      ->mapper(fn(string $formData): FileFormData|TextFormData => (
        $this->formdDataDecode( $formData)
      ))
      ->mapper(function(FileFormData|TextFormData $formData) {
        if( $formData instanceof FileFormData ){
          $this->files[$formData->getKey()] = $formData;
        } else 
        if( $formData instanceof TextFormData ) {
          $this->body[$formData->getKey()] = $formData->value;
        }
      });
  }

  private function setBodyFromUrlEncode(
  ): void {
    parse_str( 
      $this->acceptHeader->getContentBody(), 
      $this->body
    );
  }  

  private function ready(
  ): void {
    $this->queryDecode();

    if($this->isApplicationJson()){
      $this->setBodyFromJsonDecode(); 
    } else
    if($this->isMultipartFormData()){
      $this->setBodyFromFormData();
    } else
    if($this->isFormUrlEncoded()){
      $this->setBodyFromUrlEncode();
    }
  }
}