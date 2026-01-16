import { router } from '@inertiajs/react';
import { PencilIcon, TrashIcon } from 'lucide-react';
import { ConfirmDialog } from '../components/confirm-dialog';
import { Button } from '../components/ui/button';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '../components/ui/table';
import { formatCurrency, formatDate } from '../lib/utils';
import { destroy } from '../routes/trips/expenses';
import { Trip, TripExpense } from '../types';

interface ExpensesTableProps {
  trip: Trip;
  expenses: TripExpense[];
  filter?: 'all' | 'my' | 'shared';
  onEdit: (expense: TripExpense) => void;
}

export default function ExpensesTable({
  trip,
  expenses,
  filter = 'all',
  onEdit,
}: ExpensesTableProps) {
  const filteredExpenses = expenses.filter((expense) => {
    if (filter === 'my') {
      return !expense.is_shared;
    }
    if (filter === 'shared') {
      return expense.is_shared;
    }
    return true;
  });

  const handleDelete = (expenseId: number) => {
    router.delete(destroy({ trip: trip.id, tripExpense: expenseId }).url);
  };

  return (
    <>
      <div className="flex items-center justify-center py-2 uppercase">
        <h2 className="text-lg font-bold">Expenses</h2>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Date</TableHead>
            <TableHead>Description</TableHead>
            <TableHead className="text-right">Amount</TableHead>
            <TableHead>Shared</TableHead>
            <TableHead>Paid By</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {filteredExpenses.length === 0 ? (
            <TableRow>
              <TableCell colSpan={6} className="text-center">
                No expenses found
              </TableCell>
            </TableRow>
          ) : (
            filteredExpenses.map((expense) => (
              <TableRow key={expense.id}>
                <TableCell>{formatDate(expense.date)}</TableCell>
                <TableCell>{expense.description}</TableCell>
                <TableCell className="text-right">
                  {formatCurrency(expense.amount)}
                </TableCell>
                <TableCell>{expense.is_shared ? 'Yes' : 'No'}</TableCell>
                <TableCell>{expense.paid_by_user?.name}</TableCell>
                <TableCell>
                  <div className="flex gap-2">
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => onEdit(expense)}
                    >
                      <PencilIcon className="h-4 w-4" />
                    </Button>
                    <ConfirmDialog
                      title="Delete Expense"
                      description="Are you sure you want to delete this expense?"
                      confirmButtonText="Delete"
                      trigger={
                        <Button variant="ghost" size="sm">
                          <TrashIcon className="h-4 w-4" />
                        </Button>
                      }
                      onConfirm={() => handleDelete(expense.id)}
                    />
                  </div>
                </TableCell>
              </TableRow>
            ))
          )}
        </TableBody>
      </Table>
    </>
  );
}
