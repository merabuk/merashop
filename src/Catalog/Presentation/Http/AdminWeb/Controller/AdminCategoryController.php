<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminWeb\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminCategoryController extends AbstractController
{
    #[Route('/admin/catalog/categories', name: 'catalog.admin.web.v1.categories.list')]
    public function index(): Response
    {
        return $this->render('admin_base.html.twig', [
            'vue_component' => 'CategoryListView',
        ]);
    }

    #[Route(path: '/admin/catalog/categories/create', name: 'catalog.admin.web.v1.categories.create')]
    public function create(): Response
    {
        return $this->render('admin_base.html.twig', [
            'vue_component' => 'CategoryCreateView',
        ]);
    }
}
