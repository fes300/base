<?php

namespace App-zero\Controller;

use Symfony\Component\HttpFoundation\Response;
use App-zero\Repository\WebsiteRepository;
use App-zero\Model\WebsiteModel as Website;

class WebsiteController {
    function __construct($app) {
        $this->app = $app;
    }

    function build() {
        $app = $this->app;

        $pages = $app['controllers_factory'];

        $controller = $this;
        $pages->get('/', function ()  use ($controller) {
            return $controller->showPage(null, str_replace('www.', '', $_SERVER['HTTP_HOST']));
        });

        $pages->get('/{pageName}', function ($pageName)  use ($controller) {
            return $controller->showPage($pageName, str_replace('www.', '', $_SERVER['HTTP_HOST']));
        });

        $pages->get('/preview/{domain}', function ($domain)  use ($controller) {
            $app = $this->app;
            $websiteRepository = new websiteRepository($app['db']);
            $website = $websiteRepository->getByDomain($domain);
            if (empty($website)) {
                return new Response($app['twig']->render('/common/404.twig'), 404);
            }
            return $app->redirect('/preview/'.$domain.'/'.$website->getPageNames()[0]);
        });

        $pages->get('/preview/{domain}/{pageName}', function ($domain, $pageName)  use ($controller) {
            return $controller->showPage($pageName, $domain);
        });

        $pages->get('/preview/{domain}/{draftVersion}/{pageName}', function ($domain, $pageName, $draftVersion)  use ($controller) {
            return $controller->showPage($pageName, $domain, $draftVersion);
        });

        return $pages;
    }

    function showPage ($pageName = null, $domain, $draftVersion = null){
        $app = $this->app;
        $websiteRepository = new websiteRepository($app['db']);
        $website = $websiteRepository->getByDomain($domain);

        if (empty($website)) {
            return new Response($app['twig']->render('/common/404.twig'), 404);
        }

        // hydrate page
        $website->content->domain = $website->domain;
        $pageNames = $website->getPageNames();

        if (!empty($pageName)) {
            if ($pageName == 'robots.txt') {
                return new Response(
                    $app['twig']->render(
                        '/common/robots.txt.twig',
                        [
                            'domain' => $domain
                        ]
                    ),
                    200,
                    array('Content-Type' => 'text/plain')
                );
            }
            if ($pageName == 'sitemap.xml') {
                $data = array(
                    'host' => $website->domain,
                    'pageNames' => $pageNames,
                    'changeFreq' => "monthly",
                    'mainPriority' => "1.00",
                    'secondaryPriority' => "0.80"
                );

                return new Response(
                    $app['twig']->render(
                        '/common/sitemap.xml.twig',
                        array('data' => $data)
                    ),
                    200,
                    array('Content-Type' => 'application/xml')
                );
            }
            if (empty($website->content->pages->$pageName)) {
                return new Response($app['twig']->render('/common/404.twig'), 404);
            } else {
                $website->content->currentPage = $pageName;
            }
        } else {
            reset($website->content->pages);
            $website->content->currentPage = key($website->content->pages);
        }
        if (!empty($draftVersion)) {
            $website->content->settings = $website->content->drafts->$draftVersion;
        }
        $website->content->created = $website->created;
        $currentPageName = $website->content->currentPage;
        if (!empty($website->content->pages->$currentPageName->redirect)) {
            return $app->redirect($website->content->pages->$currentPageName->redirect);
        } elseif (!empty($website->content->pages->$currentPageName->proxy)) {
            $proxyUrl = $website->content->pages->$currentPageName->proxy;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $proxyUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
            //curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
            //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
            $output = curl_exec($ch);
            curl_close($ch);
            return $output;
        } else {
            return $app['twig']->render('layout.twig', json_decode(json_encode($website->content),1));
        }
    }
}
