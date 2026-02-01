import { Head } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '../../components/heading';
import ReconcileBalanceDialog from '../../components/reconcile-balance-dialog';
import { Button } from '../../components/ui/button';
import AccountForm from '../../forms/account-form';
import AppLayout from '../../layouts/app-layout';
import { index } from '../../routes/accounts';
import { Account, BreadcrumbItem } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Accounts',
    href: index().url,
  },
  {
    title: 'Add Account',
    href: '#',
  },
];

interface AccountsShowProps {
  account: Account;
}

export default function AccountsShowPage({ account }: AccountsShowProps) {
  const [reconcileDialogOpen, setReconcileDialogOpen] = useState(false);

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Account Details" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="flex flex-wrap items-center justify-between gap-4">
          <Heading
            title="Account Details"
            description="View the details of an account"
          />
          <Button
            type="button"
            variant="outline"
            onClick={() => setReconcileDialogOpen(true)}
          >
            Reconcile Balance
          </Button>
        </div>

        <ReconcileBalanceDialog
          account={account}
          isOpen={reconcileDialogOpen}
          onClose={() => setReconcileDialogOpen(false)}
        />

        <div className="w-full lg:grid lg:grid-cols-4">
          <div className="lg:col-span-2">
            <div className="flex flex-col gap-4">
              <AccountForm account={account} />
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
