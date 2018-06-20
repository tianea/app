<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.06.18
 * Time: 21:08
 */

namespace Controllers;

use Repository\QuestionRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class QuestionController
 **/
class QuestionController implements ControllerProviderInterface
{
    /**
     * @param Application $app
     *
     * @return mixed
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])->bind('questions_view');
        $controller->match('/add/{id}', [$this, 'addAction'])
            ->method('POST|GET')
            ->assert('id', '[1-9]\d*')
            ->bind('questions_add');

        return $controller;
    }

    /**
     * @param Application $app
     * @param int         $page
     *
     * @return mixed
     */
    public function indexAction(Application $app, $page = 1)
    {
        $questionRepository = new QuestionRepository($app['db']);

        return $app['twig']->render(
            'questions/view.html.twig',
            ['paginator' => $questionRepository->findAllPaginated($page)]
        );
    }

    /**
     * @param Application $app
     * @param string      $id  Element Id
     *
     * @return mixed
     */
    public function viewAction(Application $app, $id)
    {
        $questionRepository = new QuestionRepository($app['db']);
        $question = $questionRepository->findOneById($id);

        if (!isset($question) || !count($question)) {
            $app->abort('404', 'Invalid entry');
        }

        return $app['twig']->render(
            'questions/view.html.twig',
            ['question' => $question]
        );
    }


}