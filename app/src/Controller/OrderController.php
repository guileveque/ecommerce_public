<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\OrderRepository;
use App\Repository\CatalogRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;



class OrderController extends AbstractController
{
    #[Route('/api/orders', name: 'list_orders')]
    public function index(OrderRepository $orderRepository): JsonResponse
    {           
        $order = $orderRepository->findAllByPurchaserId($this->getUser()->getId());

        if($order == null){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $serializer = new Serializer([new ObjectNormalizer()]);
        $json = $serializer->normalize($order, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    #[Route('/api/orders/{id}', name: 'get_order')]
    public function show(OrderRepository $orderRepository, int $id): JsonResponse
    {

        if(is_int($id) == false){
            return new JsonResponse(null, Response::HTTP_BAD_PARAMETER);
        }
        $order = $orderRepository->find($id);


        if($order->getPurchaser()->getId() != $this->getUser()->getId()){
            return new JsonResponse("This is not your order", Response::HTTP_FORBIDDEN);
        }

        if($order == null){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $serializer = new Serializer([new ObjectNormalizer()]);
        $json = $serializer->normalize($order, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    #[Route('/api/carts/validate', name: 'validate_cart', methods: ['POST'])]
    public function validateCart(OrderRepository $orderRepository): JsonResponse
    {           
        $order = $orderRepository->findOneBy(['cartStatus' => true]);
        if($order == null){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $order->setCartStatus(false);
        $orderRepository->save($order, true);
        $serializer = new Serializer([new ObjectNormalizer()]);
        $json = $serializer->normalize($order, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    #[Route('/api/carts/{product_id}', name: 'carts', methods: ['POST'])]
    public function addProductToCart(OrderRepository $orderRepository, CatalogRepository $catalogRepository, int $product_id): JsonResponse
    {    
        if(is_int($product_id) == false){
            return new JsonResponse(null, Response::HTTP_BAD_PARAMETER);
        }       
        $order = $orderRepository->findOneBy(['cartStatus' => true]);
        $product = $catalogRepository->findOneBy(['id' => $product_id]);
        if($order->getCartStatus() == true){
            $order->addProduct($product);
        }
        else{
            $order = new Order();
            $order->setCartStatus(true);
            $order->addProduct($product_id);
        }
        $orderRepository->save($order, true);
        $serializer = new Serializer([new ObjectNormalizer()]);
        $json = $serializer->normalize($order, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    #[Route('/api/carts/{product_id}', name: 'remove_product_from_cart', methods: ['DELETE'])]
    public function removeProductFromCart(OrderRepository $orderRepository, CatalogRepository $catalogRepository, int $product_id): JsonResponse
    {      
        if(is_int($product_id) == false){
            return new JsonResponse(null, Response::HTTP_BAD_PARAMETER);
        }
        $order = $orderRepository->findOneBy(['cartStatus' => true]);
        $product = $catalogRepository->findOneBy(['id' => $product_id]);
        if($order->getCartStatus() == true){
            $order->removeProduct($product);
        }
        $orderRepository->save($order, true);
        $serializer = new Serializer([new ObjectNormalizer()]);
        $json = $serializer->normalize($order, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    #[Route('/api/carts', name: 'get_cart', methods: ['GET'])]
    public function getCart(OrderRepository $orderRepository): JsonResponse
    {           
        $order = $orderRepository->findOneBy(['cartStatus' => true]);
        if($order == null){
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $serializer = new Serializer([new ObjectNormalizer()]);
        $json = $serializer->normalize($order, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json['products'], Response::HTTP_OK);
    }
}
