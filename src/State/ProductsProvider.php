<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ProductRepository;
use Symfony\Bundle\SecurityBundle\Security;

final class ProductsProvider implements ProviderInterface
{
    public function __construct(
        private ProductRepository $productRepository,
        private Security $security
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();
        
        if (!$user) {
            return [];
        }

        return $this->productRepository->findBy(['createBy' => $user]);
    }
}