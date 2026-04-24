<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// TODO: Remove this controller when the frontend is ready
class TestFrontendController extends AbstractController
{
    #[Route('/test-vue', name: 'shared.web.v1.test-vue')]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/admin/dashboard', name: 'shared.web.v1.admin-dashboard')]
    public function dashboard(): Response
    {
        // Ideally, there should be a token check here, but for testing purposes:
        return $this->render('admin_base.html.twig', [
            'vue_component' => 'AdminLayout',
        ]);
    }
}
