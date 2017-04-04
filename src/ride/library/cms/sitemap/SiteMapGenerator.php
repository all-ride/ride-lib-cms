<?php

namespace ride\library\cms\sitemap;

use ride\library\cms\exception\CmsException;
use ride\library\cms\node\Node;
use ride\library\cms\node\SiteNode;
use ride\library\cms\widget\Widget;
use ride\library\cms\Cms;
use ride\library\reflection\Boolean;
use ride\library\system\file\File;
use ride\library\StringHelper;

/**
 * Generator of the site map files
 */
class SiteMapGenerator {

    /**
     * Flag in the node properties to tell if the node should be taken into the
     * sitemap
     * @var string
     */
    const PROPERTY_SITEMAP = 'sitemap';

    /**
     * Value in the node properties for the priority of the URL
     * @var string
     */
    const PROPERTY_SITEMAP_PRIORITY = 'sitemap.priority';

    /**
     * Value in the node properties for the change frequency of an URL
     * @var string
     */
    const PROPERTY_SITEMAP_FREQUENCY = 'sitemap.frequency';

    /**
     * Constructs a new site map generator
     * @param \ride\library\cms\Cms $cms
     * @return null
     */
    public function __construct(Cms $cms) {
        $this->cms = $cms;
    }

    /**
     * Generates the site map files for the provided site
     * @param \ride\library\system\file\File $directory Parent directory for the
     * generated files
     * @param \ride\library\cms\node\SiteNode $site Instance of the site
     * @param string $baseUrl Default base URL
     * @return array Array with the written files
     */
    public function generateSiteMaps(File $directory, SiteNode $site, $baseUrl) {
        $hosts = $this->getUrls($site, $baseUrl);

        foreach ($hosts as $host => $siteMapUrls) {
            $xml = $this->getXml($siteMapUrls);

            $file = $directory->getChild('sitemap-' . StringHelper::safeString($host) . '.xml');
            $file->write($xml);

            $files[$file->getAbsolutePath()] = $file;
        }

        return $files;
    }

    /**
     * Gets the XML for the provided site map URL's
     * @param array $siteMapUrls Array with SiteMapUrl instances
     * @return string XML of the site map
     */
    public function getXml(array $siteMapUrls) {
        $xml =
'<?xml version="1.0" encoding="utf-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

        foreach ($siteMapUrls as $siteMapUrl) {
            if (!$siteMapUrl instanceof SiteMapUrl) {
                throw new CmsException('Could not generate site map XML: value for ' . $url . ' is not a ride\\library\\cms\\sitemap\\SiteMapUrl instance');
            }

            $xml .= "\n    " . $siteMapUrl;
        }

        $xml .= "\n</urlset>\n";

        return $xml;
    }

    /**
     * Gets the site map URL's
     * @param \ride\library\cms\node\SiteNode $site Instance of the site
     * @param string $baseUrl Default base URL
     * @return array Array with the host as key and as value an array with the
     * URL as key and a SiteMapUrl instance as value
     */
    public function getUrls(SiteNode $site, $baseUrl) {
        $urls = array();

        $site = $this->cms->getNode($site->getId(), $site->getRevision(), $site->getId(), null, true);

        $locales = $this->cms->getLocales();
        foreach ($locales as $locale) {
            $siteBaseUrl = $site->getBaseUrl($locale);
            if (!$siteBaseUrl) {
                $siteBaseUrl = $baseUrl;
            }

            $urls += $this->getUrlsForNode($site, $locale, $siteBaseUrl);
        }

        return $this->processUrls($urls);
    }

    /**
     * Gets the URL's for the provided node by looping all widget instances
     * @param \ride\library\cms\node\Node $node Instance of the node
     * @param string $locale Code of the locale
     * @param string $baseUrl Default base URL
     * @return array
     */
    private function getUrlsForNode(Node $node, $locale, $baseUrl) {
        $urls = array();

        $skip = !Boolean::getBoolean($node->getLocalized($locale, self::PROPERTY_SITEMAP, true));

        $nodeType = $this->cms->getNodeType($node);
        if ($nodeType->getFrontendCallback() === null
            || !$node->isPublished()
            || !$node->isAvailableInLocale($locale)
            || !$node->isAllowed($this->cms->getSecurityManager())
        ) {
            $skip = true;
        }

        if (!$skip) {
            $url = $node->getUrl($locale, $baseUrl);

            $urls[$url] = new SiteMapUrl($url, $node->getDateModified());

            $theme = $node->getTheme();
            $theme = $this->cms->getTheme($theme);

            $regions = $theme->getRegions();
            foreach ($regions as $region) {
                $sections = $node->getSections($region);
                foreach ($sections as $section => $layout) {
                    $widgets = $node->getWidgets($region, $section);
                    foreach ($widgets as $blockId => $blockWidgets) {
                        foreach ($blockWidgets as $widgetInstanceId => $widgetId) {
                            $widgetProperties = $node->getWidgetProperties($widgetInstanceId);

                            $widget = $this->cms->getWidget($widgetId);
                            if ($widget === null) {
                                continue;
                            }

                            $widget = clone $widget;
                            $widget->setIdentifier($widgetInstanceId);
                            $widget->setLocale($locale);
                            $widget->setRegion($region);
                            $widget->setSection($section);
                            $widget->setBlock($blockId);
                            $widget->setProperties($widgetProperties);

                            $this->prepareWidget($widget);

                            if (!$widgetProperties->isPublished() || !$widgetProperties->isAvailableInLocale($locale) || !$widgetProperties->isAllowed($this->cms->getSecurityManager())) {
                                continue;
                            }

                            $urls += $widget->getSiteMapUrls($locale, $baseUrl);
                        }
                    }
                }
            }
        }

        $children = $node->getChildren();
        if ($children) {
            foreach ($children as $child) {
                $urls += $this->getUrlsForNode($child, $locale, $baseUrl);
            }
        }

        return $urls;
    }

    /**
     * Hook to perform extra processing on a widget
     * @param \ride\library\cms\widget\Widget $widget
     * @return null
     */
    protected function prepareWidget(Widget $widget) {

    }

    /**
     * Processes the gathered URL's by ordering on host and filtering out the
     * not (!) URL's
     * @param array $urls Array with the URL as key and a SiteMapUrl instance as
     * value
     * @return array Array with the host as key and as value an array with the
     * URL as key and a SiteMapUrl instance as value
     */
    private function processUrls(array $urls) {
        $hosts = array();
        $ignore = array();

        foreach ($urls as $url => $siteMapUrl) {
            if (substr($url, 0, 1) !== '!') {
                continue;
            }

            $ignore[substr($url, 1)] = true;

            unset($urls[$url]);
        }

        foreach ($urls as $url => $siteMapUrl) {
            if (isset($ignore[$url])) {
                continue;
            } elseif (!$siteMapUrl instanceof SiteMapUrl) {
                throw new CmsException('Could not process site map URL: value for ' . $url . ' is not a ride\\library\\cms\\sitemap\\SiteMapUrl instance');
            }

            $parts = parse_url($url);

            $hosts[$parts['host']][$url] = $siteMapUrl;
        }

        return $hosts;
    }

}
