<?php
namespace Cubex\Sitemap;

use Packaged\Helpers\Objects;
use stdClass;

class PathHistory
{
  public $hash;
  public $hostName;

  public static function fromRaw(stdClass $raw)
  {
    return Objects::hydrate(new static(), $raw);
  }
}
