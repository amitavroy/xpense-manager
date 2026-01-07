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
import { show } from '../routes/accounts';
import { Account, PaginateData } from '../types';

interface AccountsTableProps {
  accounts: PaginateData<Account>;
  fullView?: boolean;
}

export default function AccountsTable({
  accounts,
  fullView = true,
}: AccountsTableProps) {
  return (
    <>
      <div className="flex items-center justify-center py-2 uppercase">
        <h2 className="text-lg font-bold">My accounts</h2>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            {fullView && <TableHead>#</TableHead>}
            {fullView && <TableHead>Date</TableHead>}
            <TableHead>Name</TableHead>
            {fullView && <TableHead>Type</TableHead>}
            {fullView && <TableHead>Status</TableHead>}
            <TableHead className="text-right">Balance</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {accounts.data.map((account) => (
            <TableRow
              key={account.id}
              onClick={() => router.visit(show(account.id).url)}
              className="cursor-pointer"
            >
              {fullView && <TableCell>{account.id}</TableCell>}
              {fullView && (
                <TableCell>{formatDate(account.created_at)}</TableCell>
              )}
              <TableCell>{account.name}</TableCell>
              {fullView && (
                <TableCell className="capitalize">{account.type}</TableCell>
              )}
              {fullView && (
                <TableCell>
                  {account.is_active ? 'Active' : 'Inactive'}
                </TableCell>
              )}
              <TableCell className="text-right">
                {formatCurrency(account.balance)}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </>
  );
}
