<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks/{type}", name="task_list", requirements={"type"="todo|done"})
     */
    public function list(string $type, TaskRepository $taskRepository)
    {
        $isDone = ($type === Task::TODO) ? false : true;
        $hasTask = count($taskRepository->findAll()) > 0;
        $navName = ($type === Task::TODO) ? 'navTodo' : 'navDone';

        return $this->render(
            'task/list.html.twig',
            [
                'tasks' => $taskRepository->findBy(['isDone' => $isDone], ['createdAt' => 'DESC']),
                'hasTask' => $hasTask,
                'listType' => $type,
                $navName => true,
            ]
        );
    }

    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function create(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setOwner($this->getUser());

            $em = $this->getDoctrine()->getManager();

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list', ['type' => Task::TODO]);
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     */
    public function edit(Task $task, Request $request)
    {
        $type = $this->getListType($task);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list', ['type' => $type]);
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
            'type' => $type,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTask(Task $task)
    {
        $type = $this->getListType($task);
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        $status = ($task->isDone()) ? 'faite' : 'non terminée';

        $this->addFlash(
            'success',
            sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $status)
        );

        return $this->redirectToRoute('task_list', ['type' => $type]);
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @IsGranted(
     *     "TASK_DELETE",
     *     subject="task",
     *     message="Vous n'êtes pas autorisé(e) à supprimer cette tâche",
     *     statusCode=403
     * )
     */
    public function deleteTask(Task $task)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list', ['type' => $this->getListType($task)]);
    }

    /**
     * @param Task $task
     *
     * @return string
     */
    private function getListType(Task $task): string
    {
        return ($task->isDone()) ? Task::DONE : Task::TODO;
    }
}
