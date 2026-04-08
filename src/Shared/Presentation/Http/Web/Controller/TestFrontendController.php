<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestFrontendController extends AbstractController
{
    #[Route('/test-vue', name: 'app_test_vue')]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/admin/login', name: 'admin_login')]
    public function login(): Response
    {
        return $this->render('base.html.twig', [
            'vue_component' => 'LoginView',
        ]);
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        // Тут в ідеалі має бути перевірка токена, але для тесту:
        return $this->render('base.html.twig', [
            'vue_component' => 'AdminLayout',
        ]);
    }
}
