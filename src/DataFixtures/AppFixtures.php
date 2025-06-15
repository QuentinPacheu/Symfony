<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Créer un admin
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setName('Admin');
        $admin->setFirstname('Super');
        $admin->setPoints(10000);
        $admin->setActif(true);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password123'));
        $manager->persist($admin);

        // Créer un utilisateur normal
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setName('User');
        $user->setFirstname('Normal');
        $user->setPoints(500);
        $user->setActif(true);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $manager->persist($user);

        // Créer quelques produists
        $categories = ['Électronique', 'Livres', 'Vêtements', 'Alimentation'];
        
        for ($i = 1; $i <= 10; $i++) {
            $product = new Product();
            $product->setName('Produit ' . $i);
            $product->setPrice(random_int(50, 500));
            $product->setCategory($categories[array_rand($categories)]);
            $product->setDescription('Description du produit ' . $i);
            $product->setCreateBy($admin);
            $manager->persist($product);
        }

        $manager->flush();
    }
}