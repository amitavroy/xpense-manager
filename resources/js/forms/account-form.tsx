import { router, useForm } from '@inertiajs/react';
import { ConfirmDialog } from '../components/confirm-dialog';
import { Button } from '../components/ui/button';
import { Card, CardContent } from '../components/ui/card';
import {
  Field,
  FieldContent,
  FieldDescription,
  FieldError,
  FieldGroup,
  FieldLabel,
  FieldLegend,
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
import { destroy, store, update } from '../routes/accounts';
import { Account, AccountType } from '../types';

const accountTypeOptions: { value: AccountType; label: string }[] = [
  { value: 'bank', label: 'Bank Account' },
  { value: 'cash', label: 'Cash' },
  { value: 'credit_card', label: 'Credit Card' },
];

export default function AccountForm({ account }: { account: Account }) {
  const isEdit = account.id !== undefined;
  const { data, setData, post, put, processing, errors, reset } = useForm({
    name: account.name ? account.name : '',
    type: account.type ? account.type : '',
    balance: account.balance ? account.balance : '',
  });
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const url = isEdit ? update(account.id).url : store().url;
    if (isEdit) {
      put(url);
    } else {
      post(url, {
        onSuccess: () => {
          reset();
        },
      });
    }
  };

  const handleDelete = () => router.delete(destroy(account.id).url);
  return (
    <Card>
      <CardContent>
        <form onSubmit={handleSubmit}>
          <FieldSet>
            <FieldLegend>Add a new Account</FieldLegend>
            <FieldDescription>Add details about a new account</FieldDescription>
            <FieldGroup>
              {/* Account name */}
              <Field>
                <FieldLabel htmlFor="name">Account name</FieldLabel>
                <FieldContent>
                  <Input
                    id="name"
                    type="text"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="Enter the account name"
                  />
                  <FieldError
                    errors={
                      errors.name
                        ? [
                            {
                              message: errors.name,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Account type */}
              <Field>
                <FieldLabel htmlFor="type">Account type</FieldLabel>
                <FieldContent>
                  <Select
                    value={data.type}
                    onValueChange={(value) => setData('type', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select account type" />
                    </SelectTrigger>
                    <SelectContent>
                      {accountTypeOptions.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                          {option.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FieldError
                    errors={
                      errors.type
                        ? [
                            {
                              message: errors.type,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Account balance */}
              <Field>
                <FieldLabel htmlFor="balance">Balance</FieldLabel>
                <FieldContent>
                  <Input
                    id="balance"
                    type="number"
                    step="0.01"
                    min="0"
                    value={data.balance}
                    onChange={(e) => setData('balance', e.target.value)}
                    placeholder="Enter balance"
                  />
                  <FieldError
                    errors={
                      errors.balance ? [{ message: errors.balance }] : undefined
                    }
                  />
                </FieldContent>
              </Field>
            </FieldGroup>

            {/* Submit Button */}
            <div className="flex justify-between gap-2">
              <ConfirmDialog
                title="Delete Account"
                description="Are you sure you want to delete this account?"
                confirmButtonText="Delete"
                trigger={
                  <Button variant="destructive" type="button">
                    Delete
                  </Button>
                }
                onConfirm={handleDelete}
              />
              <Button type="submit" disabled={processing}>
                {processing
                  ? 'Saving...'
                  : isEdit
                    ? 'Save Transaction'
                    : 'Add Transaction'}
              </Button>
            </div>
          </FieldSet>
        </form>
      </CardContent>
    </Card>
  );
}
