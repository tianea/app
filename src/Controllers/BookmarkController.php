<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 23.05.18
 * Time: 16:09
 */

namespace Controllers;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Model\BookmarkModel;

class BookmarkController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])->bind('bookmark_index');
        $controller->get('/{bookmarkId}', [$this, 'viewAction'])->bind('bookmark_view');

        return $controller;
    }

    public function indexAction(Application $app)
    {
        $bookmarkModel = new BookmarkModel();
        $bookmarks = $bookmarkModel->findAll();
        if (!isset($bookmarks) || !count($bookmarks)) {
            $app->abort('404', 'Invalid entry');
        }
        return $app['twig']->render(
            'bookmarks/view.html.twig',
            ['bookmarks' => $bookmarks]
        );
    }


    public function viewAction(Application $app, $bookmarkId)
    {
        $bookmarkModel = new BookmarkModel();
        $bookmark = $bookmarkModel->findOneById($bookmarkId);
        if (!isset($bookmark) || !count($bookmark)) {
            $app->abort('404', 'Invalid entry');
        }
        return $app['twig']->render(
            'bookmarks/view.html.twig',
            [
                'bookmark' => $bookmark,
                'bookmarkId' => $bookmarkId
            ]
        );

    }
}
