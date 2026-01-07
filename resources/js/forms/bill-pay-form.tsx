import { billPay } from '@/routes';
import { router, useForm } from '@inertiajs/react';
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '../components/ui/select';
import { BillInstance } from '../types';

interface BillPayFormProps {
  pendingBill: BillInstance;
  accounts: { id: number; name: string; balance: string }[];
}

export default function BillPayForm({
  pendingBill,
  accounts,
}: BillPayFormProps) {
  const { data, setData, post, processing, errors } = useForm({
    account_id: '',
    bill_instance_id: pendingBill.id,
    amount: pendingBill.amount,
    paid_date: new Date().toISOString().split('T')[0],
    notes: 'Paying bill for ' + pendingBill.bill?.biller?.name,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(billPay.url(), {
      onSuccess: () => {
        router.reload();
      },
    });
  };
  return (
    <>
      <form onSubmit={handleSubmit}>
        <FieldSet>
          <FieldGroup>
            {/* Account Selection */}
            <Field>
              <FieldLabel htmlFor="account_id">Account</FieldLabel>
              <FieldContent>
                <Select
                  value={data.account_id}
                  onValueChange={(value) => setData('account_id', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select an account" />
                  </SelectTrigger>
                  <SelectContent>
                    {accounts.map((account) => (
                      <SelectItem
                        key={account.id}
                        value={account.id.toString()}
                      >
                        {account.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                {data.account_id && (
                  <FieldDescription>
                    {
                      accounts.find(
                        (account) => account.id.toString() === data.account_id,
                      )?.balance
                    }
                  </FieldDescription>
                )}
                <FieldError
                  errors={
                    errors.account_id
                      ? [
                          {
                            message: errors.account_id,
                          },
                        ]
                      : undefined
                  }
                />
              </FieldContent>
            </Field>

            {/* Amount */}
            <Field>
              <FieldLabel htmlFor="amount">Amount</FieldLabel>
              <FieldContent>
                <Input
                  id="amount"
                  type="number"
                  step="0.01"
                  min="0"
                  value={data.amount}
                  onChange={(e) =>
                    setData('amount', parseFloat(e.target.value))
                  }
                  placeholder="Enter amount"
                />
                <FieldError
                  errors={
                    errors.amount ? [{ message: errors.amount }] : undefined
                  }
                />
              </FieldContent>
            </Field>

            {/* Date */}
            <Field>
              <FieldLabel htmlFor="date">Date</FieldLabel>
              <FieldContent>
                <Input
                  id="date"
                  type="date"
                  value={data.paid_date}
                  onChange={(e) => setData('paid_date', e.target.value)}
                />
                <FieldError
                  errors={
                    errors.paid_date
                      ? [{ message: errors.paid_date }]
                      : undefined
                  }
                />
              </FieldContent>
            </Field>

            {/* Notes */}
            <Field>
              <FieldLabel htmlFor="notes">Notes</FieldLabel>
              <FieldContent>
                <Input
                  id="notes"
                  type="text"
                  value={data.notes}
                  onChange={(e) => setData('notes', e.target.value)}
                  placeholder="Enter notes"
                />
                <FieldError
                  errors={
                    errors.notes
                      ? [
                          {
                            message: errors.notes,
                          },
                        ]
                      : undefined
                  }
                />
              </FieldContent>
            </Field>
          </FieldGroup>

          {/* Submit Button */}
          <div className="flex justify-between gap-2">
            <Button type="submit" disabled={processing}>
              {processing ? 'Paying...' : 'Pay Bill'}
            </Button>
          </div>
        </FieldSet>
      </form>
    </>
  );
}
