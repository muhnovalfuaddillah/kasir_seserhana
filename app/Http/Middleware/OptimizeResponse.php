<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptimizeResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Add Cache-Control for static vendor/build assets
        if ($request->is('build/*') || $request->is('vendor/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        // Enable Gzip compression for text responses if supported
        if (
            function_exists('gzencode') &&
            !$response->headers->has('Content-Encoding')
        ) {
            $contentType = $response->headers->get('Content-Type', '');
            if (
                str_contains($contentType, 'text/html') ||
                str_contains($contentType, 'text/css') ||
                str_contains($contentType, 'javascript') ||
                str_contains($contentType, 'json')
            ) {
                $acceptEncoding = $request->header('Accept-Encoding', '');
                if (str_contains($acceptEncoding, 'gzip')) {
                    $content = $response->getContent();
                    if ($content && strlen($content) > 512) {
                        $compressed = gzencode($content, 6);
                        if ($compressed !== false) {
                            $response->setContent($compressed);
                            $response->headers->set('Content-Encoding', 'gzip');
                            $response->headers->set('Content-Length', (string) strlen($compressed));
                        }
                    }
                }
            }
        }

        return $response;
    }
}
