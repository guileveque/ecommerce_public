<?php

namespace App\Controller;

use App\Entity\Catalog;
use App\Repository\CatalogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class CatalogController extends AbstractController
{
    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function index( CatalogRepository $catalogRepository): JsonResponse
    {
        $products = $catalogRepository->findAll();

        $serializer = new Serializer([new ObjectNormalizer()]);
        $json = $serializer->normalize($products, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    #[Route('/api/products/{id}', name: 'product', methods: ['GET'])]
    public function show(CatalogRepository $catalogRepository, int $id): JsonResponse
    {
        $product = $catalogRepository->find($id);
        if (!$product) { return new JsonResponse(['message'=> 'product not found'], Response::HTTP_NOT_FOUND); }

        $serializer = new Serializer([new ObjectNormalizer()]);
        $json = $serializer->normalize($product, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, Response::HTTP_OK);
    }

    #[Route('/api/products', name: 'new_catalog', methods: ['POST'])]
    public function new(Request $request, CatalogRepository $catalogRepository): JsonResponse
    {
        $content = $request->toArray();

        if (!isset($content['name']) || !isset($content['description']) || !isset($content['photo']) || !isset($content['price'])) {
            return new JsonResponse(['message'=> 'missing value'], Response::HTTP_EXPECTATION_FAILED);
        }

        $product = new Catalog();

        if(is_string($content['name'])) { $product->setName($content['name']); }
        if(is_string($content['photo'])) { $product->setPhoto($content['photo']); }
        if(is_string($content['description'])) { $product->setDescription($content['description']); }
        if(is_float($content['price'])) { $product->setPrice($content['price']); }

        $product->setCreatedAt(new \DateTimeImmutable());

        $catalogRepository->save($product, true);

        $serializer = new Serializer([new ObjectNormalizer()]);
        $json = $serializer->normalize($product, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, Response::HTTP_CREATED);
    }

    #[Route('/api/products/{id}', name: 'update_catalog', methods: ['PUT'])]
    public function edit(Request $request, CatalogRepository $catalogRepository, int $id): JsonResponse
    {
        $content = $request->toArray();
        $product = $catalogRepository->find($id);

        if(isset($content['name']) && is_string($content['name'])) { $product->setName($content['name']); }
        if(isset($content['photo']) && is_string($content['photo'])) { $product->setPhoto($content['photo']); }
        if(isset($content['description']) && is_string($content['description'])) { $product->setDescription($content['description']); }
        if(isset($content['price']) && is_float($content['price'])) { $product->setPrice($content['price']); }

        $catalogRepository->save($product, true);

        $serializer = new Serializer([new ObjectNormalizer()]);
        $json = $serializer->normalize($product, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, Response::HTTP_CREATED);
    }

    #[Route('/api/products/{id}', name: 'delete_catalog', methods: ['DELETE'])]
    public function delete(CatalogRepository $catalogRepository, int $id): JsonResponse
    {
        $product = $catalogRepository->find($id);
        if (!$product) { return new JsonResponse(['message'=> 'product not found'], Response::HTTP_NOT_FOUND); }

        $catalogRepository->remove($product, true);

        $serializer = new Serializer([new ObjectNormalizer()]);
        $json = $serializer->normalize($product, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, Response::HTTP_ACCEPTED);
    }
}
