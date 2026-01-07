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
import { Textarea } from '../components/ui/textarea';
import billers from '../routes/billers';
import { Biller, Category } from '../types';

interface BillerFormProps {
  biller: Biller;
  categories: Category[];
}

export default function BillerForm({ biller, categories }: BillerFormProps) {
  const isEdit = biller.id !== undefined;
  const { data, setData, post, put, processing, errors, reset } = useForm({
    name: biller.name ? biller.name : '',
    description: biller.description ? biller.description : '',
    category_id: biller.category_id ? biller.category_id : '',
  });
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const url = isEdit ? billers.update(biller.id).url : billers.store().url;
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

  const handleDelete = () => router.delete(billers.destroy(biller.id).url);
  return (
    <Card>
      <CardContent>
        <form onSubmit={handleSubmit}>
          <FieldSet>
            <FieldLegend>Add a new Biller</FieldLegend>
            <FieldDescription>Add details about a new biller</FieldDescription>
            <FieldGroup>
              {/* Biller name */}
              <Field>
                <FieldLabel htmlFor="name">Biller name</FieldLabel>
                <FieldContent>
                  <Input
                    id="name"
                    type="text"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    placeholder="Enter the biller name"
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

              {/* Biller category */}
              <Field>
                <FieldLabel htmlFor="type">Biller category</FieldLabel>
                <FieldContent>
                  <Select
                    value={data.category_id.toString()}
                    onValueChange={(value) => setData('category_id', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select account type" />
                    </SelectTrigger>
                    <SelectContent>
                      {categories.map((category) => (
                        <SelectItem
                          key={category.id}
                          value={category.id.toString()}
                        >
                          {category.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FieldError
                    errors={
                      errors.category_id
                        ? [
                            {
                              message: errors.category_id,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Biller description */}
              <Field>
                <FieldLabel htmlFor="description">Description</FieldLabel>
                <FieldContent>
                  <Textarea
                    placeholder="Enter description"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                  />

                  <FieldError
                    errors={
                      errors.description
                        ? [
                            {
                              message: errors.description,
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
                title="Delete Biller"
                description="Are you sure you want to delete this biller?"
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
                    ? 'Save Biller'
                    : 'Add Biller'}
              </Button>
            </div>
          </FieldSet>
        </form>
      </CardContent>
    </Card>
  );
}
