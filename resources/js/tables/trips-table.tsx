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
import { show } from '../routes/trips';
import { PaginateData, Trip } from '../types';

interface TripsTableProps {
  trips: PaginateData<Trip>;
}

export default function TripsTable({ trips }: TripsTableProps) {
  const handleTripClick = (tripId: number) => {
    router.visit(show(tripId).url);
  };

  return (
    <>
      <div className="flex items-center justify-center py-2 uppercase">
        <h2 className="text-lg font-bold">My Trips</h2>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>#</TableHead>
            <TableHead>Name</TableHead>
            <TableHead>Start Date</TableHead>
            <TableHead>End Date</TableHead>
            <TableHead>Members</TableHead>
            <TableHead>Created By</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {trips.data.map((trip) => (
            <TableRow
              key={trip.id}
              onClick={() => handleTripClick(trip.id)}
              className="cursor-pointer"
            >
              <TableCell>{trip.id}</TableCell>
              <TableCell>{trip.name}</TableCell>
              <TableCell>{formatDate(trip.start_date)}</TableCell>
              <TableCell>{formatDate(trip.end_date)}</TableCell>
              <TableCell>
                {trip.members ? trip.members.length + 1 : 1}
              </TableCell>
              <TableCell>{trip.user?.name}</TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </>
  );
}
