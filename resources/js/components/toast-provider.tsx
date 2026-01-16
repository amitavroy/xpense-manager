import { useEffect, useRef, useState } from 'react';

import { Toast, ToastComponent, type ToastType } from '@/components/ui/toast';

interface FlashToast {
  type?: ToastType;
  message: string;
  title?: string;
  duration?: number;
}

export function ToastProvider() {
  const [toasts, setToasts] = useState<Toast[]>([]);
  const processedMessages = useRef<Set<string>>(new Set());

  const addToast = (toast: Omit<Toast, 'id'>) => {
    // Create a unique key for this toast to prevent duplicates
    const toastKey = `${toast.type || 'default'}-${toast.message}-${toast.title || ''}`;

    // Skip if we've already processed this exact toast
    if (processedMessages.current.has(toastKey)) {
      return;
    }

    processedMessages.current.add(toastKey);
    const id = crypto.randomUUID();
    setToasts((prev) => [...prev, { ...toast, id }]);

    // Clean up the key after the toast duration (or default 5s)
    setTimeout(() => {
      processedMessages.current.delete(toastKey);
    }, toast.duration || 5000);
  };

  const removeToast = (id: string) => {
    setToasts((prev) => prev.filter((toast) => toast.id !== id));
  };

  // Listen to flash events
  // Note: We only listen to native browser events because Inertia automatically
  // dispatches 'inertia:flash' events when flash data is received, and this
  // also catches events dispatched by FlashHandler for initial page load.
  useEffect(() => {
    const handleFlash = (event: CustomEvent) => {
      const flash = event.detail.flash as { notification?: FlashToast };

      if (flash.notification) {
        const {
          type = 'info',
          message,
          title,
          duration = 5000,
        } = flash.notification;
        addToast({ type, message, title, duration });
      }
    };

    document.addEventListener('inertia:flash', handleFlash as EventListener);

    return () => {
      document.removeEventListener(
        'inertia:flash',
        handleFlash as EventListener,
      );
    };
  }, []);

  if (toasts.length === 0) {
    return null;
  }

  return (
    <div className="fixed top-4 right-4 z-50 flex w-full max-w-md flex-col gap-2">
      {toasts.map((toast) => (
        <ToastComponent key={toast.id} toast={toast} onClose={removeToast} />
      ))}
    </div>
  );
}
