<?php

namespace Cubex\Sitemap;

use JsonException;
use Packaged\Context\ContextAware;
use Packaged\Context\ContextAwareTrait;

class Config implements ContextAware
{
  use ContextAwareTrait;

  protected string $_configPath = "";

  /**
   * @param string $config
   *
   * @return static
   */
  public static function i(string $config): Config
  {
    $i = new static();
    $i->_configPath = $config;

    return $i;
  }

  /**
   * @return array|mixed
   * @throws JsonException
   */
  public function getConfig()
  {
    if(!file_exists($this->_configPath))
    {
      return [];
    }
    $file = file_get_contents($this->_configPath);
    return json_decode($file, true, 512, JSON_THROW_ON_ERROR);
  }

  /**
   * @param string $url
   *
   * @return array
   */
  protected function _createConfigItem(string $url)
  {
    return [
      'loc'        => $url,
      'changefreq' => 'monthly',
      'priority'   => '1.0',
      'private'    => false,
      'lastmod'    => '',
    ];
  }

  /**
   * @param $config
   *
   * @throws JsonException
   */
  protected function _saveConfig($config)
  {
    file_put_contents($this->_configPath, json_encode($config, JSON_THROW_ON_ERROR));
  }

  /**
   * @param string $url
   *
   * @throws JsonException
   */
  public function addUrlToConfig(string $url)
  {
    $config = $this->getConfig();

    if(!is_array($config))
    {
      $config = [];
    }

    if(is_array($config) && !isset($config[$url]))
    {
      $config[$url] = $this->_createConfigItem($url);
      $this->_saveConfig($config);
    }
  }
}
