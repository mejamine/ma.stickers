<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\SubCategory;
use App\Entity\Sticker;

class StickerController extends AbstractController
{
    #[Route('/sticker/{id1}/{id}', name: 'app_sticker')]
    public function index($id1,$id)
    {
        $subCategory = $this->getDoctrine()
        ->getRepository(SubCategory::class)
        ->find($id);

        $stickers=$this->getDoctrine()
        ->getManager()->getRepository(Sticker::class)
        ->findBy(['subCategory'=>$subCategory]);
        return $this->render('sticker/index.html.twig', [
            'stickers' => $stickers,'subCategory'=>$subCategory,'id'=>$id1,
        ]);
    }
}
