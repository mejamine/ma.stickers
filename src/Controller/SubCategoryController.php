<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\SubCategory;
use App\Entity\Category;


class SubCategoryController extends AbstractController
{
    #[Route('/sub/category/{id1}/{id}', name: 'app_sub_category')]
    public function index($id1,$id)
    {
        $category = $this->getDoctrine()
        ->getRepository(Category::class)
        ->find($id);

        $subCategories=$this->getDoctrine()
        ->getManager()->getRepository(SubCategory::class)
        ->findBy(['category'=>$category]);
        return $this->render('sub_category/index.html.twig', [
            'subCategories' => $subCategories, 'category' => $category,'id'=>$id1,
        ]);
    }
}
