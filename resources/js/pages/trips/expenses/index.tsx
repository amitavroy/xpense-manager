import { Head } from '@inertiajs/react';
import Heading from '../../../components/heading';
import AppLayout from '../../../layouts/app-layout';
import { show } from '../../../routes/trips';
import ExpensesTable from '../../../tables/expenses-table';
import { BreadcrumbItem, Trip, TripExpense } from '../../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Trips',
    href: '#',
  },
  {
    title: 'Trip Expenses',
    href: '#',
  },
];

interface TripExpensesIndexProps {
  trip: Trip;
  expenses: TripExpense[];
}

export default function TripExpensesIndexPage({
  trip,
  expenses,
}: TripExpensesIndexProps) {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const handleEdit = (expense: TripExpense) => {
    // Redirect to trip show page for editing
    window.location.href = show({ trip: trip.id }).url;
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Trip Expenses" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading
          title={`Expenses for ${trip.name}`}
          description="View and manage trip expenses"
        />

        <ExpensesTable
          trip={trip}
          expenses={expenses}
          filter="all"
          onEdit={handleEdit}
        />
      </div>
    </AppLayout>
  );
}
