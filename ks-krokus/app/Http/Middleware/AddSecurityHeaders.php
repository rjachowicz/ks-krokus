<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

final class AddSecurityHeaders
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();
        $response = $next($request);
        [$viteHttpSource, $viteWebSocketSource] = $this->viteDevelopmentSources();

        $contentSecurityPolicy = [
            "default-src 'self'",
            "base-uri 'self'",
            "connect-src 'self'{$viteHttpSource}{$viteWebSocketSource}",
            "font-src 'self' https://fonts.gstatic.com",
            "form-action 'self'",
            "frame-ancestors 'self'",
            'frame-src https://www.google.com',
            "img-src 'self' data: blob: https://images.unsplash.com",
            "object-src 'none'",
            "script-src 'self' 'nonce-{$nonce}'{$viteHttpSource}",
            "script-src-attr 'none'",
            "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com{$viteHttpSource}",
            "style-src-attr 'none'",
        ];

        if (app()->environment('production')) {
            $contentSecurityPolicy[] = 'upgrade-insecure-requests';
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
            );
        }

        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', $contentSecurityPolicy),
        );
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('Origin-Agent-Cluster', '?1');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), geolocation=(), microphone=()',
        );

        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        if ($this->containsSensitiveFormOrAccountData($request)) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        if ($request->is('panel', 'panel/*', 'logowanie', 'wniosek-o-konto', 'ustaw-haslo', 'ustaw-haslo/*')) {
            $response->headers->set(
                'X-Robots-Tag',
                'noindex, nofollow, noarchive',
            );
        }

        return $response;
    }

    /**
     * @return array{string, string}
     */
    private function viteDevelopmentSources(): array
    {
        if (! app()->environment('local', 'testing') || ! Vite::isRunningHot()) {
            return ['', ''];
        }

        $viteOrigin = rtrim(trim((string) file_get_contents(Vite::hotFile())), '/');
        $scheme = parse_url($viteOrigin, PHP_URL_SCHEME);

        if (
            ! in_array($scheme, ['http', 'https'], true)
            || filter_var($viteOrigin, FILTER_VALIDATE_URL) === false
        ) {
            return ['', ''];
        }

        $webSocketOrigin = preg_replace('/^http/', 'ws', $viteOrigin);

        return [
            ' '.$viteOrigin,
            is_string($webSocketOrigin) ? ' '.$webSocketOrigin : '',
        ];
    }

    private function containsSensitiveFormOrAccountData(Request $request): bool
    {
        return $request->is(
            'kontakt',
            'logowanie',
            'wniosek-o-konto',
            'ustaw-haslo',
            'ustaw-haslo/*',
            'wylogowanie',
            'panel',
            'panel/*',
        );
    }
}
