import { router } from '@inertiajs/react';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '../components/ui/table';
import { formatDate } from '../lib/utils';
import billerRoutes from '../routes/billers';
import { Biller, PaginateData } from '../types';

interface BillersTableProps {
  billers: PaginateData<Biller>;
}

export default function AccountsTable({ billers }: BillersTableProps) {
  return (
    <Table>
      <TableCaption>A list of my billers</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead>#</TableHead>
          <TableHead>Date</TableHead>
          <TableHead>Name</TableHead>
          <TableHead>Category</TableHead>
          <TableHead className="text-right">Status</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {billers.data.map((biller) => (
          <TableRow
            key={biller.id}
            onClick={() => router.visit(billerRoutes.show(biller.id).url)}
            className="cursor-pointer"
          >
            <TableCell>{biller.id}</TableCell>
            <TableCell>{formatDate(biller.created_at)}</TableCell>
            <TableCell>{biller.name}</TableCell>
            <TableCell className="capitalize">
              {biller.category?.name}
            </TableCell>
            <TableCell className="text-right">
              {biller.is_active ? 'Active' : 'Inactive'}
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  );
}
