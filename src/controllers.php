<?php
/**
 * Controllers file.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Controllers\AuthController;
use Controllers\SurveyController;
use Controllers\UserController;
use Controllers\QuestionController;
use Controllers\AnswerController;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->mount('/auth', new AuthController());
$app->mount('/surveys', new SurveyController());
$app->mount('/user', new UserController());
$app->mount('/questions', new QuestionController());
$app->mount('/answer', new AnswerController());

$app->get('/', function () use ($app) {
    return $app['twig']->render('surveys/index.html.twig', array());
})
    ->bind('homepage')
;

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
