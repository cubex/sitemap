<?php
namespace Cubex\Sitemap;

use Packaged\Glimpse\Core\CustomHtmlTag;
use Packaged\SafeHtml\SafeHtml;

class Sitemap
{
  protected PathConfig $_config;

  protected function __construct(PathConfig $c)
  {
    $this->_config = $c;
  }

  public static function i(PathConfig $c)
  {
    return new static($c);
  }

  public function generateSitemap()
  {
    $config = $this->_config;

    $sitemapItems = [];
    /**
     * @var string   $url
     * @var PathItem $path
     */
    foreach($config->paths as $url => $path)
    {
      if(!$path->excludeFromSitemap)
      {
        $sitemapItems[] = $this->_tag('url', [
          $this->_tag('loc', $this->_config->hostname . '/' . ltrim($url, '/')),
          $this->_tag('priority', $path->priority),
          $this->_getLastModified($path->lastModified),
          $this->_tag('changefreq', $path->changeFrequency),
        ]);
      }
    }

    $xml = new SafeHtml('<?xml version="1.0" encoding="UTF-8"?>');
    $xml->append($this->_createContainer($sitemapItems));

    return $xml->produceSafeHTML()->getContent();
  }

  protected function _createContainer($sitemapItems): CustomHtmlTag
  {
    return CustomHtmlTag::build(
      'urlset',
      [
        "xmlns" => "http://www.sitemaps.org/schemas/sitemap/0.9",
      ],
      $sitemapItems
    );
  }

  protected function _tag(string $tagName, $content)
  {
    return isset($content) ? CustomHtmlTag::build($tagName, [], $content) : null;
  }

  protected function _getLastModified($lastModified)
  {
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

    return $this->_tag('lastmod', $lastModified);
  }
}
