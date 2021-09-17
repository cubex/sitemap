<?php

namespace Cubex\Sitemap;

use Cubex\Cubex;
use Cubex\Events\Handle\ResponsePreSendContentEvent;
use JsonException;
use Packaged\Context\Context;
use Packaged\Context\ContextAware;
use Packaged\Context\ContextAwareTrait;
use Packaged\Glimpse\Core\CustomHtmlTag;
use Packaged\Routing\RequestCondition;
use Packaged\SafeHtml\SafeHtml;

class SitemapGenerator implements ContextAware
{
  use ContextAwareTrait;

  protected string $_sitemapPath = "";

  public static function i(Cubex $cubex): SitemapGenerator
  {
    return new static($cubex);
  }

  public function __construct(Cubex $cubex)
  {
    if($cubex->getContext()->isEnv(Context::ENV_LOCAL))
    {
      $this->_sitemapPath = $cubex->getContext()->getProjectRoot(
        ) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sitemap.xml';
      $config = $cubex->getContext()->getProjectRoot();

      $cubex->events()->listen(new ResponsePreSendContentEvent(), function ($content) use ($cubex, $config) {
        $config = Config::i($config);

        // Check and Update Config with current route
        $config->addUrlToConfig($cubex->getContext()->meta()->get(RequestCondition::META_ROUTED_PATH, '/'));

        // Generate SitemapGenerator based off of config
        $this->_generateSitemap($config);
      });
    }
  }

  /**
   * @param Config $config
   *
   * @throws JsonException
   */
  protected function _generateSitemap(Config $config)
  {
    $items = $config->getConfig();

    $sitemapItems = [];
    foreach($items as $url => $data)
    {
      $item = SitemapItem::create($url);
      $item->map($data);

      $sitemapItems[] = $item->produceSafeHTML();
    }

    $html = new SafeHtml('<?xml version="1.0" encoding="UTF-8"?>');
    $html->append($this->_createContainer($sitemapItems));

    $content = $html->produceSafeHTML()->getContent();
    file_put_contents($this->_sitemapPath, $content);
  }

  /**
   * @param $items
   *
   * @return CustomHtmlTag
   */
  protected function _createContainer($items): CustomHtmlTag
  {
    return CustomHtmlTag::build(
      'urlset',
      [
        "xmlns" => "http://www.sitemaps.org/schemas/sitemap/0.9",
      ],
      $items
    );
  }

}
