import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

/**
 * FlashHandler dispatches the initial flash event on page load
 * so that toast notifications work on the first page visit.
 * This component should be rendered inside an Inertia page component.
 *
 * Note: This only dispatches once on mount. Subsequent flash events
 * are handled automatically by Inertia.
 */
export function FlashHandler() {
  const { flash } = usePage<{ flash?: { notification?: unknown } }>();
  const hasDispatched = useRef(false);

  useEffect(() => {
    // Only dispatch once on initial mount if flash exists
    // Inertia will handle subsequent flash events automatically
    if (flash && !hasDispatched.current) {
      hasDispatched.current = true;
      // Dispatch the flash event for initial page load
      const event = new CustomEvent('inertia:flash', {
        detail: { flash },
      });
      document.dispatchEvent(event);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []); // Only run on mount - flash is intentionally excluded to prevent re-dispatching

  return null;
}
