<?php
/**
 * Answer controller.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */

namespace Controllers;

use Repository\QuestionRepository;
use Repository\AnswerRepository;
use Form\AnswerType;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AnswerController
 **/
class AnswerController implements ControllerProviderInterface
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
        $controller->get('/{id}/index', [$this, 'indexAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('answers_index');
        $controller->get('/answer/{id}', [$this, 'answerAction'])
            ->method('POST|GET')
            ->assert('id', '[1-9]\d*')
            ->bind('questions_answer');

        return $controller;
    }

    /**
     * Index action.
     *
     * @param Application $app
     * @param int         $id
     *
     * @return mixed
     */
    public function indexAction(Application $app, $id)
    {
        $questionRepository = new QuestionRepository($app['db']);
        $question = $questionRepository->findOneById($id);
        $questionId = $question['id'];

        $answerRepository = new AnswerRepository($app['db']);
        $answers = $answerRepository->findAllByQuestionId($id);

        return $app['twig']->render(
            'answers/index.html.twig',
            [
                'questionId' => $questionId,
                'question' => $question,
                'answers' => $answers,
            ]
        );
    }

    /**
     * Answer action.
     *
     * @param Application $app
     * @param Id          $id
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function answerAction(Application $app, $id, Request $request)
    {
        $questionRepository = new QuestionRepository($app['db']);
        $question = $questionRepository->findOneById($id);
        $surveyId = $question['survey_id'];
        $questionId = $question['id'];

        $answerRepository = new AnswerRepository($app['db']);
        $answer = [];

        if (!$question) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'));
        }

        $form = $app['form.factory']->createBuilder(AnswerType::class, $answer)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $answerRepository->save($form->getData(), $questionId);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            return $app->redirect($app['url_generator']->generate('questions_view', ['id' => $surveyId]), 301);
        }

        return $app['twig']->render(
            'answers/add.html.twig',
            [
                'question' => $question,
                'answer' => $answer,
                'id' => $questionId,
                'form' => $form->createView(),
            ]
        );
    }

}