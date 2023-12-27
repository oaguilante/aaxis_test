<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Security\AccessTokenHandler;

class ProductController extends AbstractController
{   
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/products/load', name:'/products/load', methods: ['GET', 'POST'])]
    public function loadProductsView(): Response
    {
        return $this->render('product/load_products.html.twig');
    }

    #[Route('/api/products/load', methods: ['POST'])]
    public function loadProducts(Request $request, AccessTokenHandler $accessTokenHandler): Response
    {
        $authorizationHeader = $request->headers->get('Authorization');

        $response = $accessTokenHandler->validateToken($authorizationHeader);
        
        if ($response !== null) {
            return $response;
        }

        $data = json_decode($request->getContent(), true);
        try {
            if (isset($data['products']) && is_array($data['products'])) {
                foreach ($data['products'] as $productData) {
                    
                    $existingProduct = $this->entityManager->getRepository(Product::class)->findOneBy(['sku' => $productData['sku']]);

                    if ($existingProduct) {
                        continue; 
                    }

                    $product = new Product();

                    $product->setSku($productData['sku']);
                    $product->setProductName($productData['product_name']);
                    $product->setDescription($productData['description']);
                    
                    $createdAt = new \DateTimeImmutable();
                    $updatedAt = new \DateTimeImmutable();
                    $product->setCreatedAt($createdAt);
                    $product->setUpdateAt($updatedAt);

                    $this->entityManager->persist($product);
                }
                $this->entityManager->flush();
                
                return new Response('Products loaded successfully.', Response::HTTP_CREATED);
            } else {
                return new Response('Incorrect payload or no products provided.', Response::HTTP_BAD_REQUEST);
            }

        } catch (\Exception $e) {
            return new Response('Error loading the products: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    #[Route('/products/update', name:'/products/update', methods: ['GET', 'POST'])]
    public function updateProductsView(): Response
    {
        return $this->render('product/update_products.html.twig');
    }

    #[Route('/api/products/update', methods: ['PUT'])]
    public function updateProducts(Request $request, AccessTokenHandler $accessTokenHandler): Response
    {   
        $authorizationHeader = $request->headers->get('Authorization');

        $response = $accessTokenHandler->validateToken($authorizationHeader);

        if ($response !== null) {
            return $response;
        }

        $data = json_decode($request->getContent(), true);

        try {
            if (isset($data['products']) && is_array($data['products']) && $data['products'] !=[]) {
                foreach ($data['products'] as $productData) {

                    $product = $this->entityManager->getRepository(Product::class)->findOneBy(['sku' => $productData['sku']]);

                    if ($product) {
                        $product->setProductName($productData['product_name']);
                        $product->setDescription($productData['description']);

                        $updatedAt = new \DateTimeImmutable();
                        $product->setUpdateAt($updatedAt);

                        $this->entityManager->persist($product);
                    } else {
                        return new Response('The product with SKU was not found: ' . $productData['sku'], Response::HTTP_NOT_FOUND);
                    }
                }

                $this->entityManager->flush();

                return new Response('Products updated successfully.', Response::HTTP_OK);
            } else {
                return new Response('Incorrect payload or no products provided.', Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            return new Response('Error updating the products: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/products/list', name:'/products/list', methods: ['GET', 'POST'])]
    public function listProductsView(): Response
    {
        return $this->render('product/list_products.html.twig');
    }

    #[Route('/api/products/list', methods: ['GET'])]
    public function listProducts(Request $request, AccessTokenHandler $accessTokenHandler): JsonResponse
    {
        $authorizationHeader = $request->headers->get('Authorization');

        $response = $accessTokenHandler->validateToken($authorizationHeader);

        if ($response !== null) {
            return $response; 
        }

        $encodedToken = str_replace('Basic ', '', $authorizationHeader);
        $accessToken = base64_decode($encodedToken);

        try {

            $products = $this->entityManager->getRepository(Product::class)->findAll();

            $productsArray = [];
            foreach ($products as $product) {
                $productsArray[] = [
                    'id' => $product->getId(),
                    'sku' => $product->getSku(),
                    'product_name' => $product->getProductName(),
                    'description' => $product->getDescription(),
                    'created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $product->getUpdateAt()->format('Y-m-d H:i:s')
                ];
            }

            return new JsonResponse($productsArray, JsonResponse::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error retrieving the list of products: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
