<?php

namespace Websyspro;

use Websyspro\Commons\DataList;

class Request
{
  public mixed $body = null;
  public mixed $files = null;

  public function __construct(
    private AcceptHeader $acceptHeader   
  ){
    $this->ready();
  }

  private function isApplicationJson(
  ): bool {
    return (bool)preg_match(
      "#^application/json#", 
      $this->acceptHeader->contentType()
    ) === true;
  }

  private function isMultipartFormData(
  ): bool {
    return (bool)preg_match(
      "#^multipart/form-data#",
      $this->acceptHeader->contentType()
    ) === true;
  }

  private function isFormUrlEncoded(
  ): bool {
    return (bool)preg_match(
      "#^application/x-www-form-urlencoded#",
      $this->acceptHeader->contentType()
    ) === true;
  }
  
  private function bodyDecode(
  ): void {
    $this->body = json_decode(
      $this->acceptHeader->contentBody()
    );
  }

  private function isFileContentDisposition(
    string $contentDisposition,
  ): bool {
    return (bool)preg_match("#filename#", $contentDisposition) === true;
  }
  
  private function formdDataDecode(
    string $formData
  ): FileFormData|array {
    $formData = preg_split(
      "#\r\n#", 
      preg_replace(
        "#(^\r\n)|(\r\n$)#", 
        "", 
        $formData
      )
    );

    [ $contentDisposition ] = $formData;

    if( $this->isFileContentDisposition( $contentDisposition )){
      return new FileFormData( 
        $contentDisposition,
        $formData
      );
    }

    return [ $contentDisposition ];
  }

  private function formDataToList(
  ): array {
    return preg_split(
      "#\-{28}\d{24}#",
      preg_replace(
        "#--\r\n$#",
        "",
        $this->acceptHeader->contentBody()
      ),
      -1, 
      PREG_SPLIT_NO_EMPTY
    );
  }

  private function formData(
  ): void {
    $formDataList = array_map(
      fn(string $formData) => (
        $this->formdDataDecode(
          $formData
        )
      ),
      $this->formDataToList()
    );

    // ----------------------------912907606829627820448582

    // print_r($formDataList);
    //var_dump($this->acceptHeader->contentType());
    //var_dump($this->acceptHeader->contentBody());

  }

  private function urlEncode(
  ): void {
    parse_str( 
      $this->acceptHeader->contentBody(), 
      $this->body
    );
  }  

  private function ready(
  ): void {
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