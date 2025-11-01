import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../components/ui/table';
import { formatDate } from '../lib/utils';
import { BillInstance } from '../types';

interface BillInstanceTableProps {
    billInstances: BillInstance[];
}

export default function BillInstanceTable({
    billInstances,
}: BillInstanceTableProps) {
    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>#</TableHead>
                    <TableHead>Biller</TableHead>
                    <TableHead>Frequency</TableHead>
                    <TableHead>Due Date</TableHead>
                    <TableHead>Amount</TableHead>
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
                        <TableCell>
                            {formatDate(billInstance.due_date)}
                        </TableCell>
                        <TableCell>{billInstance.amount}</TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
