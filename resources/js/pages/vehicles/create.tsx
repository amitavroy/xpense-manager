import { Head } from '@inertiajs/react';
import Heading from '../../components/heading';
import VehicleForm from '../../forms/vehicle-form';
import AppLayout from '../../layouts/app-layout';
import { index } from '../../routes/vehicles';
import { BreadcrumbItem, Vehicle } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Vehicles',
    href: index().url,
  },
  {
    title: 'Add Vehicle',
    href: '#',
  },
];

interface VehiclesCreateProps {
  vehicle: Vehicle;
}

export default function VehiclesCreatePage({ vehicle }: VehiclesCreateProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Add Vehicle" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title="Add Vehicle" description="Add a new vehicle" />

        <div className="w-full lg:grid lg:grid-cols-4">
          <div className="lg:col-span-2">
            <div className="flex flex-col gap-4">
              <VehicleForm vehicle={vehicle} />
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
