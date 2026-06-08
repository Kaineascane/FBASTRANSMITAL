/** Public origin for redirects (dev tunnels, reverse proxies, local dev). */
export function getRequestBaseUrl(request: Request): string {
  const forwardedHost = request.headers.get('x-forwarded-host');
  const hostHeader = request.headers.get('host');
  const forwardedProto = request.headers.get('x-forwarded-proto');

  let host = forwardedHost ?? hostHeader;
  let proto = forwardedProto;

  if (!host || !proto) {
    const url = new URL(request.url);
    host = host ?? url.host;
    proto = proto ?? url.protocol.replace(':', '');
  }

  // Dev tunnels expose HTTPS on 443; request.url may still carry :3000.
  if (proto === 'https' && host.endsWith(':3000') && host.includes('devtunnels.ms')) {
    host = host.slice(0, -5);
  }

  return `${proto}://${host}/`;
}
