import { useForm } from '@inertiajs/react';
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
import { store } from '../routes/fuel-entry/index';
import { AccountDropdown, FuelEntry, VehicleDropdown } from '../types';

interface FuelEntryFormProps {
  fuelEntry: FuelEntry;
  vehicles: VehicleDropdown[];
  accounts: AccountDropdown[];
  vehicleId?: number;
}

export default function FuelEntryForm({
  fuelEntry,
  vehicles,
  accounts,
  vehicleId,
}: FuelEntryFormProps) {
  const initialVehicleId =
    vehicleId !== undefined && vehicleId !== null
      ? vehicleId.toString()
      : fuelEntry.vehicle_id
        ? fuelEntry.vehicle_id.toString()
        : '';

  const { data, setData, post, processing, errors, reset } = useForm({
    vehicle_id: initialVehicleId,
    account_id: fuelEntry.account_id ? fuelEntry.account_id.toString() : '',
    date: fuelEntry.date
      ? new Date(fuelEntry.date).toISOString().split('T')[0]
      : new Date().toISOString().split('T')[0],
    odometer_reading: fuelEntry.odometer_reading || '',
    fuel_quantity: fuelEntry.fuel_quantity || '',
    amount: fuelEntry.amount || '',
    petrol_station_name: fuelEntry.petrol_station_name || '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(store().url, {
      onSuccess: () => {
        reset();
      },
    });
  };

  return (
    <Card>
      <CardContent>
        <form onSubmit={handleSubmit}>
          <FieldSet>
            <FieldLegend>Add Fuel Entry</FieldLegend>
            <FieldDescription>
              Record a new fuel purchase for your vehicle
            </FieldDescription>
            <FieldGroup>
              {/* Vehicle Selection */}
              <Field>
                <FieldLabel htmlFor="vehicle_id">Vehicle</FieldLabel>
                <FieldContent>
                  <Select
                    value={data.vehicle_id}
                    onValueChange={(value) => setData('vehicle_id', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select a vehicle" />
                    </SelectTrigger>
                    <SelectContent>
                      {vehicles.map((vehicle) => (
                        <SelectItem
                          key={vehicle.id}
                          value={vehicle.id.toString()}
                        >
                          {vehicle.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FieldError
                    errors={
                      errors.vehicle_id
                        ? [
                            {
                              message: errors.vehicle_id,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Account Selection */}
              <Field>
                <FieldLabel htmlFor="account_id">Bank Account</FieldLabel>
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

              {/* Date Selection */}
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
                      errors.date
                        ? [
                            {
                              message: errors.date,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Odometer Reading */}
              <Field>
                <FieldLabel htmlFor="odometer_reading">
                  Odometer Reading
                </FieldLabel>
                <FieldContent>
                  <Input
                    id="odometer_reading"
                    type="number"
                    min="0"
                    step="1"
                    value={data.odometer_reading}
                    onChange={(e) =>
                      setData('odometer_reading', e.target.value)
                    }
                    placeholder="Enter odometer reading"
                  />
                  <FieldError
                    errors={
                      errors.odometer_reading
                        ? [
                            {
                              message: errors.odometer_reading,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Fuel Quantity */}
              <Field>
                <FieldLabel htmlFor="fuel_quantity">Fuel Quantity</FieldLabel>
                <FieldContent>
                  <Input
                    id="fuel_quantity"
                    type="number"
                    min="0"
                    step="0.01"
                    value={data.fuel_quantity}
                    onChange={(e) => setData('fuel_quantity', e.target.value)}
                    placeholder="Enter fuel quantity"
                  />
                  <FieldError
                    errors={
                      errors.fuel_quantity
                        ? [
                            {
                              message: errors.fuel_quantity,
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
                    min="0"
                    step="0.01"
                    value={data.amount}
                    onChange={(e) => setData('amount', e.target.value)}
                    placeholder="Enter amount"
                  />
                  <FieldError
                    errors={
                      errors.amount
                        ? [
                            {
                              message: errors.amount,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Petrol Station Name */}
              <Field>
                <FieldLabel htmlFor="petrol_station_name">
                  Petrol Station Name
                </FieldLabel>
                <FieldContent>
                  <Input
                    id="petrol_station_name"
                    type="text"
                    value={data.petrol_station_name}
                    onChange={(e) =>
                      setData('petrol_station_name', e.target.value)
                    }
                    placeholder="Enter petrol station name"
                  />
                  <FieldError
                    errors={
                      errors.petrol_station_name
                        ? [
                            {
                              message: errors.petrol_station_name,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>
            </FieldGroup>

            {/* Submit Button */}
            <div className="flex justify-end gap-2">
              <Button type="submit" disabled={processing}>
                {processing ? 'Saving...' : 'Add Fuel Entry'}
              </Button>
            </div>
          </FieldSet>
        </form>
      </CardContent>
    </Card>
  );
}
