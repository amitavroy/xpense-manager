import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { Button } from '../components/ui/button';
import { Checkbox } from '../components/ui/checkbox';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '../components/ui/dialog';
import {
  Field,
  FieldContent,
  FieldError,
  FieldGroup,
  FieldLabel,
  FieldSet,
} from '../components/ui/field';
import { Input } from '../components/ui/input';
import { store, update } from '../routes/trips/expenses';
import { Trip, TripExpense, UserDropdown } from '../types';

interface ExpenseFormProps {
  trip: Trip;
  expense?: TripExpense;
  tripMembers: UserDropdown[];
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

export default function ExpenseForm({
  trip,
  expense,
  tripMembers,
  open,
  onOpenChange,
}: ExpenseFormProps) {
  const isEdit = expense?.id !== undefined;
  const { data, setData, post, put, processing, errors, reset } = useForm({
    trip_id: trip.id,
    date: expense?.date
      ? new Date(expense.date).toISOString().split('T')[0]
      : '',
    amount: expense?.amount || '',
    description: expense?.description || '',
    is_shared: expense?.is_shared ?? true,
    shared_with: expense?.shared_with?.map((u) => u.id) || [],
  });

  // Update form data when expense prop changes
  useEffect(() => {
    if (expense) {
      setData({
        trip_id: trip.id,
        date: expense.date
          ? new Date(expense.date).toISOString().split('T')[0]
          : '',
        amount: expense.amount || '',
        description: expense.description || '',
        is_shared: expense.is_shared ?? true,
        shared_with: expense.shared_with?.map((u) => u.id) || [],
      });
    } else {
      // Reset form when creating new expense
      reset();
      setData('trip_id', trip.id);
    }
  }, [expense, trip.id, setData, reset]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const url = isEdit
      ? update({ trip: trip.id, tripExpense: expense.id }).url
      : store({ trip: trip.id }).url;
    if (isEdit) {
      put(url, {
        onSuccess: () => {
          reset();
          onOpenChange(false);
        },
      });
    } else {
      post(url, {
        onSuccess: () => {
          reset();
          onOpenChange(false);
        },
      });
    }
  };

  const toggleUser = (userId: number) => {
    const currentIds = data.shared_with as number[];
    if (currentIds.includes(userId)) {
      setData(
        'shared_with',
        currentIds.filter((id) => id !== userId),
      );
    } else {
      setData('shared_with', [...currentIds, userId]);
    }
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-2xl">
        <DialogHeader>
          <DialogTitle>{isEdit ? 'Edit Expense' : 'Add Expense'}</DialogTitle>
          <DialogDescription>
            {isEdit
              ? 'Update the expense details'
              : 'Add a new expense to this trip'}
          </DialogDescription>
        </DialogHeader>
        <form onSubmit={handleSubmit}>
          <FieldSet>
            <FieldGroup>
              <Field>
                <FieldLabel htmlFor="date">Date</FieldLabel>
                <FieldContent>
                  <Input
                    id="date"
                    type="date"
                    value={data.date}
                    onChange={(e) => setData('date', e.target.value)}
                  />
                  <FieldError
                    errors={
                      errors.date ? [{ message: errors.date }] : undefined
                    }
                  />
                </FieldContent>
              </Field>

              <Field>
                <FieldLabel htmlFor="amount">Amount</FieldLabel>
                <FieldContent>
                  <Input
                    id="amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    value={data.amount}
                    onChange={(e) => setData('amount', e.target.value)}
                    placeholder="Enter amount"
                  />
                  <FieldError
                    errors={
                      errors.amount ? [{ message: errors.amount }] : undefined
                    }
                  />
                </FieldContent>
              </Field>

              <Field>
                <FieldLabel htmlFor="description">Description</FieldLabel>
                <FieldContent>
                  <Input
                    id="description"
                    type="text"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    placeholder="Enter description"
                  />
                  <FieldError
                    errors={
                      errors.description
                        ? [{ message: errors.description }]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              <Field>
                <FieldContent>
                  <div className="flex items-center gap-2">
                    <Checkbox
                      id="is_shared"
                      checked={data.is_shared as boolean}
                      onCheckedChange={(checked) =>
                        setData('is_shared', checked === true)
                      }
                    />
                    <label htmlFor="is_shared" className="cursor-pointer">
                      Share this expense
                    </label>
                  </div>
                  <FieldError
                    errors={
                      errors.is_shared
                        ? [{ message: errors.is_shared }]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {data.is_shared && tripMembers.length > 0 && (
                <Field>
                  <FieldLabel>Share with</FieldLabel>
                  <FieldContent>
                    <div className="flex flex-col gap-2">
                      {tripMembers.map((member) => (
                        <div
                          key={member.id}
                          className="flex items-center gap-2"
                        >
                          <Checkbox
                            id={`member-${member.id}`}
                            checked={(data.shared_with as number[]).includes(
                              member.id,
                            )}
                            onCheckedChange={() => toggleUser(member.id)}
                          />
                          <label
                            htmlFor={`member-${member.id}`}
                            className="cursor-pointer"
                          >
                            {member.name}
                          </label>
                        </div>
                      ))}
                    </div>
                    <FieldError
                      errors={
                        errors.shared_with
                          ? [{ message: errors.shared_with }]
                          : undefined
                      }
                    />
                  </FieldContent>
                </Field>
              )}
            </FieldGroup>
          </FieldSet>
          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => onOpenChange(false)}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={processing}>
              {processing
                ? 'Saving...'
                : isEdit
                  ? 'Update Expense'
                  : 'Add Expense'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
