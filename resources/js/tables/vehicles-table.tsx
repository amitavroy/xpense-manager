import { router } from '@inertiajs/react';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '../components/ui/table';
import { formatDate } from '../lib/utils';
import { show } from '../routes/vehicles';
import { PaginateData, Vehicle } from '../types';

interface VehiclesTableProps {
  vehicles: PaginateData<Vehicle>;
  fullView?: boolean;
}

export default function VehiclesTable({
  vehicles,
  fullView = true,
}: VehiclesTableProps) {
  return (
    <>
      <div className="flex items-center justify-center py-2 uppercase">
        <h2 className="text-lg font-bold">My vehicles</h2>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            {fullView && <TableHead>#</TableHead>}
            {fullView && <TableHead>Date</TableHead>}
            <TableHead>Name</TableHead>
            {fullView && <TableHead>Company</TableHead>}
            {fullView && <TableHead>Registration</TableHead>}
            <TableHead className="text-right">Kilometers</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {vehicles.data.map((vehicle) => (
            <TableRow
              key={vehicle.id}
              onClick={() => router.visit(show(vehicle.id!).url)}
              className="cursor-pointer"
            >
              {fullView && <TableCell>{vehicle.id}</TableCell>}
              {fullView && (
                <TableCell>
                  {vehicle.created_at ? formatDate(vehicle.created_at) : '-'}
                </TableCell>
              )}
              <TableCell>{vehicle.name}</TableCell>
              {fullView && <TableCell>{vehicle.company_name}</TableCell>}
              {fullView && <TableCell>{vehicle.registration_number}</TableCell>}
              <TableCell className="text-right">
                {vehicle.kilometers.toLocaleString()} km
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </>
  );
}
