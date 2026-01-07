import { router, useForm } from '@inertiajs/react';
import { ConfirmDialog } from '../components/confirm-dialog';
import { Button } from '../components/ui/button';
import { Card, CardContent } from '../components/ui/card';
import {
  Field,
  FieldContent,
  FieldDescription,
  FieldError,
  FieldGroup,
  FieldLabel,
  FieldLegend,
  FieldSet,
} from '../components/ui/field';
import { Input } from '../components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '../components/ui/select';
import { destroy, store, update } from '../routes/categories';
import { Category, CategoryType } from '../types';

const categoryTypeOptions: { value: CategoryType; label: string }[] = [
  { value: 'income', label: 'Income' },
  { value: 'expense', label: 'Expense' },
];

export default function CategoryForm({ category }: { category: Category }) {
  const isEdit = category.id !== undefined;
  const { data, setData, post, put, processing, errors, reset } = useForm({
    name: category.name ? category.name : '',
    type: category.type ? category.type : '',
  });
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const url = isEdit ? update(category.id).url : store().url;
    if (isEdit) {
      put(url);
    } else {
      post(url, {
        onSuccess: () => {
          reset();
        },
      });
    }
  };

  const handleDelete = () => router.delete(destroy(category.id).url);
  return (
    <Card>
      <CardContent>
        <form onSubmit={handleSubmit}>
          <FieldSet>
            <FieldLegend>Add a new Category</FieldLegend>
            <FieldDescription>
              Add details about a new category
            </FieldDescription>
            <FieldGroup>
              {/* Category name */}
              <Field>
                <FieldLabel htmlFor="name">Category name</FieldLabel>
                <FieldContent>
                  <Input
                    id="name"
                    type="text"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="Enter the category name"
                  />
                  <FieldError
                    errors={
                      errors.name
                        ? [
                            {
                              message: errors.name,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Category type */}
              <Field>
                <FieldLabel htmlFor="type">Category type</FieldLabel>
                <FieldContent>
                  <Select
                    value={data.type}
                    onValueChange={(value) => setData('type', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select account type" />
                    </SelectTrigger>
                    <SelectContent>
                      {categoryTypeOptions.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                          {option.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FieldError
                    errors={
                      errors.type
                        ? [
                            {
                              message: errors.type,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>
            </FieldGroup>

            {/* Submit Button */}
            <div className="flex justify-between gap-2">
              <ConfirmDialog
                title="Delete Category"
                description="Are you sure you want to delete this category?"
                confirmButtonText="Delete"
                trigger={
                  <Button variant="destructive" type="button">
                    Delete
                  </Button>
                }
                onConfirm={handleDelete}
              />
              <Button type="submit" disabled={processing}>
                {processing
                  ? 'Saving...'
                  : isEdit
                    ? 'Save Category'
                    : 'Add Category'}
              </Button>
            </div>
          </FieldSet>
        </form>
      </CardContent>
    </Card>
  );
}
