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
import { show } from '../routes/categories';
import { Category, PaginateData } from '../types';

interface CategoryTableProps {
  categories: PaginateData<Category>;
}

export default function CategoriesTable({ categories }: CategoryTableProps) {
  return (
    <Table>
      <TableCaption>A list of categories</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead>#</TableHead>
          <TableHead>Date</TableHead>
          <TableHead>Name</TableHead>
          <TableHead>Type</TableHead>
          <TableHead className="text-right">Status</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {categories.data.map((category) => (
          <TableRow
            key={category.id}
            onClick={() => router.visit(show(category.id).url)}
            className="cursor-pointer"
          >
            <TableCell>{category.id}</TableCell>
            <TableCell>{formatDate(category.created_at)}</TableCell>
            <TableCell>{category.name}</TableCell>
            <TableCell className="capitalize">{category.type}</TableCell>
            <TableCell className="text-right">
              {category.is_active ? 'Active' : 'Inactive'}
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  );
}
