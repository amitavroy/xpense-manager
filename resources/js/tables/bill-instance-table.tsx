import { CreditCardIcon } from 'lucide-react';
import { Button } from '../components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '../components/ui/dialog';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '../components/ui/table';
import BillPayForm from '../forms/bill-pay-form';
import { formatCurrency, formatDate } from '../lib/utils';
import { Account, BillInstance, PaginateData } from '../types';

interface BillInstanceTableProps {
  billInstances: BillInstance[];
  accounts: PaginateData<Account>;
}

export default function BillInstanceTable({
  billInstances,
  accounts,
}: BillInstanceTableProps) {
  const accountOptions = accounts.data.map((account) => ({
    id: account.id,
    name: account.name,
    balance: account.balance.toString(),
  }));

  const renderTable = () => {
    return (
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>#</TableHead>
            <TableHead>Biller</TableHead>
            <TableHead>Frequency</TableHead>
            <TableHead>Due Date</TableHead>
            <TableHead>Amount</TableHead>
            <TableHead>Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {billInstances.map((billInstance) => (
            <TableRow key={billInstance.id}>
              <TableCell>{billInstance.id}</TableCell>
              <TableCell>{billInstance.bill?.biller?.name}</TableCell>
              <TableCell className="capitalize">
                {billInstance.bill?.frequency}
              </TableCell>
              <TableCell>{formatDate(billInstance.due_date)}</TableCell>
              <TableCell>{formatCurrency(billInstance.amount)}</TableCell>
              <TableCell>
                <Dialog>
                  <DialogTrigger asChild>
                    <Button variant="default" size="sm">
                      <CreditCardIcon />
                      Pay
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>Pay Bill</DialogTitle>
                      <DialogDescription>
                        Select an account and pay the bill.
                      </DialogDescription>
                    </DialogHeader>
                    <BillPayForm
                      pendingBill={billInstance}
                      accounts={accountOptions}
                    />
                  </DialogContent>
                </Dialog>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    );
  };

  return (
    <>
      <div className="flex items-center justify-center py-2 uppercase">
        <h2 className="text-lg font-bold">Pending Bills</h2>
      </div>
      {billInstances.length > 0 ? (
        renderTable()
      ) : (
        <div className="flex items-center justify-center py-12">
          <p className="text-sm text-gray-500">No pending bills found</p>
        </div>
      )}
    </>
  );
}
