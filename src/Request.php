<?php

namespace Websyspro;

use Websyspro\Commons\Collection;

class Request
{
  public mixed $body = [];
  public mixed $files = [];
  public mixed $query = [];

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
    if($this->acceptHeader->getRequestUrlQuery() !== null){
      parse_str(
        $this->acceptHeader->getRequestUrlQuery(), 
        $this->query
      );      
    }
  }  
  
  private function bodyDecode(
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

  private function formData(
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

  private function urlEncode(
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
      $this->bodyDecode(); 
    } else
    if($this->isMultipartFormData()){
      $this->formData();
    } else
    if($this->isFormUrlEncoded()){
      $this->urlEncode();
    }
  }
}