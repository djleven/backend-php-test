<?php

namespace Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Entity\Todo;
use Entity\User;
use Doctrine\ORM\EntityManager;


class TodoController

{

    /** @var \Silex\Application */
    private $app;

    /** @var RequestStack */
    private $requestStack;

    /** @var \Doctrine\ORM\EntityManager */
    private $orm_em;

    /** @var \Entity\User */
    private $user;


    /**
     * Class constructor
     * @param Application $app
     * @param RequestStack $requestStack
     * @param EntityManager $orm_em
     * @param User $user
     */
    public function __construct(Application $app, RequestStack $requestStack, EntityManager $orm_em, User $user)
    {
        $this->app = $app;
        $this->request = $requestStack->getCurrentRequest();
        $this->orm_em = $orm_em;
        $this->userid = $user->getId();

    }

    /**
     * User todos index view
     *
     * Not adding doc to the TodoController methods yet as some may soon change
     */
    public function indexAction()
    {

        $todos = $this->orm_em->getRepository('Entity\Todo')->getUserTodos($this->userid);
        return $this->app['twig']->render('todos.html', ['todos' => $todos, ]);
    }

    /**
     * Todo single view
     *
     */
    public function viewAction()
    {

        $id = $this->request->get('id');
        // bug squash: check if todo id exists and not if request id
        $todo = $this->orm_em->find('Entity\Todo', $id);    
        if ($todo) {
            if ($this->userid == $todo->getuser_id()) {
                return $this->app['twig']->render('todo.html', ['todo' => $todo, ]);
            }
            else {

                $this->app['session']->getFlashBag()->add('message', 'You are not authorized to view this todo!');

                return $this->app->redirect('/todo');
            }
        }
        else {

            $this->app['session']->getFlashBag()->add('message', 'The specified todo does not exist!');

            return $this->app->redirect('/todo');
        }
    }

    /**
     * Add a todo
     *
     */
    public function addAction()
    {

        $description = $this->request->get('description');
        $errors = $this->app['validator']->validate($description, new Assert\NotBlank());
        if (count($errors) > 0) {

            $this->app['session']->getFlashBag()->add('message', 'The description cannot be blank.');

        }
        else {
            $todo = new Todo();
            $todo->setDescription($description);
            $todo->setuser_id($this->userid);
            $this->orm_em->persist($todo);
            $this->orm_em->flush();

            $this->app['session']->getFlashBag()->add('message', 'The todo has been added!');

        }

        return $this->app->redirect('/todo');
    }

    /**
     * Edit a todo
     *
     */
    public function editAction()
    {

        // this will be for task 2

    }

    /**
     * Delete a todo
     *
     */
    public function deleteAction()
    {

        $id = $this->request->get('id');

        // bug squash: check if todo id exists and not if request id
        $todo = $this->orm_em->find('Entity\Todo', $id);    
        if ($todo) {
            
            // add a check - if todo belongs to this user

            if ($this->userid == $todo->getuser_id()) {
                $this->orm_em->remove($todo);
                $this->orm_em->flush();

                $this->app['session']->getFlashBag()->add('message', 'The todo has been removed!');

            }
            else {
			 
                // Todo: NotFoundHttpException thrown when accessed via direct url
                $this->app['session']->getFlashBag()->add('message', 'You are not authorized to perform this action');

            }
        }
        else {
			// Todo: NotFoundHttpException thrown in this case - must rethink this 
            //$this->app['session']->getFlashBag()->add('message', 'The specified todo does not exist!');

        }

        return $this->app->redirect('/todo');
    }
}
