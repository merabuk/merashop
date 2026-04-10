<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminWeb\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminAttributeController extends AbstractController
{
    #[Route('/admin/catalog/attributes', name: 'catalog.admin.web.v1.attributes.list')]
    public function index(): Response
    {
        return $this->render('admin_base.html.twig', [
            'vue_component' => 'AttributeListView',
        ]);
    }

    #[Route('/admin/catalog/attributes/create', name: 'catalog.admin.web.v1.attributes.create')]
    public function create(): Response
    {
        return $this->render('admin_base.html.twig', [
            'vue_component' => 'AttributeCreateView',
        ]);
    }
}
