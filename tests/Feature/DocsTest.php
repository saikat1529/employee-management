<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocsTest extends TestCase
{
    private const PASSWORD = 'test-docs-password-123';

    protected function setUp(): void
    {
        parent::setUp();

        config(['docs.password' => self::PASSWORD]);
    }

    public function test_docs_login_page_loads(): void
    {
        $this->get('/docs')->assertOk()->assertSee('Enter the document password');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $response = $this->post('/docs/login', ['password' => 'wrong-password']);

        $response->assertRedirect('/docs');
        $response->assertSessionHasErrors('password');
        $this->get('/docs/index')->assertRedirect('/docs');
    }

    public function test_empty_password_is_rejected(): void
    {
        $this->post('/docs/login', ['password' => ''])->assertSessionHasErrors('password');
        $this->get('/docs/index')->assertRedirect('/docs');
    }

    public function test_correct_password_is_allowed(): void
    {
        $this->post('/docs/login', ['password' => self::PASSWORD])
            ->assertRedirect('/docs/index');

        $this->get('/docs/index')
            ->assertOk()
            ->assertSee('website-guide')
            ->assertSee('quotation-template');
    }

    public function test_document_is_not_served_without_login(): void
    {
        $this->get('/docs/website-guide')->assertRedirect('/docs');
        $this->get('/docs/quotation-template')->assertRedirect('/docs');
    }

    public function test_document_is_served_after_login(): void
    {
        $this->post('/docs/login', ['password' => self::PASSWORD]);

        $this->get('/docs/website-guide')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertSee('Demo Portal — Website Guide', false);

        $this->get('/docs/quotation-template')
            ->assertOk()
            ->assertSee('Quotation — Demo Portal', false);
    }

    public function test_unknown_document_name_gives_404(): void
    {
        $this->get('/docs/unknown-doc')->assertNotFound();

        $this->post('/docs/login', ['password' => self::PASSWORD]);

        $this->get('/docs/unknown-doc')->assertNotFound();
        $this->get('/docs/..%2F.env')->assertNotFound();
    }

    public function test_logout_clears_access(): void
    {
        $this->post('/docs/login', ['password' => self::PASSWORD]);
        $this->get('/docs/website-guide')->assertOk();

        $this->post('/docs/logout')->assertRedirect('/docs');

        $this->get('/docs/website-guide')->assertRedirect('/docs');
    }

    public function test_documents_are_refused_when_password_is_not_configured(): void
    {
        config(['docs.password' => '']);

        $this->get('/docs')
            ->assertStatus(503)
            ->assertSee('Documents are not configured');

        $this->post('/docs/login', ['password' => ''])
            ->assertStatus(503)
            ->assertSee('Documents are not configured');

        $this->get('/docs/website-guide')->assertStatus(503);
    }

    public function test_document_files_are_not_reachable_as_public_files(): void
    {
        $this->assertFileDoesNotExist(public_path('docs/website-guide.html'));
        $this->assertFileDoesNotExist(public_path('docs/quotation-template.html'));

        $this->get('/docs/website-guide.html')->assertNotFound();
        $this->get('/docs/quotation-template.html')->assertNotFound();

        $this->post('/docs/login', ['password' => self::PASSWORD]);

        $this->get('/docs/website-guide.html')->assertNotFound();
        $this->get('/docs/quotation-template.html')->assertNotFound();
        $this->get('/public/docs/website-guide.html')->assertNotFound();
    }
}
