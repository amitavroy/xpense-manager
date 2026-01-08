import { router } from '@inertiajs/react';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '../components/ui/table';
import { formatCurrency, formatDate } from '../lib/utils';
import { show as showIncome } from '../routes/incomes';
import { show as showTransaction } from '../routes/transactions';
import { PaginateData, Transaction, TransactionType } from '../types';

interface TransactionsTableProps {
  transactions: PaginateData<Transaction>;
  type: TransactionType;
}

export default function TransactionsTable({
  transactions,
  type,
}: TransactionsTableProps) {
  const handleTransactionClick = (transactionId: number) => {
    router.visit(
      type === 'income'
        ? showIncome(transactionId).url
        : showTransaction(transactionId).url,
    );
  };
  return (
    <>
      <div className="flex items-center justify-center py-2 uppercase">
        <h2 className="text-lg font-bold">Recent Transactions</h2>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>#</TableHead>
            <TableHead>Date</TableHead>
            <TableHead>User</TableHead>
            <TableHead>Description</TableHead>
            <TableHead>Category</TableHead>
            <TableHead>Account</TableHead>
            <TableHead className="text-right">Amount</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {transactions.data.map((transaction) => (
            <TableRow
              key={transaction.id}
              onClick={() => handleTransactionClick(transaction.id)}
              className="cursor-pointer"
            >
              <TableCell>{transaction.id}</TableCell>
              <TableCell>{formatDate(transaction.date)}</TableCell>
              <TableCell>{transaction.user?.name}</TableCell>
              <TableCell>{transaction.description}</TableCell>
              <TableCell>{transaction.category?.name}</TableCell>
              <TableCell>{transaction.account?.name}</TableCell>
              <TableCell className="text-right">
                {formatCurrency(transaction.amount)}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </>
  );
}
