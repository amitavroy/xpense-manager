import VehiclesTable from '@/tables/vehicles-table';
import { Head, router } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import Heading from '../../components/heading';
import { Button } from '../../components/ui/button';
import AppLayout from '../../layouts/app-layout';
import { create as createFuelEntry } from '../../routes/fuel-entry';
import { create, index } from '../../routes/vehicles';
import { BreadcrumbItem, PaginateData, Vehicle } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Vehicles',
    href: index().url,
  },
];

interface VehiclesIndexProps {
  vehicles: PaginateData<Vehicle>;
}

export default function VehiclesIndexPage({ vehicles }: VehiclesIndexProps) {
  const goToAddVehiclePage = () => {
    router.visit(create().url);
  };

  const goToAddFuelEntryPage = () => {
    router.visit(createFuelEntry().url);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="My vehicles" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title="Vehicles" description="All my vehicles" />

        <div className="flex w-full justify-end gap-2">
          <Button onClick={goToAddFuelEntryPage}>
            <PlusIcon />
            Add Fuel
          </Button>
          <Button onClick={goToAddVehiclePage} variant="outline">
            <PlusIcon />
            Add Vehicle
          </Button>
        </div>

        <div className="flex flex-col gap-4">
          <VehiclesTable vehicles={vehicles} />
        </div>
      </div>
    </AppLayout>
  );
}
