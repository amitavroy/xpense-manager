import { Head, router } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import Heading from '../../components/heading';
import Pagination from '../../components/pagination';
import { Button } from '../../components/ui/button';
import AppLayout from '../../layouts/app-layout';
import { create, index } from '../../routes/trips';
import TripsTable from '../../tables/trips-table';
import { BreadcrumbItem, PaginateData, Trip } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Trips',
    href: index().url,
  },
];

interface TripsIndexProps {
  trips: PaginateData<Trip>;
}

export default function TripsIndexPage({ trips }: TripsIndexProps) {
  const goToAddTripPage = () => {
    router.visit(create().url);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Trips" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title="Trips" description="All my trips" />

        <div className="flex w-full justify-end">
          <Button onClick={goToAddTripPage}>
            <PlusIcon />
            Add Trip
          </Button>
        </div>

        <div className="flex flex-col gap-4">
          <TripsTable trips={trips} />
          <Pagination paginatedData={trips} />
        </div>
      </div>
    </AppLayout>
  );
}
