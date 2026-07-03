<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Rejects direct browser navigation to JSON/fragment endpoints.
 *
 * Passes only requests that look like same-page AJAX calls:
 *  - X-Requested-With: XMLHttpRequest (set automatically by jQuery for
 *    same-origin $.ajax), AND
 *  - not a top-level document load (Sec-Fetch-Dest, when the browser sends it).
 *
 * NOTE: headers can be forged with curl — this stops link-sharing and casual
 * browsing, not a determined attacker. Real protection requires auth.
 */
class AjaxOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Modern browsers mark address-bar / link navigation as "document"
        $fetchDest = $request->getHeaderLine('Sec-Fetch-Dest');

        if ($request->isAJAX() && $fetchDest !== 'document') {
            return; // legitimate AJAX — continue to controller
        }

        return service('response')
            ->setStatusCode(403)
            ->setJSON([
                'status'  => 'error',
                'message' => 'Forbidden.',
            ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
