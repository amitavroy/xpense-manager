import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '../components/ui/table';
import { formatCurrency, formatDate } from '../lib/utils';
import { FuelEntry } from '../types';

interface FuelEntriesTableProps {
  fuelEntries: FuelEntry[];
}

export default function FuelEntriesTable({
  fuelEntries,
}: FuelEntriesTableProps) {
  if (fuelEntries.length === 0) {
    return (
      <div className="flex items-center justify-center py-8 text-muted-foreground">
        No fuel entries found. Add your first fuel entry using the form.
      </div>
    );
  }

  return (
    <div className="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Date</TableHead>
            <TableHead>Odometer Reading</TableHead>
            <TableHead>Fuel Quantity (L)</TableHead>
            <TableHead>Amount</TableHead>
            <TableHead>Petrol Station</TableHead>
            <TableHead>Account</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {fuelEntries.map((entry) => {
            const odometerReading =
              entry.odometer_reading != null
                ? typeof entry.odometer_reading === 'string'
                  ? parseInt(entry.odometer_reading, 10)
                  : entry.odometer_reading
                : null;

            const fuelQuantity =
              entry.fuel_quantity != null
                ? typeof entry.fuel_quantity === 'string'
                  ? parseFloat(entry.fuel_quantity)
                  : entry.fuel_quantity
                : null;

            const amount =
              entry.amount != null
                ? typeof entry.amount === 'string'
                  ? parseFloat(entry.amount)
                  : entry.amount
                : null;

            return (
              <TableRow key={entry.id}>
                <TableCell>
                  {entry.date ? formatDate(entry.date) : '-'}
                </TableCell>
                <TableCell>
                  {odometerReading != null
                    ? odometerReading.toLocaleString()
                    : '-'}
                </TableCell>
                <TableCell>
                  {fuelQuantity != null ? fuelQuantity.toFixed(2) : '-'}
                </TableCell>
                <TableCell>
                  {amount != null ? formatCurrency(amount) : '-'}
                </TableCell>
                <TableCell>{entry.petrol_station_name || '-'}</TableCell>
                <TableCell>{entry.account?.name || '-'}</TableCell>
              </TableRow>
            );
          })}
        </TableBody>
      </Table>
    </div>
  );
}
