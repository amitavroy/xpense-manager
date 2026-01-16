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
import { destroy, store, update } from '../routes/vehicles';
import { Vehicle } from '../types';

export default function VehicleForm({ vehicle }: { vehicle: Vehicle }) {
  const isEdit = vehicle.id !== undefined;
  const { data, setData, post, put, processing, errors, reset } = useForm({
    name: vehicle.name ? vehicle.name : '',
    company_name: vehicle.company_name ? vehicle.company_name : '',
    registration_number: vehicle.registration_number
      ? vehicle.registration_number
      : '',
    kilometers: vehicle.kilometers !== undefined ? vehicle.kilometers : 0,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const url = isEdit ? update(vehicle.id!).url : store().url;
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

  const handleDelete = () => {
    if (vehicle.id) {
      router.delete(destroy(vehicle.id).url);
    }
  };

  return (
    <Card>
      <CardContent>
        <form onSubmit={handleSubmit}>
          <FieldSet>
            <FieldLegend>
              {isEdit ? 'Edit Vehicle' : 'Add a new Vehicle'}
            </FieldLegend>
            <FieldDescription>
              {isEdit
                ? 'Update vehicle details'
                : 'Add details about a new vehicle'}
            </FieldDescription>
            <FieldGroup>
              {/* Vehicle name */}
              <Field>
                <FieldLabel htmlFor="name">Vehicle name</FieldLabel>
                <FieldContent>
                  <Input
                    id="name"
                    type="text"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="Enter the vehicle name"
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

              {/* Company name */}
              <Field>
                <FieldLabel htmlFor="company_name">Company name</FieldLabel>
                <FieldContent>
                  <Input
                    id="company_name"
                    type="text"
                    value={data.company_name}
                    onChange={(e) => setData('company_name', e.target.value)}
                    placeholder="Enter the company name"
                  />
                  <FieldError
                    errors={
                      errors.company_name
                        ? [
                            {
                              message: errors.company_name,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Registration number */}
              <Field>
                <FieldLabel htmlFor="registration_number">
                  Registration number
                </FieldLabel>
                <FieldContent>
                  <Input
                    id="registration_number"
                    type="text"
                    value={data.registration_number}
                    onChange={(e) =>
                      setData('registration_number', e.target.value)
                    }
                    placeholder="Enter the registration number"
                  />
                  <FieldError
                    errors={
                      errors.registration_number
                        ? [
                            {
                              message: errors.registration_number,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Kilometers */}
              <Field>
                <FieldLabel htmlFor="kilometers">Kilometers</FieldLabel>
                <FieldContent>
                  <Input
                    id="kilometers"
                    type="number"
                    min="0"
                    step="1"
                    value={data.kilometers}
                    onChange={(e) =>
                      setData('kilometers', parseInt(e.target.value) || 0)
                    }
                    placeholder="Enter kilometers"
                  />
                  <FieldError
                    errors={
                      errors.kilometers
                        ? [
                            {
                              message: errors.kilometers,
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
              {isEdit && (
                <ConfirmDialog
                  title="Archive Vehicle"
                  description="Are you sure you want to archive this vehicle? This action cannot be undone."
                  confirmButtonText="Archive"
                  trigger={
                    <Button variant="destructive" type="button">
                      Archive
                    </Button>
                  }
                  onConfirm={handleDelete}
                />
              )}
              <Button type="submit" disabled={processing}>
                {processing
                  ? 'Saving...'
                  : isEdit
                    ? 'Save Vehicle'
                    : 'Add Vehicle'}
              </Button>
            </div>
          </FieldSet>
        </form>
      </CardContent>
    </Card>
  );
}
