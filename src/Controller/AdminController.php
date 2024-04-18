<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Sticker;
use App\Entity\Category;
use App\Entity\SubCategory;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    #[Route('/viewCategories', name: 'viewCategories')]
    public function viewCategories(){
        $categories=$this->getDoctrine()->getManager()
        ->getRepository(Category::class)
        ->findAll();
        return $this->render('admin/viewCategoryAdmin.html.twig',[
            'categories' => $categories,
        ]);
    }
    #[Route('/viewSubCategories/{id}', name: 'viewSubCategories')]
    public function viewSubCategories($id){
        $category = $this->getDoctrine()
        ->getRepository(Category::class)
        ->find($id);
        $subCategories=$this->getDoctrine()
        ->getManager()->getRepository(SubCategory::class)
        ->findBy(['category'=>$category]);
        return $this->render('admin/viewSubCategoryAdmin.html.twig',[
            'subCategories' => $subCategories,'category'=>$category,
        ]);
    }
    #[Route('/viewStickers/{id}', name: 'viewStickers')]
    public function viewStickers($id){
        $subCategory = $this->getDoctrine()
        ->getRepository(SubCategory::class)
        ->find($id);

        $stickers=$this->getDoctrine()
        ->getManager()->getRepository(Sticker::class)
        ->findBy(['subCategory'=>$subCategory]);
        return $this->render('admin/viewStickersAdmin.html.twig',[
            'stickers' => $stickers,'subCategory'=>$subCategory,
        ]);
    }
    #[Route('/addCategory', name: 'addCategory')]
    public function addCategory(Request $request){
        $category = new Category();
        $f = $this->createFormBuilder($category)
        ->add('name',TextType::class)
        ->add('Submit', SubmitType::class);
        $form= $f->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute('viewCategories');
        }
        return $this->render('admin/addCategory.html.twig',
        ['f' => $form->createView()]);
    
    }
    #[Route('/addSubCategory/{id}', name: 'addSubCategory')]
    public function addSubCategory(Request $request,ParameterBagInterface $parameterBag,$id){
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        $subCategory = new SubCategory();
        $f = $this->createFormBuilder($subCategory)
        ->add('name',TextType::class)
        ->add('image',FileType::class)
        ->add('Submit', SubmitType::class);
        $form= $f->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $directory=$parameterBag->get('upload_directory_subCategory');
            $file=$form['image']->getData();
            $file->move($directory, $file->getClientOriginalName());
            $subCategory->setCategory($category);
            $subCategory->setImage($file->getClientOriginalName());
            $em=$this->getDoctrine()->getManager();
            $em->persist($subCategory);
            $em->flush();
            return $this->redirectToRoute('viewSubCategories',['id'=>$id]);
        }
        return $this->render('admin/addSubCategory.html.twig',
        ['f' => $form->createView()]);
    }
    #[Route('/addSticker/{id}', name: 'addSticker')]
    public function addSticker(Request $request,ParameterBagInterface $parameterBag,$id){
        $subCategory = $this->getDoctrine()->getRepository(SubCategory::class)->find($id);
        $sticker = new Sticker();
        $f = $this->createFormBuilder($sticker)
        ->add('image',FileType::class)
        ->add('Submit', SubmitType::class);
        $form= $f->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $directory=$parameterBag->get('upload_directory_sticker');
            $file=$form['image']->getData();
            $file->move($directory, $file->getClientOriginalName());
            $sticker->setSubCategory($subCategory);
            $sticker->setImage($file->getClientOriginalName());
            $sticker->setPrice('0.700');
            $em=$this->getDoctrine()->getManager();
            $em->persist($sticker);
            $em->flush();
            return $this->redirectToRoute('viewStickers',['id'=>$id]);
        }
        return $this->render('admin/addSticker.html.twig',
        ['f' => $form->createView()]);
    }
    #[Route('/deleteCategory/{id}', name: 'deleteCategory')]
    public function deleteCategory(Request $request,$id){
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);
        if(!$category){
            throw $this->createNotFoundException('No Category found for id'.$id);
        }
        $subCategories=$this->getDoctrine()->getRepository(SubCategory::class)->findBy(['category'=>$category]);
        foreach($subcategories as $subcategory){
            $stickers=$this->getDoctrine()->getRepository(Sticker::class)->findBy(['subCategory'=>$subCategory]);
            foreach($stickers as $sticker){
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($sticker);
                $entityManager->flush();
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($subCategory);
            $entityManager->flush();
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($category);
        $entityManager->flush();
        return $this->redirectToRoute('viewCategories');
    
    }
    #[Route('/deleteSubCategory/{id}/{id2}', name: 'deleteSubCategory')]
    public function deleteSubCategory(Request $request,$id,$id2){
        $subCategory = $this->getDoctrine()->getRepository(SubCategory::class)->find($id);
        if(!$subCategory){
            throw $this->createNotFoundException('No SubCategory found for id'.$id);
        }
        $stickers=$this->getDoctrine()->getRepository(Sticker::class)->findBy(['subCategory'=>$subCategory]);
        foreach($stickers as $sticker){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($sticker);
            $entityManager->flush();
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($subCategory);
        $entityManager->flush();
        return $this->redirectToRoute('viewSubCategories',['id'=>$id2]);
    }
    #[Route('/deleteSticker/{id}/{id2}', name: 'deleteSticker')]
    public function deleteSticker(Request $request,$id,$id2){
        $sticker = $this->getDoctrine()->getRepository(Sticker::class)->find($id);
        if(!$sticker){
            throw $this->createNotFoundException('No Sticker found for id'.$id);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($sticker);
        $entityManager->flush();
        return $this->redirectToRoute('viewStickers',['id'=>$id2]);
    }
    #[Route('/updateCategory/{id}', name: 'updateCategory')]
    public function updateCategory(Request $request,$id){
        $category = new Category();
        $category = $this->getDoctrine()
        ->getRepository(Category::class)
        ->find($id);
        if(!$category){
            throw $this->createNotFoundException(
                    'No Category Found For id ' .$id
            );
        }
        $fb = $this->createFormBuilder($category)
        ->add('name', TextType::class)
        ->add('Submit', SubmitType::class);
        $form=$fb->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('viewCategories');
        }
        return $this->render('admin/editCategory.html.twig',[
            'f' => $form->createView(),
        ]);
    
    }
    #[Route('/updateSubCategory/{id}/{id2}', name: 'updateSubCategory')]
    public function updateSubCategory(Request $request,$id,$id2,ParameterBagInterface $parameterBag){
        $subCategory = new SubCategory();
        $subCategory = $this->getDoctrine()
        ->getRepository(SubCategory::class)
        ->find($id);
        if(!$subCategory){
            throw $this->createNotFoundException(
                    'No SubCategory Found For id ' .$id
            );
        }
        $fb = $this->createFormBuilder($subCategory)
        ->add('name', TextType::class)
        ->add('category',EntityType::class,
        ['class'=>Category::class,
        'choice_label'=>'name'])
        ->add('Submit', SubmitType::class);
        $form=$fb->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('viewSubCategories',['id'=>$id2]);
        }
        return $this->render('admin/editSubCategory.html.twig',[
            'f' => $form->createView(),
        ]);
    }
    #[Route('/updateSubCategoryImage/{id}/{id2}', name: 'updateSubCategoryImage')]
    public function updateSubCategoryImage(Request $request,$id,$id2,ParameterBagInterface $parameterBag){
            $subCategory = $this->getDoctrine()
            ->getRepository(SubCategory::class)
            ->find($id);
            if(!$subCategory){
                throw $this->createNotFoundException(
                        'No SubCategory Found For id ' .$id
                );
            }
            $fb = $this->createFormBuilder($subCategory)
            ->add('image', FileType::class,[
                'data_class'=>null])
            ->add('Submit', SubmitType::class);
            $form=$fb->getForm();
            $form->handleRequest($request);
            if($form->isSubmitted())
            {
                $directory=$parameterBag->get('upload_directory_subCategory');
                $file=$form['image']->getData();
                $file->move($directory, $file->getClientOriginalName());
                $subCategory->setImage($file->getClientOriginalName());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                return $this->redirectToRoute('viewSubCategories',['id'=>$id2]);
            }
            return $this->render('admin/editImageSubCategory.html.twig',[
                'f' => $form->createView(),
            ]);
    }
    #[Route('/updateSticker/{id}/{id2}', name: 'updateSticker')]
    public function updateSticker(Request $request,$id,$id2,ParameterBagInterface $parameterBag){
        $sticker = $this->getDoctrine()
            ->getRepository(Sticker::class)
            ->find($id);
            if(!$sticker){
                throw $this->createNotFoundException(
                        'No Sticker Found For id ' .$id
                );
            }
            $fb = $this->createFormBuilder($sticker)
            ->add('image', FileType::class,[
                'data_class'=>null])
            ->add('Submit', SubmitType::class);
            $form=$fb->getForm();
            $form->handleRequest($request);
            if($form->isSubmitted())
            {
                $directory=$parameterBag->get('upload_directory_sticker');
                $file=$form['image']->getData();
                $file->move($directory, $file->getClientOriginalName());
                $sticker->setImage($file->getClientOriginalName());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                return $this->redirectToRoute('viewStickers',['id'=>$id2]);
            }
            return $this->render('admin/editSticker.html.twig',[
                'f' => $form->createView(),
            ]);
    }
}
