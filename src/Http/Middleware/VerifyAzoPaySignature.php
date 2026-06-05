<?php

namespace Ftech\AzoPay\Http\Middleware;

use Closure;
use Ftech\AzoPay\Exceptions\InvalidSignatureException;
use Ftech\AzoPay\Webhook\WebhookSignature;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects webhook requests whose X-AzoPay-Signature header is missing or
 * invalid. Apply to any custom webhook route you register manually.
 */
class VerifyAzoPaySignature
{
    public function __construct(
        protected WebhookSignature $signature,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->signature->verify(
                $request->getContent(),
                $request->header('X-AzoPay-Signature'),
            );
        } catch (InvalidSignatureException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return $next($request);
    }
}
