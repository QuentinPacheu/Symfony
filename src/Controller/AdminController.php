<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Notification;
use App\Form\ProductType;
use App\Message\AddPointsMessage;
use App\Message\NotificationMessage;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/product', name: 'app_admin_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('admin/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/product/new', name: 'app_admin_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MessageBusInterface $bus): Response
    {
        $product = new Product();
        $product->setCreateBy($this->getUser());
        
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            // Envoyer une notification aux admins
            $bus->dispatch(new NotificationMessage(
                'CREATION',
                $product->getName(),
                $product->getId(),
                $this->getUser()->getId()
            ));

            $this->addFlash('success', 'Produit créé avec succès.');

            return $this->redirectToRoute('app_admin_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/product/{id}/edit', name: 'app_admin_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, MessageBusInterface $bus): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour la date de modification (optionnel car fait automatiquement par PreUpdate)
            $product->setUpdatedAt(new \DateTime());
            
            $entityManager->flush();

            // Envoyer une notification aux admins
            $bus->dispatch(new NotificationMessage(
                'MODIFICATION',
                $product->getName(),
                $product->getId(),
                $this->getUser()->getId()
            ));

            $this->addFlash('success', 'Produit modifié avec succès.');

            return $this->redirectToRoute('app_admin_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/product/{id}', name: 'app_admin_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager, MessageBusInterface $bus): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productName = $product->getName(); // Sauvegarder le nom avant suppression
            $productId = $product->getId(); // Sauvegarder l'ID avant suppression
            
            $entityManager->remove($product);
            $entityManager->flush();

            // Envoyer une notification aux admins
            $bus->dispatch(new NotificationMessage(
                'SUPPRESSION',
                $productName,
                $productId,
                $this->getUser()->getId()
            ));

            $this->addFlash('success', 'Produit supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/users', name: 'app_admin_users', methods: ['GET'])]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/user/{id}/toggle', name: 'app_admin_user_toggle', methods: ['POST'])]
    public function toggleUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$user->getId(), $request->request->get('_token'))) {
            // Sauvegarder l'état actuel avant modification
            $wasActive = $user->isActif();
            
            // Changer le statut
            $user->setActif(!$user->isActif());
            
            // Créer une notification pour l'utilisateur
            $notification = new Notification();
            if ($user->isActif()) {
                // L'utilisateur vient d'être activé
                $notification->setLabel('Votre compte a été réactivé par un administrateur');
            } else {
                // L'utilisateur vient d'être désactivé
                $notification->setLabel('Votre compte a été désactivé par un administrateur');
            }
            $notification->setUser($user);
            $notification->setCreatedAt(new \DateTime());
            
            // Persister les changements
            $entityManager->persist($notification);
            $entityManager->flush();
            
            $status = $user->isActif() ? 'activé' : 'désactivé';
            $this->addFlash('success', "L'utilisateur a été $status avec succès.");
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/add-points', name: 'app_admin_add_points', methods: ['POST'])]
    public function addPoints(Request $request, MessageBusInterface $bus): Response
    {
        if ($this->isCsrfTokenValid('add_points', $request->request->get('_token'))) {
            $bus->dispatch(new AddPointsMessage());
            $this->addFlash('success', 'Les points seront ajoutés aux utilisateurs actifs.');
        }

        return $this->redirectToRoute('app_admin_users');
    }
}