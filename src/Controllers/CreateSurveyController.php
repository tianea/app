<?php
/**
 * CreateSurvey controller.
 */
namespace Controllers;

use Repository\SurveyRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Form\CreateSurveyType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CreateSurveyController.
 */
class CreateSurveyController implements ControllerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])->bind('surveys_index');
        $controller->get('/page/{page}', [$this, 'indexAction'])
            ->value('page', 1)
            ->bind('surveys_index_paginated');
        $controller->get('/{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('surveys_view');
        $controller->match('/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('survey_add');

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
        $surveyRepository = new SurveyRepository($app['db']);

        return $app['twig']->render(
            'surveys/index.html.twig',
            ['paginator' => $surveyRepository->findAllPaginated($page)]
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
        $surveyRepository = new SurveyRepository($app['db']);
        $survey = $surveyRepository->findOneById($id);

        if (!isset($survey) || !count($survey)) {
            $app->abort('404', 'Invalid entry');
        }

        return $app['twig']->render(
            'surveys/view.html.twig',
            ['survey' => $survey]
        );
    }

    /**
     * Add action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function addAction(Application $app, Request $request)
    {
        $survey = [];

        $form = $app['form.factory']->createBuilder(CreateSurveyType::class, $survey)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $surveyRepository = new SurveyRepository($app['db']);
            $surveyRepository->save($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'), 301);
        }

        return $app['twig']->render(
            'surveys/add.html.twig',
            [
                'survey' => $survey,
                'form' => $form->createView(),
            ]
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