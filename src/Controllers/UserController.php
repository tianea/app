<?php
/**
 * User controller.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */

namespace Controllers;

use Repository\UserRepository;
use Form\UserType;
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
     * Connect function.
     *
     * @param Application $app
     *
     * @return mixed
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/index', [$this, 'indexAction'])->bind('user_index');
        $controller->get('/{id}', [$this, 'accountAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('user_account');
        $controller->get('/index/{page}', [$this, 'indexAction'])
            ->value('page', 1)
            ->bind('user_index_paginated');
        $controller->get('/sign_up', [$this, 'signUpAction'])
            ->method('POST|GET')
            ->bind('sign_up');
        $controller->get('my_profile', [$this, 'viewAction'])->bind('my_profile');

        return $controller;
    }

    /**
     * Index action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function indexAction(Application $app)
    {
        $userRepository = new UserRepository($app['db']);
        $users = $userRepository->findAll();

        return $app['twig']->render(
            'users/index.html.twig',
            [
                'users' => $users,
            ]
        );
    }

    /**
     * account action.
     *
     * @param \Silex\Application $app Silex application
     * @param int                $id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function accountAction(Application $app, $id)
    {
        $userRepository = new UserRepository($app['db']);
        $user = $userRepository->findOneById($id);

        return $app['twig']->render(
            'users/view.html.twig',
            ['user' => $user]
        );
    }

    /**
     * View action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function viewAction(Application $app)
    {
        $userRepository = new UserRepository($app['db']);
        $userLogin = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userId = $userRepository->findUserIdByLogin($userLogin);

        $user = $userRepository->findOneById($userId);

        if (!isset($user) || !count($user)) {
            $app->abort('404', 'Invalid entry');
        }

        return $app['twig']->render(
            'users/view.html.twig',
            ['user' => $user]
        );
    }

    /**
     * Sign Up action.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function signUpAction(Application $app, Request $request)
    {
        $user = [];

        $form = $app['form.factory']->createBuilder(
            UserType::class,
            $user,
            ['user_repository' => new UserRepository($app['db']), ]
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository = new UserRepository($app['db']);
            $buffer = $form->getData();

            $userRepository->save($app, $buffer);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.successfully_registered',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'), 301);
        }

        return $app['twig']->render(
            'users/add.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

}