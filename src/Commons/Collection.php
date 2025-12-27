<?php

namespace Websyspro\Commons;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Collection
{
  public function __construct(
    public array $items = []
  ){}

  public function add(
    mixed $item
  ): Collection {
    $this->items[] = $item;
    return $this;
  }

  public function merge(
    Collection|array $collectionOrArray 
  ): Collection {
    if($collectionOrArray instanceof Collection){
      $this->items = array_merge(
        $this->items, $collectionOrArray->all()
      );
    } else {
      $this->items = array_merge(
        $this->items, $collectionOrArray
      );
    }

    return $this;
  }

  public function mapper(
    callable|object $callback
  ): Collection {
    if(is_callable( $callback ) === false){
      // TODO:: mapper
      return new Collection();
    }

    return new Collection(
      Utils::mapper(
        $this->items, $callback
      )
    );
  }

  public function where(
    callable $callable
  ): Collection {
    return new Collection(Utils::where($this->items, $callable));
  }

  public function find(
    callable $callable
  ): mixed {
    return Utils::find($this->items, $callable);
  }

  public function reduce(
    mixed $curremt,
    callable $callable
  ): mixed {
    return Utils::reduce($curremt, $this->items, $callable);
  }

  public function slice(
    int $start,
    int|null $lenght = null
  ): Collection {;
    return new Collection(array_slice($this->items, $start, $lenght));
  }
  
  public function chunk(
    int $length
  ): Collection {
    $this->items = Utils::chunk($this->items, $length);
    return $this;
  }  

  public function join(
    string $join = ""
  ): string {
    return implode($join, $this->items);
  }

  public function joinWithComma(
  ): string {
    return $this->Join(", ");
  }

  public function joinWithSpace(
  ): string {
    return $this->Join(" ");
  }

  public function joinNotSpace(
  ): string {
    return $this->Join("");
  }

  public function joinWithBreak(
  ): string {
    return $this->Join( "\r\n" );
  }  

  public function count(
  ): int {
    return sizeof($this->items);
  }

  public function exist(
  ): bool {
    return sizeof($this->items) !== 0;
  }
  
  public function sum(
    callable $callable
  ): float {
    return array_sum(Utils::mapper( $this->items, $callable ));
  }

  public function eq(
    int $eq
  ): Collection {
    return new Collection(
      array_slice($this->items, $eq, 1)
    );
  }

  public function first(    
  ): mixed {
    return reset($this->items);
  }

  public function last(    
  ): mixed {
    return end($this->items);
  } 
  
  public function orderByAsc(
  ): Collection {
    ksort($this->items);
    return new Collection($this->items);
  }

  public function all(
  ): array {
    return $this->items;
  }
}