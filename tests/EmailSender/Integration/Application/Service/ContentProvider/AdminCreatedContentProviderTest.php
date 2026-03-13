<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Integration\Application\Service\ContentProvider;

use App\EmailSender\Application\Service\ContentProvider\AdminCreatedContentProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;
use Twig\Environment;

final class AdminCreatedContentProviderTest extends KernelTestCase
{
    private Environment $twig;
    private AdminCreatedContentProvider $provider;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->twig = self::getContainer()->get('twig');
        $this->provider = self::getContainer()->get(AdminCreatedContentProvider::class);
    }

    public function testItTranslationsAndTemplatesExist(): void
    {
        $translationKey = 'admin_email.admin_created.subject';
        $params = [
            'appName' => 'MeraShop',
            'adminName' => 'Andrii',
            'temporaryPassword' => 'secret123',
        ];

        $templateName = $this->provider->getTemplate();
        self::assertTrue($this->twig->getLoader()->exists($templateName), "Template {$templateName} not found");

        try {
            $this->twig->load($templateName);
        } catch (Throwable $e) {
            self::fail("Twig template {$templateName} has syntax errors: ".$e->getMessage());
        }

        $subject = $this->provider->getSubject($params);

        self::assertNotSame($translationKey, $subject, "Translation key '{$translationKey}' is missing");

        self::assertStringNotContainsString('{appName}', $subject);
        self::assertStringContainsString($params['appName'], $subject);
    }
}
