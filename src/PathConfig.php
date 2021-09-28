<?php
namespace Cubex\Sitemap;

use Cubex\Cubex;
use Packaged\Context\ContextAware;
use Packaged\Context\ContextAwareTrait;
use Packaged\Context\WithContext;
use Packaged\Context\WithContextTrait;
use Packaged\Helpers\Objects;

class PathConfig implements WithContext, ContextAware
{
  use ContextAwareTrait;
  use WithContextTrait;

  public $hostname;
  public $paths = [];

  protected function _filepath()
  {
    return Cubex::dir($this->getContext()) . 'paths.json';
  }

  public function load()
  {
    $raw = json_decode(file_get_contents($this->_filepath()));

    if($raw)
    {
      if(isset($raw->paths) && !empty($raw->paths))
      {
        $paths = [];
        foreach($raw->paths as $pathPart => $path)
        {
          $paths[$pathPart] = PathItem::fromRaw($path);
        }
        $raw->paths = $paths;
      }
      Objects::hydrate($this, $raw);
    }
  }

  public function save()
  {
    file_put_contents($this->_filepath(), json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  }
}
