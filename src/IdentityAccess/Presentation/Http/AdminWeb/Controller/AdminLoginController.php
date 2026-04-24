<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\AdminWeb\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminLoginController extends AbstractController
{
    #[Route('/admin/login', name: 'identity_access.admin.web.v1.login')]
    public function index(): Response
    {
        return $this->render('admin_base.html.twig', [
            'vue_component' => 'LoginView',
        ]);
    }
}
