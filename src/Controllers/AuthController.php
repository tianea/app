<?php
/**
 * Auth controller.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */
namespace Controllers;

use Form\LoginType;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthController.
 */
class AuthController implements ControllerProviderInterface
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
        $controller->match('login', [$this, 'loginAction'])
            ->method('GET|POST')
            ->bind('auth_login');
        $controller->get('logout', [$this, 'logoutAction'])
            ->bind('auth_logout');

        return $controller;
    }

    /**
     * Login action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function loginAction(Application $app, Request $request)
    {
        $user = ['login' => $app['session']->get('_security.last_username')];
        $form = $app['form.factory']->createBuilder(LoginType::class, $user)->getForm();

        return $app['twig']->render(
            'auth/login.html.twig',
            [
                'form' => $form->createView(),
                'error' => $app['security.last_error']($request),
            ]
        );
    }

    /**
     * Logout action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function logoutAction(Application $app)
    {
        $app['session']->clear();

        return $app['twig']->render('auth/logout.html.twig', []);
    }
}
