<?php

namespace pkpudev\graph;

/**
 * Get byte range for Resumable File Upload 
 * 
 * @author Zein Miftah <zmiftahdev@gmail.com>
 * @license MIT
 */
class ByteRangeCollection implements \IteratorAggregate
{
  /**
   * @var array Range data
   */
  protected $range = [];

  /**
   * Class Constructor
   * 
   * @param string $fileSize File Size
   * @param string $limit Limit for chunk
   * @return void
   */
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

  /**
   * Implementation of IteratorAggregate Interface
   * 
   * @return ArrayIterator Iterator
   */
  public function getIterator()
	{
		return new \ArrayIterator($this->range);
  }
  
  /**
   * Get array of chunk sizes
   * 
   * @return int[] Array of chunk sizes
   */
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

/**
 * Byte range class
 * 
 * @author Zein Miftah <zmiftahdev@gmail.com>
 * @license MIT
 */
class ByteRange
{
  /**
   * @var int Start byte
   */
  public $start;
  /**
   * @var int End byte
   */
  public $end;
  /**
   * @var int Limit / Length
   */
  public $limit;
  /**
   * @var int Size
   */
  public $size;

  /**
   * Class Constructor
   * 
   * @param int $start Start
   * @param int $end End
   * @param int $limit Limit
   * @param int $size Size
   * @return void
   */
  public function __construct($start, $end, $limit, $size)
  {
    $this->start = $start;
    $this->end = $end;
    $this->limit = $limit;
    $this->size = $size;
  }
}