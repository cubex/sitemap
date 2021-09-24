<?php

namespace Cubex\Sitemap;

use Cubex\Cubex;
use Cubex\Events\Handle\HandleCompleteEvent;
use Packaged\Context\ContextAware;
use Packaged\Helpers\Arrays;
use Packaged\Helpers\Objects;

class SitemapListener
{
  protected function __construct() { }

  public static function with(Cubex $cubex, ContextAware $context)
  {
    $cubex->listen(HandleCompleteEvent::class, static function (HandleCompleteEvent $e) use ($cubex, $context) {
      $ctx = $context->getContext();
      $root = $ctx->getProjectRoot();
      $cubex->getLogger()->alert('Sitemap Gen Stuff');
      $i = new static();

      $sitemapLocation = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sitemap.xml';

      $c = PathConfig::withContext($ctx);
      $c->load();

      if(empty($c->hostname))
      {
        $c->hostname = $cubex->getContext()->request()->getSchemeAndHttpHost();
      }

      $c = $i->_mapResponse($c, $e);

      Sitemap::i($sitemapLocation, $c)->generateSitemap();

      $c->save();
    });
  }

  protected function _mapResponse(PathConfig $config, HandleCompleteEvent $event): PathConfig
  {
    $response = $event->getResponse();
    $path = $event->getContext()->request()->path() . 'test';
    $content = $this->_GetContent((string)$response->getContent());
    $hash = md5($content);

    if(isset($config->paths[$path]))
    {
      $pathItem = $config->paths[$path];
    }
    else
    {
      $pathItem = new PathItem();
      $pathItem->priority = 1.0;
      $pathItem->excludeFromSitemap = false;
      $title = ucfirst(ltrim($path, '/')) . ' Page';
      $pathItem->title = $title ?? 'Homepage';
    }

    if(Objects::property(Arrays::value($pathItem->history, $pathItem->lastModified), 'hash') !== $hash)
    {
      $time = time();
      $h = new PathHistory();
      $h->hash = $hash;
      $h->hostName = $config->hostname;

      $pathItem->history[$time] = $h;
      $pathItem->lastModified = $time;
    }

    $config->paths[$path] = $pathItem;

    return $config;
  }

  protected function _GetContent(string $content)
  {
    // Remove the head, we really only care about content
    $content = preg_replace('/<head>(.*?)<\/head>/s', '', $content);
    $content = preg_replace('/<script(.*?)<\/script>/s', '', $content);
    $content = preg_replace('/<link(.*?)<\/link>/s', '', $content);
    return $content;
  }

}
