<?php

namespace pkpudev\graph;

class ByteRangeCollection implements \IteratorAggregate
{
  protected $range = [];

	public function __construct($fileSize, $limit)
	{ 
    $chunks = $this->getChunkSizes($fileSize, $limit);
    $chunkLimit = count($chunks) - 1;

    for ($i=0; $i < $chunkLimit; $i++) {
      $start = $chunks[$i]+1;
      $end = $chunks[$i+1];
      $sum = $end - $start + 1;
      $this->range[] = new ByteRange($start, $end, $sum, $fileSize);
    }
  }

  public function getIterator()
	{
		return new \ArrayIterator($this->range);
  }
  
  protected function getChunkSizes($size, $limit)
  {
    $maxIteration = ceil($size / $limit);
    return array_map(function($pointer) use ($size, $limit) {
      $chunk = ($pointer * $limit) - 1;
      $chunk = $chunk <= -1 ? -1 : $chunk;
      $chunk = $chunk >= $size ? ($size-1) : $chunk;
      return $chunk;
    }, range(0, $maxIteration));
  }
}

class ByteRange
{
  public $start;
  public $end;
  public $limit;
  public $size;

  public function __construct($start, $end, $limit, $size)
  {
    $this->start = $start;
    $this->end = $end;
    $this->limit = $limit;
    $this->size = $size;
  }
}