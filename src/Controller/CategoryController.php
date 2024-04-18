<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;

class CategoryController extends AbstractController
{
    #[Route('/category/{id}', name: 'users')]
    public function index($id)
    {
        $categories=$this->getDoctrine()->getManager()->getRepository(Category::class)->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories,'id'=>$id,
        ]);
    }
}
