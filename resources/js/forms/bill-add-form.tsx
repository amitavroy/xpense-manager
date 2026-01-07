import { useForm } from '@inertiajs/react';
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
import { Switch } from '../components/ui/switch';
import bills from '../routes/bills';
import { Bill, Biller, BillFrequency } from '../types';

interface BillAddFormProps {
  bill: Bill;
  biller: Biller;
}

const frequencyOptions = [
  { label: 'Weekly', value: 'weekly' },
  { label: 'Monthly', value: 'monthly' },
  { label: 'Quarterly', value: 'quarterly' },
  { label: 'Half-Yearly', value: 'half_yearly' },
  { label: 'Yearly', value: 'yearly' },
  { label: 'Custom', value: 'custom' },
];

export default function BillAddForm({ bill, biller }: BillAddFormProps) {
  const isEdit = bill?.id !== undefined;
  const { data, setData, post, put, processing, errors, reset } = useForm({
    biller_id: biller.id,
    default_amount: bill?.default_amount || 0,
    frequency: bill?.frequency || 'monthly',
    next_payment_date:
      bill?.next_payment_date || new Date().toISOString().split('T')[0],
    interval_days: bill?.interval_days || 1,
    auto_generate_bill: bill?.auto_generate_bill ?? true,
  });
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const url = isEdit ? bills.update(bill.id).url : bills.store().url;
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
  return (
    <Card>
      <CardContent>
        <form onSubmit={handleSubmit}>
          <FieldSet>
            <FieldLegend>Add a new Bill</FieldLegend>
            <FieldDescription>Add details about a new bill</FieldDescription>
            <FieldGroup>
              {/* Default Amount */}
              <Field>
                <FieldLabel htmlFor="default_amount">Default Amount</FieldLabel>
                <FieldContent>
                  <Input
                    id="default_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    value={data.default_amount}
                    onChange={(e) =>
                      setData('default_amount', parseFloat(e.target.value))
                    }
                    placeholder="Enter default amount"
                  />
                  <FieldError
                    errors={
                      errors.default_amount
                        ? [
                            {
                              message: errors.default_amount,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Frequency */}
              <Field>
                <FieldLabel htmlFor="frequency">Frequency</FieldLabel>
                <FieldContent>
                  <Select
                    value={data.frequency}
                    onValueChange={(value) =>
                      setData('frequency', value as BillFrequency)
                    }
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select account type" />
                    </SelectTrigger>
                    <SelectContent>
                      {frequencyOptions.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                          {option.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <FieldError
                    errors={
                      errors.frequency
                        ? [
                            {
                              message: errors.frequency,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Next Payment Date */}
              <Field>
                <FieldLabel htmlFor="next_payment_date">
                  Next Payment Date
                </FieldLabel>
                <FieldContent>
                  <Input
                    id="next_payment_date"
                    type="date"
                    value={data.next_payment_date}
                    onChange={(e) =>
                      setData('next_payment_date', e.target.value)
                    }
                  />
                  <FieldError
                    errors={
                      errors.next_payment_date
                        ? [
                            {
                              message: errors.next_payment_date,
                            },
                          ]
                        : undefined
                    }
                  />
                </FieldContent>
              </Field>

              {/* Interval Days */}
              {data.frequency === 'custom' && (
                <Field>
                  <FieldLabel htmlFor="interval_days">Interval Days</FieldLabel>
                  <FieldContent>
                    <Input
                      id="interval_days"
                      type="number"
                      step="1"
                      min="1"
                      max="31"
                      value={data.interval_days}
                      onChange={(e) =>
                        setData('interval_days', parseInt(e.target.value))
                      }
                      placeholder="Enter interval days"
                    />
                    <FieldError
                      errors={
                        errors.interval_days
                          ? [
                              {
                                message: errors.interval_days,
                              },
                            ]
                          : undefined
                      }
                    />
                  </FieldContent>
                </Field>
              )}

              {/* Auto Generate Bill */}
              <Field>
                <FieldLabel htmlFor="auto_generate_bill">
                  Auto Generate Bill
                </FieldLabel>
                <FieldContent>
                  <Switch
                    id="auto_generate_bill"
                    checked={data.auto_generate_bill}
                    onCheckedChange={(checked) =>
                      setData('auto_generate_bill', checked)
                    }
                  />
                </FieldContent>
              </Field>

              {/* Submit Button */}
              <div className="flex justify-between gap-2">
                <Button type="submit" disabled={processing}>
                  {processing ? 'Saving...' : isEdit ? 'Save Bill' : 'Add Bill'}
                </Button>
              </div>
            </FieldGroup>
          </FieldSet>
        </form>
      </CardContent>
    </Card>
  );
}
