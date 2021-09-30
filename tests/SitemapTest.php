<?php

namespace Cubex\Sitemap\Tests;

use Cubex\Sitemap\PathConfig;
use Cubex\Sitemap\PathItem;
use Cubex\Sitemap\Sitemap;
use Packaged\Context\Context;
use PHPUnit\Framework\TestCase;

class SitemapTest extends TestCase
{
  protected function _getConfig(): PathConfig
  {
    $context = Context::create(__DIR__);
    $config = PathConfig::withContext($context);
    $config->load();

    return $config;
  }

  public function testEmptySitemap()
  {
    $config = $this->_getConfig();
    $sitemap = Sitemap::i($config)->generateSitemap();

    self::assertStringContainsString(
      '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>',
      $sitemap
    );
  }

  public function testConfig()
  {
    $config = $this->_getConfig();
    self::assertEmpty($config->paths);

    $pathItem = new PathItem();
    $pathItem->title = 'Home';
    $pathItem->changeFrequency = 'weekly';

    $config->paths['/'] = $pathItem;

    self::assertNotEmpty($config->paths);
    self::assertStringContainsString($config->paths['/']->title, 'Home');
    self::assertStringContainsString($config->paths['/']->changeFrequency, 'weekly');

    $sitemap = Sitemap::i($config)->generateSitemap();

    self::assertStringContainsString('<loc>/</loc>', $sitemap);
    self::assertStringContainsString('<changefreq>weekly</changefreq>', $sitemap);
    self::assertStringNotContainsString('<priority></priority>', $sitemap);

    $config->hostname = 'www.something.com';
    $pathItem->lastModified = strtotime('today');

    $newSitemap = Sitemap::i($config)->generateSitemap();

    self::assertStringContainsString('<loc>www.something.com/</loc>', $newSitemap);
    self::assertStringContainsString('<changefreq>weekly</changefreq>', $newSitemap);
    self::assertStringContainsString('<lastmod>' . date('c', strtotime('today')) . '</lastmod>', $newSitemap);
  }
}
