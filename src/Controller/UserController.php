<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    #[Route('/viewUsers', name: 'viewUsers')]
    public function index(): Response
    {
        $users = $this->getDoctrine()
        ->getRepository(User::class)
        ->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }
    #[Route('/addUser', name: 'addUser')]
    public function addUser(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('viewUsers');
         }
        return $this->render('security/form.html.twig', array('form' => $form->createView()));
    }
    #[Route('/deleteUser/{id}', name: 'deleteUser')]
    public function deleteUser(Request $request,$id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        if(!$user){
            throw $this->createNotFoundException('No User found for id'.$id);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->redirectToRoute('viewUsers');
    }
    #[Route('/editUser/{id}',name: 'editUser')]
    public function editUser(Request $request,$id,UserPasswordEncoderInterface $passwordEncoder){
        $user = new User();
        $user = $this->getDoctrine()
        ->getRepository(User::class)
        ->find($id);
        if(!$user){
            throw $this->createNotFoundException(
                    'No User Found For id ' .$id
            );
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('users',['id'=>$id]);
         }
        return $this->render('user/editUser.html.twig', array('form' => $form->createView()));
    }
}
