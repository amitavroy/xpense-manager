import VehicleForm from '@/forms/vehicle-form';
import { Head, router } from '@inertiajs/react';
import Heading from '../../components/heading';
import { Button } from '../../components/ui/button';
import AppLayout from '../../layouts/app-layout';
import { create as createFuelEntry } from '../../routes/fuel-entry';
import { index } from '../../routes/vehicles';
import FuelEntriesTable from '../../tables/fuel-entries-table';
import { BreadcrumbItem, FuelEntry, Vehicle } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Vehicles',
    href: index().url,
  },
  {
    title: 'Vehicle Details',
    href: '#',
  },
];

interface VehiclesShowProps {
  vehicle: Vehicle;
  fuelEntries: FuelEntry[];
}

export default function VehiclesShowPage({
  vehicle,
  fuelEntries,
}: VehiclesShowProps) {
  const goToAddFuelEntryPage = () => {
    router.visit(
      createFuelEntry({
        query: {
          vehicle_id: vehicle.id,
        },
      }).url,
    );
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Vehicle Details" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="flex flex-wrap items-center justify-between gap-4">
          <Heading
            title="Vehicle Details"
            description="View the details of a vehicle"
          />
          <Button
            type="button"
            variant="outline"
            onClick={goToAddFuelEntryPage}
          >
            Fuel entry
          </Button>
        </div>

        <div className="flex flex-col gap-4 lg:grid lg:grid-cols-3">
          {/* Vehicle Form - 1/3 width */}
          <div className="flex flex-col gap-4">
            <VehicleForm vehicle={vehicle} />
          </div>

          {/* Fuel Entries Table - 2/3 width */}
          <div className="flex flex-col gap-4 lg:col-span-2">
            <FuelEntriesTable fuelEntries={fuelEntries} />
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
