import { Head } from '@inertiajs/react';
import Heading from '../../components/heading';
import TripForm from '../../forms/trip-form';
import AppLayout from '../../layouts/app-layout';
import { index } from '../../routes/trips';
import { BreadcrumbItem, Trip, UserDropdown } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Trips',
    href: index().url,
  },
  {
    title: 'Add Trip',
    href: '#',
  },
];

interface TripsCreateProps {
  trip: Trip;
  users: UserDropdown[];
}

export default function TripsCreatePage({ trip, users }: TripsCreateProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Add Trip" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title="Add Trip" description="Add a new trip" />

        <div className="w-full lg:grid lg:grid-cols-4">
          <div className="lg:col-span-2">
            <div className="flex flex-col gap-4">
              <TripForm trip={trip} users={users} />
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
