<?php
namespace Cubex\Sitemap;

use Packaged\Helpers\Objects;

class PathItem
{
  public $title;
  public $lastModified;
  public $priority;
  public $excludeFromSitemap;
  public $history = [];

  public static function fromRaw(\stdClass $raw)
  {
    if(isset($raw->history))
    {
      $history = [];
      foreach($raw->history as $time => $hist)
      {
        $history[$time] = PathHistory::fromRaw($hist);
      }
      $raw->history = $history;
    }
    return Objects::hydrate(new static(), $raw);
  }
}