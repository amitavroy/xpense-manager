import { Head } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import { useState } from 'react';
import Heading from '../../components/heading';
import { Button } from '../../components/ui/button';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '../../components/ui/card';
import { ToggleGroup, ToggleGroupItem } from '../../components/ui/toggle-group';
import ExpenseForm from '../../forms/expense-form';
import TripForm from '../../forms/trip-form';
import AppLayout from '../../layouts/app-layout';
import { formatCurrency } from '../../lib/utils';
import { index } from '../../routes/trips';
import ExpensesTable from '../../tables/expenses-table';
import {
  BreadcrumbItem,
  Trip,
  TripExpense,
  TripStats,
  UserDropdown,
} from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Trips',
    href: index().url,
  },
  {
    title: 'Trip Details',
    href: '#',
  },
];

interface TripsShowProps {
  trip: Trip;
  expenses: TripExpense[];
  stats: TripStats;
  tripMembers: UserDropdown[];
}

export default function TripsShowPage({
  trip,
  expenses,
  stats,
  tripMembers,
}: TripsShowProps) {
  const [expenseModalOpen, setExpenseModalOpen] = useState(false);
  const [editingExpense, setEditingExpense] = useState<
    TripExpense | undefined
  >();
  const [expenseFilter, setExpenseFilter] = useState<'all' | 'my' | 'shared'>(
    'all',
  );
  const [isEditingTrip, setIsEditingTrip] = useState(false);

  const handleAddExpense = () => {
    setEditingExpense(undefined);
    setExpenseModalOpen(true);
  };

  const handleEditExpense = (expense: TripExpense) => {
    setEditingExpense(expense);
    setExpenseModalOpen(true);
  };

  const handleCloseExpenseModal = () => {
    setExpenseModalOpen(false);
    setEditingExpense(undefined);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Trip Details" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title={trip.name} description="Trip expense details" />

        {!isEditingTrip ? (
          <>
            <div className="grid gap-4 md:grid-cols-3">
              <Card>
                <CardHeader>
                  <CardTitle className="text-sm font-medium">
                    Total Expenses by Me
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">
                    {formatCurrency(stats.totalExpensesByUser)}
                  </div>
                </CardContent>
              </Card>
              <Card>
                <CardHeader>
                  <CardTitle className="text-sm font-medium">
                    Shared Expenses
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">
                    {formatCurrency(stats.totalSharedExpenses)}
                  </div>
                </CardContent>
              </Card>
              <Card>
                <CardHeader>
                  <CardTitle className="text-sm font-medium">
                    Non-Shared Expenses
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">
                    {formatCurrency(stats.totalNonSharedExpenses)}
                  </div>
                </CardContent>
              </Card>
            </div>

            <div className="flex items-center justify-between">
              <div className="flex gap-2">
                <ToggleGroup
                  type="single"
                  value={expenseFilter}
                  onValueChange={(value) =>
                    setExpenseFilter(
                      (value as 'all' | 'my' | 'shared') || 'all',
                    )
                  }
                >
                  <ToggleGroupItem value="all">All Expenses</ToggleGroupItem>
                  <ToggleGroupItem value="my">My Expenses</ToggleGroupItem>
                  <ToggleGroupItem value="shared">
                    Shared Expenses
                  </ToggleGroupItem>
                </ToggleGroup>
              </div>
              <div className="flex gap-2">
                <Button
                  variant="outline"
                  onClick={() => setIsEditingTrip(true)}
                >
                  Edit Trip
                </Button>
                <Button onClick={handleAddExpense}>
                  <PlusIcon />
                  Add Expense
                </Button>
              </div>
            </div>

            <ExpensesTable
              trip={trip}
              expenses={expenses}
              filter={expenseFilter}
              onEdit={handleEditExpense}
            />
          </>
        ) : (
          <div className="w-full lg:grid lg:grid-cols-4">
            <div className="lg:col-span-2">
              <div className="flex flex-col gap-4">
                <TripForm trip={trip} users={tripMembers} />
                <Button
                  variant="outline"
                  onClick={() => setIsEditingTrip(false)}
                >
                  Cancel
                </Button>
              </div>
            </div>
          </div>
        )}

        <ExpenseForm
          trip={trip}
          expense={editingExpense}
          tripMembers={tripMembers}
          open={expenseModalOpen}
          onOpenChange={handleCloseExpenseModal}
        />
      </div>
    </AppLayout>
  );
}
