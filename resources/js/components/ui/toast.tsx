import * as React from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { X, CheckCircle2, AlertCircle, Info, AlertTriangle } from 'lucide-react';

import { cn } from '@/lib/utils';

const toastVariants = cva(
  'group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-6 pr-8 shadow-lg transition-all',
  {
    variants: {
      variant: {
        default: 'border bg-background text-foreground',
        success:
          'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-950 text-green-900 dark:text-green-100',
        destructive:
          'destructive group border-destructive bg-destructive text-destructive-foreground',
        info: 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950 text-blue-900 dark:text-blue-100',
        warning:
          'border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-950 text-yellow-900 dark:text-yellow-100',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export type ToastType = 'default' | 'success' | 'destructive' | 'info' | 'warning';

export interface Toast {
  id: string;
  type?: ToastType;
  message: string;
  title?: string;
  duration?: number;
}

interface ToastComponentProps {
  toast: Toast;
  onClose: (id: string) => void;
}

const iconMap: Record<ToastType, React.ComponentType<{ className?: string }>> = {
  default: Info,
  success: CheckCircle2,
  destructive: AlertCircle,
  info: Info,
  warning: AlertTriangle,
};

export function ToastComponent({ toast, onClose }: ToastComponentProps) {
  const { id, type = 'default', message, title } = toast;
  const Icon = iconMap[type];

  React.useEffect(() => {
    if (toast.duration && toast.duration > 0) {
      const timer = setTimeout(() => {
        onClose(id);
      }, toast.duration);

      return () => clearTimeout(timer);
    }
  }, [id, toast.duration, onClose]);

  return (
    <div
      className={cn(toastVariants({ variant: type }))}
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
    >
      <div className="flex items-start gap-3">
        <Icon className="h-5 w-5 shrink-0" />
        <div className="flex-1 space-y-1">
          {title && (
            <div className="text-sm font-semibold leading-none tracking-tight">
              {title}
            </div>
          )}
          <div className="text-sm leading-relaxed opacity-90">{message}</div>
        </div>
      </div>
      <button
        className="absolute right-2 top-2 rounded-md p-1 text-foreground/50 opacity-0 transition-opacity hover:text-foreground focus:opacity-100 focus:outline-none focus:ring-2 group-hover:opacity-100"
        onClick={() => onClose(id)}
        aria-label="Close"
      >
        <X className="h-4 w-4" />
      </button>
    </div>
  );
}
