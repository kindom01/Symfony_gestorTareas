<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Form\RegisterType;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{

    public function register(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        //formulario
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        //rellenar formulario
        $form->handleRequest($request);

        //comprobar formulario
        if ($form->isSubmitted() && $form->isValid()) {
            # guardando objeto...
            $user->setRole('ROLE_USER');
            $user->setCreatedAt(new \DateTime('now'));

            //cifrado
            $encoded = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($encoded);
            
            //guardar usuario
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('tasks');
        }

        return $this->render('user/register.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    public function login(AuthenticationUtils $autenticationUtils){
		$error = $autenticationUtils->getLastAuthenticationError();
		
		$lastUsername = $autenticationUtils->getLastUsername();
		
		return $this->render('user/login.html.twig', array(
			'error' => $error,
			'last_username' => $lastUsername
		));
	}
}
