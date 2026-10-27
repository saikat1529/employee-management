<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class DocsController extends Controller
{
    /**
     * The only documents that can be served. Anything else is a 404.
     *
     * @var array<string, string>
     */
    private const DOCUMENTS = [
        'website-guide' => 'Demo Portal — Website Guide',
        'quotation-template' => 'Quotation — Demo Portal',
    ];

    private const SESSION_KEY = 'docs_authenticated';

    public function showLogin(Request $request): Response|RedirectResponse
    {
        if (! $this->isConfigured()) {
            return $this->unavailable();
        }

        if ($this->isAuthenticated($request)) {
            return redirect()->route('docs.index');
        }

        return response()->view('docs.login');
    }

    public function login(Request $request): Response|RedirectResponse
    {
        if (! $this->isConfigured()) {
            return $this->unavailable();
        }

        $given = (string) $request->input('password', '');

        if (! hash_equals($this->configuredPassword(), $given)) {
            return redirect()
                ->route('docs.login')
                ->withErrors(['password' => 'The password is not correct.']);
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, true);

        return redirect()->route('docs.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('docs.login');
    }

    public function index(Request $request): Response|RedirectResponse
    {
        if (! $this->isConfigured()) {
            return $this->unavailable();
        }

        if (! $this->isAuthenticated($request)) {
            return redirect()->route('docs.login');
        }

        return response()->view('docs.index', [
            'documents' => self::DOCUMENTS,
        ]);
    }

    public function show(Request $request, string $name): Response|RedirectResponse
    {
        if (! array_key_exists($name, self::DOCUMENTS)) {
            abort(404);
        }

        if (! $this->isConfigured()) {
            return $this->unavailable();
        }

        if (! $this->isAuthenticated($request)) {
            return redirect()->route('docs.login');
        }

        $path = resource_path('docs/'.$name.'.html');

        abort_unless(is_file($path), 404);

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    private function configuredPassword(): string
    {
        return (string) config('docs.password', '');
    }

    private function isConfigured(): bool
    {
        return $this->configuredPassword() !== '';
    }

    private function isAuthenticated(Request $request): bool
    {
        return $request->session()->get(self::SESSION_KEY, false) === true;
    }

    private function unavailable(): Response
    {
        return response()->view('docs.login', ['configured' => false], 503);
    }
}
