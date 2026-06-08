export type FlashData = {
  errors: string[];
  old: Record<string, string | number>;
};

const emptyFlash = (): FlashData => ({ errors: [], old: {} });

/** Encode validation errors into a redirect URL (works in Route Handlers & dev tunnels). */
export function flashRedirectUrl(
  path: string,
  errors: string[],
  old: Record<string, string | number>
): string {
  const payload = Buffer.from(JSON.stringify({ errors, old }), 'utf8').toString('base64url');
  return `${path}?flash=${payload}`;
}

/** Read flash from URL search param (safe in Server Components — no cookie writes). */
export function decodeFlashParam(flashParam: string | undefined): FlashData {
  if (!flashParam) return emptyFlash();

  try {
    const json = Buffer.from(flashParam, 'base64url').toString('utf8');
    const parsed = JSON.parse(json) as Partial<FlashData>;
    return {
      errors: Array.isArray(parsed.errors) ? parsed.errors.map(String) : [],
      old: parsed.old && typeof parsed.old === 'object' ? parsed.old : {},
    };
  } catch {
    return emptyFlash();
  }
}
