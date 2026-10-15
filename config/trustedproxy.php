<?php

/*
|--------------------------------------------------------------------------
| Trusted proxies
|--------------------------------------------------------------------------
|
| Read by Laravel's built-in TrustProxies middleware on every request.
|
| CloudPanel puts an nginx in front of the nginx that talks to PHP, so PHP
| sees every request as coming from 127.0.0.1. Without trusting that hop,
| $request->ip() is the same for every visitor, which means:
|   - every guest shares ONE rate-limit bucket (login, register, contact
|     reveal, offer comments are throttled for the whole site at once);
|   - unique-visitor analytics collapse to a single "visitor";
|   - $request->isSecure() is false, so HSTS is never sent.
|
| Trusting loopback only is safe on any setup: nothing outside the machine
| can connect from 127.0.0.1, so X-Forwarded-* cannot be forged by visitors.
|
| Behind Cloudflare or a load balancer, list its addresses here as a
| comma-separated TRUSTED_PROXIES value (or have nginx restore the real IP
| with set_real_ip_from / real_ip_header). Avoid "*" unless every request
| is guaranteed to pass through a proxy you control.
|
*/

return [
    'proxies' => env('TRUSTED_PROXIES', '127.0.0.1,::1'),
];
