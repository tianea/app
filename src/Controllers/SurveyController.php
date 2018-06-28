<?php
/**
 * CreateSurvey controller.
 */
namespace Controllers;
use Repository\SurveyRepository;
use Repository\UserRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Form\SurveyType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class SurveyController.
 */
class SurveyController implements ControllerProviderInterface
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
        $controller->get('/', [$this, 'indexAction'])->bind('surveys_index');
        $controller->get('/page/{page}', [$this, 'indexAction'])
            ->value('page', 1)
            ->bind('surveys_index_paginated');
        $controller->get('/{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('surveys_view');
        $controller->match('/create', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('survey_create');
        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('surveys_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('surveys_delete');

        return $controller;
    }

    /**
     * Index action.
     *
     * @param \Silex\Application $app  Silex application
     * @param int                $page
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
        $userRepository = new UserRepository($app['db']);
        $userLogin = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userId = $userRepository->findUserIdByLogin($userLogin);

        $surveyRepository = new SurveyRepository($app['db']);
        $survey = $surveyRepository->findOneById($id);

        if (!isset($survey) || !count($survey)) {
            $app->abort('404', 'Invalid entry');
        }

        return $app['twig']->render(
            'surveys/view.html.twig',
            [
                'userId' => $userId,
                'survey' => $survey,
            ]
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
        $userRepository = new UserRepository($app['db']);
        $userLogin = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $survey = [];

        $userId = $userRepository->findUserIdByLogin($userLogin);

        $form = $app['form.factory']->createBuilder(SurveyType::class, $survey)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $surveyRepository = new SurveyRepository($app['db']);
            $surveyRepository->save($form->getData(), $userId);

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

        $surveyRepository = new SurveyRepository($app['db']);
        $survey = $surveyRepository->findOneById($id);
        $surveyId = $survey['id'];

        $userRole = $app['security.token_storage']->getToken()->getUser()->getRoles();
        $authorId = $survey['user_id'];

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

        if (!$survey) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'));
        }

        $form = $app['form.factory']->createBuilder(SurveyType::class, $survey)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $surveyRepository->save($form->getData(), $userId);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_view', ['id' => $surveyId]), 301);
        }

        return $app['twig']->render(
            'surveys/edit.html.twig',
            [
                'survey' => $survey,
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
        $userRepository = new UserRepository($app['db']);
        $userLogin = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userId = $userRepository->findUserIdByLogin($userLogin);

        $surveyRepository = new SurveyRepository($app['db']);
        $survey = $surveyRepository->findOneById($id);

        $userRole = $app['security.token_storage']->getToken()->getUser()->getRoles();
        $authorId = $survey['user_id'];

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

        if (!$survey) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('surveys_index'));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $survey)->add('id', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $surveyRepository->delete($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_deleted',
                ]
            );

            return $app->redirect(
                $app['url_generator']->generate('surveys_index'),
                301
            );
        }

        return $app['twig']->render(
            'surveys/delete.html.twig',
            [
                'survey' => $survey,
                'form' => $form->createView(),
            ]
        );
    }
}