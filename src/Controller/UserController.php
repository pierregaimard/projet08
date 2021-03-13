<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Security\UserPasswordManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="user_list")
     */
    public function list()
    {
        return $this->render(
            'user/list.html.twig',
            [
                'users' => $this->getDoctrine()->getRepository(User::class)->findAll()
            ]
        );
    }

    /**
     * @Route("/users/create", name="user_create")
     */
    public function create(Request $request, UserPasswordManager $passwordManager)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordManager->setUserPassword($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     */
    public function edit(User $user, Request $request, UserPasswordManager $passwordManager)
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordManager->setUserPassword($user);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    /**
     * @Route("/users/{id}/delete", name="user_delete")
     */
    public function delete(User $user, Request $request, CsrfTokenManagerInterface $tokenManager)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->isMethod('POST')) {
            $token = new CsrfToken('token_delete_user', $request->get('csrf_token'));
            if (!$tokenManager->isTokenValid($token)) {
                $this->addFlash('danger', "Jeton CSRF invalide.");

                return $this->redirectToRoute('user_list');
            }

            $em->remove($user);
            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été supprimé");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/delete.html.twig', ['user' => $user]);
    }
}
