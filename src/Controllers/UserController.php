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
use Form\AccountType;
use Form\PasswdType;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController.
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
        $controller->get('/{id}/edit', [$this, 'editAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('user_edit');
        $controller->get('/{id}/edit_password', [$this, 'editPasswdAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('user_passwd_edit');
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
        $userLogin = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userId = $userRepository->findUserIdByLogin($userLogin);

        return $app['twig']->render(
            'users/view.html.twig',
            [
                'userId' => $userId,
                'user' => $user,
            ]
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
            [
                'userId' => $userId,
                'user' => $user,
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param Application $app
     * @param Id          $id
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Application $app, $id, Request $request)
    {
        $userRepository = new UserRepository($app['db']);
        $userLogin = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userId = $userRepository->findUserIdByLogin($userLogin);

        $user = $userRepository->findOneById($id);
        $accountId = $user['id'];

        $userRole = $app['security.token_storage']->getToken()->getUser()->getRoles();

        if ($userId !== $accountId and $userRole[0] !== ('ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.access_denied',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'));
        }

        if (!$user) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'));
        }

        $form = $app['form.factory']->createBuilder(AccountType::class, $user)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($form->getData(), $userId);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.account_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'), 301);
        }

        return $app['twig']->render(
            'users/edit.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Password edit action.
     *
     * @param Application $app
     * @param Id          $id
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editPasswdAction(Application $app, $id, Request $request)
    {
        $userRepository = new UserRepository($app['db']);
        $userLogin = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userId = $userRepository->findUserIdByLogin($userLogin);

        $user = $userRepository->findOneById($id);
        $accountId = $user['id'];

        $userRole = $app['security.token_storage']->getToken()->getUser()->getRoles();

        if ($userId !== $accountId and $userRole[0] !== ('ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.access_denied',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'));
        }

        if (!$user) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'));
        }

        $form = $app['form.factory']->createBuilder(PasswdType::class, $user)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user['password'] = $app['security.encoder.bcrypt']->encodePassword($user['password'], '');
            $userRepository->save($user);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.password_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'), 301);
        }

        return $app['twig']->render(
            'users/edit_passwd.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
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
        $userRepository = new UserRepository($app['db']);

        $form = $app['form.factory']->createBuilder(
            UserType::class,
            $user,
            ['user_repository' => $userRepository]
        )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user['password'] = $app['security.encoder.bcrypt']->encodePassword($user['password'], '');
            $userRepository->save($user);

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
