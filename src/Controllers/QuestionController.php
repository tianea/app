<?php
/**
 * Question controller.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */

namespace Controllers;

use Repository\QuestionRepository;
use Repository\SurveyRepository;
use Repository\UserRepository;
use Form\QuestionType;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class QuestionController
 **/
class QuestionController implements ControllerProviderInterface
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
        $controller->get('/{id}', [$this, 'indexAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('questions_view');
        $controller->match('/{id}/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->assert('id', '[1-9]\d*')
            ->bind('questions_add');
        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('questions_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('questions_delete');
        $controller->get('/{id}/start', [$this, 'indexAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('survey_start');

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
        $userRepository = new UserRepository($app['db']);
        $userLogin = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userId = $userRepository->findUserIdByLogin($userLogin);

        $questionRepository = new QuestionRepository($app['db']);
        $questions = $questionRepository->findAllBySurveyId($id);

        $surveyRepository = new SurveyRepository($app['db']);
        $survey = $surveyRepository->findOneById($id);

        return $app['twig']->render(
            'questions/index.html.twig',
            [
                'userId' => $userId,
                'questions' => $questions,
                'survey' => $survey,
            ]
        );
    }

    /**
     * View action.
     *
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

    /**
     * Add action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     * @param int                                       $id
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function addAction(Application $app, Request $request, $id)
    {
        $surveyRepository = new SurveyRepository($app['db']);
        $survey = $surveyRepository->findOneById($id);
        $surveyId = $survey['id'];

        $userRepository = new UserRepository($app['db']);
        $userLogin = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userId = $userRepository->findUserIdByLogin($userLogin);
        $authorId = $survey['user_id'];
        $userRole = $app['security.token_storage']->getToken()->getUser()->getRoles();

        if ($userId != $authorId and $userRole[0] != ('ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.access_denied',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'));
        }

        $question = [];

        $form = $app['form.factory']->createBuilder(QuestionType::class, $question)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $questionRepository = new QuestionRepository($app['db']);
            $questionRepository->save($form->getData(), $surveyId);

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
            'questions/add.html.twig',
            [
                'question' => $question,
                'survey' => $survey,
                'id' => $surveyId,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Edit action.
     *
     * @param Application $app
     * @param int         $id
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Application $app, $id, Request $request)
    {
        $questionRepository = new QuestionRepository($app['db']);
        $question = $questionRepository->findOneById($id);
        $surveyId = $question['survey_id'];

        $userRepository = new UserRepository($app['db']);
        $userLogin = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userId = $userRepository->findUserIdByLogin($userLogin);
        $authorId = $question['user_id'];
        $userRole = $app['security.token_storage']->getToken()->getUser()->getRoles();

        if ($userId != $authorId and $userRole[0] != ('ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.access_denied',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'));
        }

        if (!$question) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('questions_view', ['id' => $surveyId]));
        }

        $form = $app['form.factory']->createBuilder(QuestionType::class, $question)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $questionRepository->save($form->getData(), $surveyId);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('questions_view', ['id' => $surveyId]), 301);
        }

        return $app['twig']->render(
            'questions/edit.html.twig',
            [
                'question' => $question,
                'id' => $surveyId,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param int                                       $id      Record id
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function deleteAction(Application $app, $id, Request $request)
    {
        $questionRepository = new QuestionRepository($app['db']);
        $question = $questionRepository->findOneById($id);
        $surveyId = $question['survey_id'];

        $userRepository = new UserRepository($app['db']);
        $userLogin = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userId = $userRepository->findUserIdByLogin($userLogin);
        $authorId = $question['user_id'];
        $userRole = $app['security.token_storage']->getToken()->getUser()->getRoles();

        if ($userId != $authorId and $userRole[0] != ('ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.access_denied',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'));
        }

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

        $form = $app['form.factory']->createBuilder(FormType::class, $question)
            ->add('id', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $questionRepository->delete($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_deleted',
                ]
            );

            return $app->redirect(
                $app['url_generator']->generate('questions_view', ['id' => $surveyId]),
                301
            );
        }

        return $app['twig']->render(
            'questions/delete.html.twig',
            [
                'question' => $question,
                'form' => $form->createView(),
            ]
        );
    }


}