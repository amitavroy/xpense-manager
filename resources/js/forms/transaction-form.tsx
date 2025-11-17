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
import {
    destroy as destroyIncome,
    store as storeIncome,
    update as updateIncome,
} from '../routes/incomes';
import {
    destroy as destroyTransaction,
    store as storeTransaction,
    update as updateTransaction,
} from '../routes/transactions';
import {
    AccountDropdown,
    CategoryDropdown,
    Transaction,
    TransactionType,
} from '../types';

interface TransactionAddFormProps {
    accounts: AccountDropdown[];
    categories: CategoryDropdown[];
    transaction: Transaction;
    type: TransactionType;
}

export default function TransactionForm({
    accounts,
    categories,
    transaction,
    type,
}: TransactionAddFormProps) {
    const isEdit = transaction.id !== undefined;
    const { data, setData, post, put, processing, errors, reset } = useForm({
        account_id: transaction.account_id
            ? transaction.account_id.toString()
            : '',
        category_id: transaction.category_id
            ? transaction.category_id.toString()
            : '',
        amount: transaction.amount || '',
        date: transaction.date
            ? new Date(transaction.date).toISOString().split('T')[0]
            : '',
        description: transaction.description || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const url =
            type === 'income'
                ? isEdit
                    ? updateIncome(transaction.id).url
                    : storeIncome().url
                : isEdit
                  ? updateTransaction(transaction.id).url
                  : storeTransaction().url;

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

    const handleDelete = () =>
        router.delete(
            type === 'income'
                ? destroyIncome(transaction.id).url
                : destroyTransaction(transaction.id).url,
        );

    return (
        <Card>
            <CardContent>
                <form onSubmit={handleSubmit}>
                    <FieldSet>
                        <FieldLegend>
                            {isEdit ? 'Edit Transaction' : 'Add Transaction'}
                        </FieldLegend>
                        <FieldDescription>
                            {isEdit
                                ? 'Edit the details of a transaction'
                                : 'Add details about a new transaction'}
                        </FieldDescription>
                        <FieldGroup>
                            {/* Account Selection */}
                            <Field>
                                <FieldLabel htmlFor="account_id">
                                    Account
                                </FieldLabel>
                                <FieldContent>
                                    <Select
                                        value={data.account_id}
                                        onValueChange={(value) =>
                                            setData('account_id', value)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select an account" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {accounts.map((account) => (
                                                <SelectItem
                                                    key={account.id}
                                                    value={account.id.toString()}
                                                >
                                                    {account.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <FieldError
                                        errors={
                                            errors.account_id
                                                ? [
                                                      {
                                                          message:
                                                              errors.account_id,
                                                      },
                                                  ]
                                                : undefined
                                        }
                                    />
                                </FieldContent>
                            </Field>

                            {/* Category Selection */}
                            <Field>
                                <FieldLabel htmlFor="category_id">
                                    Category
                                </FieldLabel>
                                <FieldContent>
                                    <Select
                                        value={data.category_id}
                                        onValueChange={(value) =>
                                            setData('category_id', value)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a category" />
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
                                                          message:
                                                              errors.category_id,
                                                      },
                                                  ]
                                                : undefined
                                        }
                                    />
                                </FieldContent>
                            </Field>

                            {/* Amount */}
                            <Field>
                                <FieldLabel htmlFor="amount">Amount</FieldLabel>
                                <FieldContent>
                                    <Input
                                        id="amount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.amount}
                                        onChange={(e) =>
                                            setData('amount', e.target.value)
                                        }
                                        placeholder="Enter amount"
                                    />
                                    <FieldError
                                        errors={
                                            errors.amount
                                                ? [{ message: errors.amount }]
                                                : undefined
                                        }
                                    />
                                </FieldContent>
                            </Field>

                            {/* Date */}
                            <Field>
                                <FieldLabel htmlFor="date">Date</FieldLabel>
                                <FieldContent>
                                    <Input
                                        id="date"
                                        type="date"
                                        value={data.date}
                                        onChange={(e) =>
                                            setData('date', e.target.value)
                                        }
                                    />
                                    <FieldError
                                        errors={
                                            errors.date
                                                ? [{ message: errors.date }]
                                                : undefined
                                        }
                                    />
                                </FieldContent>
                            </Field>

                            {/* Description */}
                            <Field>
                                <FieldLabel htmlFor="description">
                                    Description
                                </FieldLabel>
                                <FieldContent>
                                    <Input
                                        id="description"
                                        type="text"
                                        value={data.description}
                                        onChange={(e) =>
                                            setData(
                                                'description',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Enter description"
                                    />
                                    <FieldError
                                        errors={
                                            errors.description
                                                ? [
                                                      {
                                                          message:
                                                              errors.description,
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
                                title="Delete Transaction"
                                description="Are you sure you want to delete this transaction?"
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
                                      ? 'Save Transaction'
                                      : 'Add Transaction'}
                            </Button>
                        </div>
                    </FieldSet>
                </form>
            </CardContent>
        </Card>
    );
}
