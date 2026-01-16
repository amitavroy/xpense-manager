import { Head, router } from '@inertiajs/react';
import { useEffect } from 'react';
import Heading from '../../../components/heading';
import AppLayout from '../../../layouts/app-layout';
import { show } from '../../../routes/trips';
import { BreadcrumbItem, Trip, TripExpense } from '../../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Trips',
    href: '#',
  },
  {
    title: 'Trip Expense',
    href: '#',
  },
];

interface TripExpenseShowProps {
  trip: Trip;
  expense: TripExpense;
}

export default function TripExpenseShowPage({ trip }: TripExpenseShowProps) {
  useEffect(() => {
    // Redirect to trip show page where expenses are managed via modal
    router.visit(show({ trip: trip.id }).url);
  }, [trip.id]);

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Trip Expense" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title="Trip Expense" description="Redirecting..." />
      </div>
    </AppLayout>
  );
}
