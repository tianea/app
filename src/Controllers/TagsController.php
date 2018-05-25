<?php
/**
 * Tags controller.
 */
namespace Controllers;

use Repository\TagsRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;

/**
 * Class TagsController.
 */
class TagsController implements ControllerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])->bind('tags_index');
        $controller->get('/page/{page}', [$this, 'indexAction'])
            ->value('page', 1)
            ->bind('tags_index_paginated');
        $controller->get('/{id}', [$this, 'viewAction'])->bind('tags_view');

        return $controller;
    }

    /**
     * Index action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */

    public function indexAction(Application $app, $page = 1)
    {
        $tagsRepository = new TagsRepository($app['db']);

        return $app['twig']->render(
            'tags/index.html.twig',
            ['paginator' => $tagsRepository->findAllPaginated($page)]
        );
    }

    /**
     * View action.
     *
     * @param \Silex\Application $app Silex application
     * @param string             $id  Element Id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function viewAction(Application $app, $id)
    {
        $tagsRepository = new TagsRepository($app['db']);

        return $app['twig']->render(
            'tags/view.html.twig',
            ['tag' => $tagsRepository->findOneById($id)]
        );
    }

    /**
     * Index action.
     *
     * @param \Silex\Application $app  Silex application
     * @param int                $page Current page number
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
}