import { Head } from '@inertiajs/react';
import Heading from '../../components/heading';
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
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Account Details" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading
          title="Account Details"
          description="View the details of an account"
        />

        <div className="grid grid-cols-4">
          <div className="col-span-3 lg:col-span-2">
            <div className="flex flex-col gap-4">
              <AccountForm account={account} />
            </div>
          </div>
          <div></div>
        </div>
      </div>
    </AppLayout>
  );
}
