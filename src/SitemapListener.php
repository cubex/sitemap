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
      if($e->getResponse()->getStatusCode() !== 200)
      {
        return;
      }

      $i = new static();
      $ctx = $context->getContext();
      $root = $ctx->getProjectRoot();
      $sitemapLocation = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sitemap.xml';

      $c = PathConfig::withContext($ctx);
      $c->load();

      if(empty($c->hostname))
      {
        $c->hostname = $cubex->getContext()->request()->getSchemeAndHttpHost();
      }

      $c = $i->_mapResponse($c, $e);

      $sitemap = Sitemap::i($c)->generateSitemap();

      file_put_contents($sitemapLocation, $sitemap);
      $c->save();
    });
  }

  protected function _mapResponse(PathConfig $config, HandleCompleteEvent $event): PathConfig
  {
    $response = $event->getResponse();
    $path = $event->getContext()->request()->path();
    $content = $this->_getContent((string)$response->getContent());
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
      $pathItem->title = $this->_getTitle($content);
      $pathItem->changeFrequency = 'monthly';
    }

    if($pathItem->lastModified > strtotime('-1 hour'))
    {
      return $config;
    }

    if(Objects::property(Arrays::value($pathItem->history, $pathItem->lastModified), 'hash') !== $hash)
    {
      $time = time();
      $h = new PathHistory();
      $h->hash = $hash;
      $h->hostName = $event->getContext()->request()->getHost();

      $pathItem->history[$time] = $h;
      $pathItem->lastModified = $time;
    }

    $config->paths[$path] = $pathItem;

    return $config;
  }

  protected function _getTitle(string $content)
  {
    $matches = [];
    preg_match("/<title>(.+)<\/title>/i", $content, $matches);

    if(count($matches) > 1)
    {
      return trim($matches[1]);
    }

    return null;
  }

  protected function _getContent(string $content)
  {
    // Remove the head, we really only care about content
    $content = preg_replace('/<head>(.*?)<\/head>/s', '', $content);
    $content = preg_replace('/<script(.*?)<\/script>/s', '', $content);
    return preg_replace('/<link(.*?)<\/link>/s', '', $content);
  }
}
