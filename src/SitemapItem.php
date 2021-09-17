<?php

namespace Cubex\Sitemap;

use Exception;
use Packaged\Glimpse\Core\CustomHtmlTag;
use Packaged\SafeHtml\SafeHtml;

class SitemapItem
{
  protected $_location;
  protected $_changeFreq;
  protected $_lastModified;
  protected $_priority;

  /**
   * @param string $loc
   *
   * @return static
   */
  public static function create(string $loc): SitemapItem
  {
    $instance = new static();
    $instance->_location = $loc;
    return $instance;
  }

  public function map($data)
  {
    if(isset($data['changefreq']))
    {
      $this->_changeFreq = $data['changefreq'];
    }

    if(isset($data['priority']))
    {
      $this->_priority = $data['priority'];
    }

    if(isset($data['lastmod']))
    {
      $this->_lastModified = $data['lastmod'];
    }
  }

  /**
   * @return CustomHtmlTag|null
   */
  protected function _getLocation(): ?CustomHtmlTag
  {
    return $this->_location ? CustomHtmlTag::build('loc', [], $this->_location) : null;
  }

  /**
   * @return CustomHtmlTag|null
   */
  protected function _getLastModified(): ?CustomHtmlTag
  {
    $lastModified = $this->_lastModified;
    if(!$lastModified)
    {
      return null;
    }

    if(ctype_digit($lastModified))
    {
      $lastModified = date('c', $lastModified);
    }
    else
    {
      $lastModified = date('c', strtotime($lastModified));
    }
    return CustomHtmlTag::build('lastmod', [], $lastModified);
  }

  /**
   * @return CustomHtmlTag|null
   */
  protected function _getPriority(): ?CustomHtmlTag
  {
    return $this->_priority ? CustomHtmlTag::build('priority', [], $this->_priority) : null;
  }

  /**
   * @return CustomHtmlTag|null
   */
  protected function _getChangeFreq(): ?CustomHtmlTag
  {
    return $this->_changeFreq ? CustomHtmlTag::build('changefreq', [], $this->_changeFreq) : null;
  }

  /**
   * @return SafeHtml
   * @throws Exception
   */
  public function produceSafeHTML(): SafeHtml
  {
    $container = CustomHtmlTag::build('url');

    $container->setContent(
      [
        $this->_getLocation(),
        $this->_getLastModified(),
        $this->_getPriority(),
        $this->_getChangeFreq(),
      ]
    );

    return $container->produceSafeHTML();
  }
}
