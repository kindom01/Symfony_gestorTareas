<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\User\UserInterface;

use App\Entity\Task;
use App\Entity\User;

use App\Form\TaskType;


class TaskController extends AbstractController
{

    public function index(): Response
    {

        $em = $this->getDoctrine()->getManager();

        $task_repo = $this->getDoctrine()->getRepository(Task::class);
        $tasks = $task_repo->findAll();

        return $this->render('task/index.html.twig', [
            "tasks" => $tasks,
        ]);
    }

    public function detail(Task $task){
        if (!$task) {
            # code...
            return $this->redirectToRout('tasks');
        }

        return $this->render('task/detail.html.twig', array(
            'task' => $task,
        ));
    }

    public function creation(Request $request,  UserInterface $user){

        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            # code...
            $task->setCreatedAt(new \DateTime('now'));
            $task->setUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);

            $em->flush();

            return $this->redirect(
                $this->generateUrl('task_detail', ['id' => $task->getId()])
            );
        }

        return $this->render('task/creation.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function myTasks(UserInterface $user){
        $tasks = $user->getTasks();

        return $this->render('task/my-tasks.html.twig', array(
            'tasks' => $tasks,
        ));
    }

    public function edit(Request $request, UserInterface $user, Task $task){
		if(!$user || $user->getId() != $task->getUser()->getId()){
			return $this->redirectToRoute('tasks');
		}
		
		$form = $this->createForm(TaskType::class, $task);
		
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid()){
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($task);
			$em->flush();
			
			return $this->redirect(
                    
                    $this->generateUrl('task_detail', ['id' => $task->getId()])    
                );
		}
		
		return $this->render('task/creation.html.twig',[
			'edit' => true,
			'form' => $form->createView()
		]);
	}

    public function delete(UserInterface $user, Task $task){
		if(!$user || $user->getId() != $task->getUser()->getId()){
			return $this->redirectToRoute('tasks');
		}
		
		if(!$task){
			return $this->redirectToRout('tasks');
		}
		
		$em = $this->getDoctrine()->getManager();
		$em->remove($task);
		$em->flush();
		
		return $this->redirectToRoute('tasks');
	}
}
