import { useForm } from '@inertiajs/react';
import { Button } from '../components/ui/button';
import {
  Field,
  FieldContent,
  FieldDescription,
  FieldError,
  FieldGroup,
  FieldLabel,
  FieldSet,
} from '../components/ui/field';
import { Input } from '../components/ui/input';
import { formatCurrency } from '../lib/utils';
import { Account } from '../types';

interface ReconcileBalanceFormProps {
  account: Account;
  onSuccess?: () => void;
}

function getReconcileUrl(accountId: number): string {
  return `/account/${accountId}/reconcile`;
}

function getDifferenceMessage(
  actual: number,
  tracked: number,
): { message: string; variant: 'income' | 'expense' | 'match' } {
  const diff = actual - tracked;
  if (diff === 0) {
    return {
      message: 'Balance matches — no adjustment needed',
      variant: 'match',
    };
  }
  if (diff > 0) {
    return {
      message: `Difference: +${formatCurrency(diff)} (will add income)`,
      variant: 'income',
    };
  }
  return {
    message: `Difference: ${formatCurrency(diff)} (will add expense)`,
    variant: 'expense',
  };
}

export default function ReconcileBalanceForm({
  account,
  onSuccess,
}: ReconcileBalanceFormProps) {
  const trackedBalance = Number(account.balance);
  const { data, setData, post, processing, errors } = useForm({
    actual_balance: trackedBalance > 0 ? String(trackedBalance) : '',
  });

  const actualNum = parseFloat(data.actual_balance);
  const isValidActual = !Number.isNaN(actualNum) && actualNum >= 0;
  const differenceInfo =
    isValidActual && data.actual_balance !== ''
      ? getDifferenceMessage(actualNum, trackedBalance)
      : null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(getReconcileUrl(account.id), {
      onSuccess: () => {
        onSuccess?.();
      },
    });
  };

  return (
    <form onSubmit={handleSubmit}>
      <FieldSet>
        <FieldGroup>
          <Field>
            <FieldLabel>Current tracked balance</FieldLabel>
            <FieldContent>
              <p className="text-sm text-muted-foreground">
                {formatCurrency(trackedBalance)}
              </p>
            </FieldContent>
          </Field>

          <Field>
            <FieldLabel htmlFor="actual_balance">
              Actual bank balance
            </FieldLabel>
            <FieldContent>
              <Input
                id="actual_balance"
                type="number"
                step="0.01"
                min="0"
                value={data.actual_balance}
                onChange={(e) => setData('actual_balance', e.target.value)}
                placeholder="Enter actual balance"
              />
              <FieldError
                errors={
                  errors.actual_balance
                    ? [{ message: errors.actual_balance }]
                    : undefined
                }
              />
            </FieldContent>
          </Field>

          {differenceInfo && (
            <Field>
              <FieldDescription>
                <span
                  className={
                    differenceInfo.variant === 'income'
                      ? 'text-green-600 dark:text-green-400'
                      : differenceInfo.variant === 'expense'
                        ? 'text-red-600 dark:text-red-400'
                        : 'text-muted-foreground'
                  }
                >
                  {differenceInfo.message}
                </span>
              </FieldDescription>
            </Field>
          )}
        </FieldGroup>

        <div className="flex justify-end gap-2">
          <Button type="submit" disabled={processing}>
            {processing ? 'Reconciling...' : 'Reconcile'}
          </Button>
        </div>
      </FieldSet>
    </form>
  );
}
