<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Sticker;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\SubCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(): Response
    {
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }
    #[Route('/addToCard/{idUser}/{url}/{id2}', name: 'addToCard')]
    public function addToCard($idUser,$url,$id2)
    {

        $subCategory = $this->getDoctrine()
        ->getRepository(SubCategory::class)
        ->find($id2);

        $stickers=$this->getDoctrine()
        ->getManager()->getRepository(Sticker::class)
        ->findBy(['subCategory'=>$subCategory]);
        $s=0;
        $orders=$this->getDoctrine()->getRepository(Order::class)->findAll();
        if($orders!==[null]){
            foreach($orders as $order1 ){
                if($order1->getImage()===$url){
                    $s=1;
                    break;
                }
            }}
        if($s!==1){
            $order=new Order();
            $user=$this->getDoctrine()->getRepository(User::class)->find($idUser);
            $order->setImage($url);
            $order->setUser($user);
            $order->setValide(false);
            $order->setNumber(1);
            $em=$this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();
            return $this->redirectToRoute('app_sticker', [
                'id1' => $idUser,'id'=>$id2,
            ]);
        }
        return $this->redirectToRoute('app_sticker', [
            'id1' => $idUser,'id'=>$id2,
        ]);
        
    }
    #[Route('/viewCard/{id}', name: 'viewCard')]
    public function viewCard($id)
    {
        $user=$this->getDoctrine()->getRepository(User::class)->find($id);
        $orders=$this->getDoctrine()
        ->getManager()->getRepository(Order::class)
        ->findBy(['User'=>$user,'valide'=>false]);
        return $this->render('order/index.html.twig', [
            'id' => $id,'orders'=>$orders,
        ]);
    }
    #[Route('/deleteOrder/{id}/{id1}', name: 'deleteOrder')]
    public function deleteOrder(Request $request,$id,$id1){
        $order = $this->getDoctrine()->getRepository(Order::class)->find($id);
        if(!$order){
            throw $this->createNotFoundException('No order found for id'.$id);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($order);
        $entityManager->flush();
        return $this->redirectToRoute('viewCard',['id'=>$id1]);
    
    }
    #[Route('/updateNumber/{id}/{id1}', name: 'updateNumber')]
    public function updateNumber(Request $request,$id,$id1){
        $order = new Order();
        $order = $this->getDoctrine()
        ->getRepository(Order::class)
        ->find($id);
        if(!$order){
            throw $this->createNotFoundException(
                    'No Category Found For id ' .$id
            );
        }
        $fb = $this->createFormBuilder($order)
        ->add('number', IntegerType::class)
        ->add('Submit', SubmitType::class);
        $form=$fb->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('viewCard',['id'=>$id1]);
        }
        return $this->render('order/editNumber.html.twig',[
            'f' => $form->createView(),
        ]);
    
    }
    #[Route('/orderWrong/{id}', name: 'orderWrong')]
    public function orderWrong($id)
    {
        return $this->render('order/orderWrong.html.twig',[
            'id' => $id,
        ]);
    }
    #[Route('/order/{id}', name: 'order')]
    public function order($id)
    {
        $user=$this->getDoctrine()->getRepository(User::class)->find($id);
        $orders=$this->getDoctrine()
        ->getManager()->getRepository(Order::class)
        ->findBy(['User'=>$user,'valide'=>false]);
        $s=0;
        foreach($orders as $order){
            $s=$s+$order->getNumber();
        }
        if($s<10){
            return $this->redirectToRoute('orderWrong',['id'=>$id]);
        }
        else{
            foreach($orders as $order){
                $order->setValide(true);
                $em=$this->getDoctrine()->getManager();
                $em->persist($order);
                $em->flush();
            }
            return $this->redirectToRoute('viewCard',['id'=>$id]);
        }
        
    }
    #[Route('/viewOrder/{id}', name: 'viewOrder')]
    public function viewCardA($id)
    {
        $user=$this->getDoctrine()->getRepository(User::class)->find($id);
        $orders=$this->getDoctrine()
        ->getManager()->getRepository(Order::class)
        ->findBy(['User'=>$user,'valide'=>true]);
        return $this->render('admin/viewOrders.html.twig', [
            'id' => $id,'orders'=>$orders,'user'=>$user,
        ]);
    }
    #[Route('/done/{id}', name: 'done')]
    public function done(Request $request,$id){
        $user=$this->getDoctrine()->getRepository(User::class)->find($id);
        $orders = $this->getDoctrine()->getRepository(Order::class)->findBy(['User'=>$user,'valide'=>true]);
        if(!$orders){
            throw $this->createNotFoundException('No order found for id'.$id);
        }
        foreach($orders as $order){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($order);
            $entityManager->flush();
        }
        
        return $this->redirectToRoute('admin');
    
    }



}
