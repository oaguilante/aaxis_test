<?php declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;
use App\Entity\Product;
use App\Controller\ProductController;
use Symfony\Component\HttpFoundation\Request;
use App\Security\AccessTokenHandler;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductControllerTest extends WebTestCase
{
    public function testLoadProductsWhenSuccess(): void
    {
        $repository = $this->createMock(ProductRepository::class);
        $accessTokenHandler = $this->createMock(AccessTokenHandler::class);

        $authorizationHeader = 'Basic ' . base64_encode('AdminAaxis:AdminAaxxis2018');
        
        $accessTokenHandler->expects($this->once())
                    ->method('validateToken')
                    ->with($authorizationHeader)
                    ->willReturn(null);

        $product = new Product();
        $repository->method('find')->willReturn($product);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $controller = new ProductController($entityManager);

        $request = new Request([], [], [], [], [], ['HTTP_AUTHORIZATION' => $authorizationHeader], json_encode([
            'products' => [
                [
                    'sku' => 'SKU123',
                    'product_name' => 'Producto de prueba',
                    'description' => 'Esta es una descripción de prueba.',
                    'created_at' => '2023-12-27T00:00:00+00:00',
                    'update_at' => '2023-12-27T00:00:00+00:00',
                ],
            ],
        ]));

        $response = $controller->loadProducts($request, $accessTokenHandler);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testLoadProductsWhenProdcutIsEmpty(): void
    {
        $repository = $this->createMock(ProductRepository::class);
        $accessTokenHandler = $this->createMock(AccessTokenHandler::class);

        $authorizationHeader = 'Basic ' . base64_encode('AdminAaxis:AdminAaxxis2018');
        
        $accessTokenHandler->expects($this->once())
                    ->method('validateToken')
                    ->with($authorizationHeader)
                    ->willReturn(null);

        $repository->method('find')->willThrowException(new \Exception('Error al instanciar Product'));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $controller = new ProductController($entityManager);

        $request = new Request([], [], [], [], [], ['HTTP_AUTHORIZATION' => $authorizationHeader], json_encode([]));

        $response = $controller->loadProducts($request, $accessTokenHandler);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

    }

    public function testLoadProductsWhenTokenFails(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/products/load', [], [], [
            'HTTP_Authorization' => 'Basic ' . base64_encode('InvalidToken:InvalidPassword'),
            'CONTENT_TYPE'       => 'application/json',
        ]);

        $this->assertEquals(401, $client->getResponse()->getStatusCode());

    }

    public function testUpdateProductsWhenSuccess(): void
    {
        $repository = $this->createMock(ProductRepository::class);
        $accessTokenHandler = $this->createMock(AccessTokenHandler::class);

        $authorizationHeader = 'Basic ' . base64_encode('AdminAaxis:AdminAaxxis2018');
        
        $accessTokenHandler->expects($this->once())
                    ->method('validateToken')
                    ->with($authorizationHeader)
                    ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $product = new Product();
        $product->setSku('SKU123');
        $repository->method('findOneBy')->willReturn($product);

        $entityManager->method('getRepository')->willReturn($repository);

        $controller = new ProductController($entityManager);

        $request = new Request([], [], [], [], [], ['HTTP_AUTHORIZATION' => $authorizationHeader], json_encode([
            'products' => [
                [
                    'sku' => 'SKU123',
                    'product_name' => 'Producto actualizado',
                    'description' => 'Esta es una descripción actualizada.'
                ],
            ],
        ]));

        $response = $controller->updateProducts($request, $accessTokenHandler);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUpdateProductsWhenInvalidPayload(): void
    {
        $repository = $this->createMock(ProductRepository::class);
       $accessTokenHandler = $this->createMock(AccessTokenHandler::class);

        $authorizationHeader = 'Basic ' . base64_encode('AdminAaxis:AdminAaxxis2018');
        
        $accessTokenHandler->expects($this->once())
                    ->method('validateToken')
                    ->with($authorizationHeader)
                    ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $repository->expects($this->never())->method('findOneBy');
        $entityManager->method('getRepository')->willReturn($repository);

        $controller = new ProductController($entityManager);

        $updatedAt = new \DateTimeImmutable('2023-12-27 17:34:45.000');

        $request = new Request([], [], [], [], [], ['HTTP_AUTHORIZATION' => $authorizationHeader], json_encode([
            'wrong_key' => [
                [
                    'sku' => 'SKU123',
                    'product_name' => 'Producto incorrecto',
                    'description' => 'Esta es una descripción incorrecta.',
                    'update_at' => $updatedAt
                ],
            ],
        ]));

        $response = $controller->updateProducts($request, $accessTokenHandler);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testUpdateProductWhenTokenFails(): void
    {
        $client = static::createClient();

        $client->request('PUT', '/api/products/update', [], [], [
            'HTTP_Authorization' => 'Basic ' . base64_encode('InvalidToken:InvalidPassword'),
            'CONTENT_TYPE'       => 'application/json',
        ]);

        $this->assertEquals(401, $client->getResponse()->getStatusCode());

    }

    public function testListProducts(): void
    {
        $accessTokenHandler = $this->createMock(AccessTokenHandler::class);

        $authorizationHeader = 'Basic ' . base64_encode('AdminAaxis:AdminAaxxis2018');
        
        $accessTokenHandler->expects($this->once())
                    ->method('validateToken')
                    ->with($authorizationHeader)
                    ->willReturn(null);

        $createdAt = new \DateTimeImmutable('2023-12-27 17:34:45.000');
        $updatedAt = new \DateTimeImmutable('2023-12-27 17:34:45.000');

        $product1 = (new Product())
        ->setSku('SKU123')
        ->setProductName('Producto 1')
        ->setDescription('Descripción del producto 1')
        ->setCreatedAt($createdAt)
        ->setUpdateAt( $updatedAt);

        $product2 = (new Product())
        ->setSku('SKU456')
        ->setProductName('Producto 2')
        ->setDescription('Descripción del producto 2')
        ->setCreatedAt($createdAt)
        ->setUpdateAt( $updatedAt);
        
        $mockRepository = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $mockRepository->method('findAll')->willReturn([$product1, $product2]);

        $mockEntityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $mockEntityManager->method('getRepository')->willReturn($mockRepository);

        $controller = new ProductController($mockEntityManager);

        $request = new Request([], [], [], [], [], ['HTTP_AUTHORIZATION' => $authorizationHeader]);

        $response = $controller->listProducts($request, $accessTokenHandler);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        $this->assertCount(2, $responseData);

        $this->assertEquals('SKU123', $responseData[0]['sku']);
        $this->assertEquals('Producto 1', $responseData[0]['product_name']);

        $this->assertEquals('SKU456', $responseData[1]['sku']);
        $this->assertEquals('Producto 2', $responseData[1]['product_name']);
    }

    public function testListProductsWhenTokenFails(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/products/list', [], [], [
            'HTTP_Authorization' => 'Basic ' . base64_encode('InvalidToken:InvalidPassword'),
            'CONTENT_TYPE'       => 'application/json',
        ]);

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
    
}