import { Head } from '@inertiajs/react';
import Heading from '../../components/heading';
import FuelEntryForm from '../../forms/fuel-entry-form';
import AppLayout from '../../layouts/app-layout';
import { create } from '../../routes/fuel-entry/index';
import {
  AccountDropdown,
  BreadcrumbItem,
  FuelEntry,
  VehicleDropdown,
} from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Fuel Entry',
    href: create().url,
  },
];

interface FuelEntryCreateProps {
  fuelEntry: FuelEntry;
  vehicles: VehicleDropdown[];
  accounts: AccountDropdown[];
  vehicleId?: number | null;
}

export default function FuelEntryCreatePage({
  fuelEntry,
  vehicles,
  accounts,
  vehicleId,
}: FuelEntryCreateProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Add Fuel Entry" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading
          title="Add Fuel Entry"
          description="Record a new fuel purchase"
        />

        <div className="w-full lg:grid lg:grid-cols-4">
          <div className="lg:col-span-2">
            <div className="flex flex-col gap-4">
              <FuelEntryForm
                fuelEntry={fuelEntry}
                vehicles={vehicles}
                accounts={accounts}
                vehicleId={vehicleId ?? undefined}
              />
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
