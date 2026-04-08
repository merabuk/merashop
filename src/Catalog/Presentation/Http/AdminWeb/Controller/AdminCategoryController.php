<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminWeb\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminCategoryController extends AbstractController
{
    #[Route('/admin/catalog/categories', name: 'admin_catalog_categories')]
    public function index(): Response
    {
        return $this->render('base.html.twig', [
            'vue_component' => 'CategoryListView',
        ]);
    }
}
