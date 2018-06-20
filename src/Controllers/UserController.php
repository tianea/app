<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 14.06.18
 * Time: 20:08
 */

namespace Controllers;

use Repository\UserRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController
 * @package Controllers
 */

class UserController implements ControllerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/index', [$this, 'indexAction'])->bind('user_index');
        $controller->get('/{id}', [$this, 'indexAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('user_account');
        $controller->get('/index/{page}', [$this, 'indexAction'])
            ->value('page', 1)
            ->bind('user_index_paginated');

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
        $userRepository = new UserRepository($app['db']);

        return $app['twig']->render(
            'users/view.html.twig',
            ['paginator' => $userRepository->findAllPaginated($page)]
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
        $userRepository = new UserRepository($app['db']);
        $user = $userRepository->findOneById($id);

        if (!isset($user) || !count($user)) {
            $app->abort('404', 'Invalid entry');
        }

        return $app['twig']->render(
            'user/view.html.twig',
            ['user' => $user]
        );
    }

}