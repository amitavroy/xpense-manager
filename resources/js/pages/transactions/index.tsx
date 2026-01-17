import { Head, router } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import ExpenseFiltersComponent from '../../components/expense-filters';
import Heading from '../../components/heading';
import Pagination from '../../components/pagination';
import { Button } from '../../components/ui/button';
import { Card, CardContent } from '../../components/ui/card';
import { useExpenseFilters } from '../../hooks/use-expense-filters';
import AppLayout from '../../layouts/app-layout';
import { create, index } from '../../routes/transactions';
import TransactionsTable from '../../tables/transactions-table';
import {
  BreadcrumbItem,
  ExpenseFilters,
  PaginateData,
  Transaction,
  User,
} from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Transactions',
    href: index().url,
  },
];

interface TransactionsIndexProps {
  transactions: PaginateData<Transaction>;
  filters?: ExpenseFilters;
  users?: User[];
}

export default function TransactionsIndexPage({
  transactions,
  filters: initialFilters = {},
  users = [],
}: TransactionsIndexProps) {
  const {
    filters,
    isLoading,
    selectedUserIds,
    selectedUsersText,
    hasActiveFilters,
    getFilterSummary,
    handlePresetClick,
    handleDateChange,
    handleUserToggle,
    handleClearFilters,
    PRESETS,
  } = useExpenseFilters({
    initialFilters,
    users,
  });

  const goToAddTransactionPage = () => {
    router.visit(create().url);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Transactions" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading
          title="Transactions"
          description="All my transactions transactions"
        />

        <div className="flex w-full justify-end">
          <Button onClick={goToAddTransactionPage}>
            <PlusIcon />
            Add Expense
          </Button>
        </div>

        {/* Filter Section */}
        <ExpenseFiltersComponent
          filters={filters}
          isLoading={isLoading}
          selectedUserIds={selectedUserIds}
          selectedUsersText={selectedUsersText}
          hasActiveFilters={hasActiveFilters}
          getFilterSummary={getFilterSummary}
          handlePresetClick={handlePresetClick}
          handleDateChange={handleDateChange}
          handleUserToggle={handleUserToggle}
          handleClearFilters={handleClearFilters}
          PRESETS={PRESETS}
          users={users}
        />

        <div className="flex flex-col gap-4">
          {transactions.data.length === 0 ? (
            <Card>
              <CardContent className="p-8 text-center">
                <p className="text-muted-foreground">
                  No expenses found for the selected filters.
                </p>
                {hasActiveFilters && (
                  <Button
                    variant="outline"
                    className="mt-4"
                    onClick={handleClearFilters}
                  >
                    Clear Filters
                  </Button>
                )}
              </CardContent>
            </Card>
          ) : (
            <>
              <TransactionsTable transactions={transactions} type="expense" />
              <Pagination paginatedData={transactions} />
            </>
          )}
        </div>
      </div>
    </AppLayout>
  );
}
