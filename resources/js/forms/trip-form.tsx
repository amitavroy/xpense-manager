import { router, useForm } from '@inertiajs/react';
import { ConfirmDialog } from '../components/confirm-dialog';
import { Button } from '../components/ui/button';
import { Card, CardContent } from '../components/ui/card';
import { Checkbox } from '../components/ui/checkbox';
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
import { destroy, store, update } from '../routes/trips';
import { Trip, UserDropdown } from '../types';

interface TripFormProps {
  trip: Trip;
  users?: UserDropdown[];
}

export default function TripForm({ trip, users = [] }: TripFormProps) {
  const isEdit = trip.id !== undefined;
  const { data, setData, post, put, processing, errors, reset } = useForm({
    name: trip.name || '',
    start_date: trip.start_date
      ? new Date(trip.start_date).toISOString().split('T')[0]
      : '',
    end_date: trip.end_date
      ? new Date(trip.end_date).toISOString().split('T')[0]
      : '',
    user_ids: trip.members?.map((m) => m.id) || [],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const url = isEdit ? update(trip.id).url : store().url;
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

  const handleDelete = () => router.delete(destroy(trip.id).url);

  const toggleUser = (userId: number) => {
    const currentIds = data.user_ids as number[];
    if (currentIds.includes(userId)) {
      setData(
        'user_ids',
        currentIds.filter((id) => id !== userId),
      );
    } else {
      setData('user_ids', [...currentIds, userId]);
    }
  };

  return (
    <Card>
      <CardContent>
        <form onSubmit={handleSubmit}>
          <FieldSet>
            <FieldLegend>{isEdit ? 'Edit Trip' : 'Add Trip'}</FieldLegend>
            <FieldDescription>
              {isEdit
                ? 'Edit the details of a trip'
                : 'Add details about a new trip'}
            </FieldDescription>
            <FieldGroup>
              <Field>
                <FieldLabel htmlFor="name">Trip Name</FieldLabel>
                <FieldContent>
                  <Input
                    id="name"
                    type="text"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="Enter trip name"
                  />
                  <FieldError
                    errors={
                      errors.name ? [{ message: errors.name }] : undefined
                    }
                  />
                </FieldContent>
              </Field>

              <Field>
                <FieldLabel htmlFor="start_date">Start Date</FieldLabel>
                <FieldContent>
                  <Input
                    id="start_date"
                    type="date"
                    value={data.start_date}
                    onChange={(e) => setData('start_date', e.target.value)}
                  />
                  <FieldError
                    errors={
                      errors.start_date
                        ? [{ message: errors.start_date }]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              <Field>
                <FieldLabel htmlFor="end_date">End Date</FieldLabel>
                <FieldContent>
                  <Input
                    id="end_date"
                    type="date"
                    value={data.end_date}
                    onChange={(e) => setData('end_date', e.target.value)}
                  />
                  <FieldError
                    errors={
                      errors.end_date
                        ? [{ message: errors.end_date }]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {users.length > 0 && (
                <Field>
                  <FieldLabel>Add Members</FieldLabel>
                  <FieldContent>
                    <div className="flex flex-col gap-2">
                      {users.map((user) => (
                        <div key={user.id} className="flex items-center gap-2">
                          <Checkbox
                            id={`user-${user.id}`}
                            checked={(data.user_ids as number[]).includes(
                              user.id,
                            )}
                            onCheckedChange={() => toggleUser(user.id)}
                          />
                          <label
                            htmlFor={`user-${user.id}`}
                            className="cursor-pointer"
                          >
                            {user.name}
                          </label>
                        </div>
                      ))}
                    </div>
                    <FieldError
                      errors={
                        errors.user_ids
                          ? [{ message: errors.user_ids }]
                          : undefined
                      }
                    />
                  </FieldContent>
                </Field>
              )}
            </FieldGroup>

            <div className="flex justify-between gap-2">
              {isEdit && (
                <ConfirmDialog
                  title="Delete Trip"
                  description="Are you sure you want to delete this trip?"
                  confirmButtonText="Delete"
                  trigger={
                    <Button variant="destructive" type="button">
                      Delete
                    </Button>
                  }
                  onConfirm={handleDelete}
                />
              )}
              <Button type="submit" disabled={processing}>
                {processing ? 'Saving...' : isEdit ? 'Save Trip' : 'Add Trip'}
              </Button>
            </div>
          </FieldSet>
        </form>
      </CardContent>
    </Card>
  );
}
