import { Head, router } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import Heading from '../../components/heading';
import { Button } from '../../components/ui/button';
import AppLayout from '../../layouts/app-layout';
import { create, index } from '../../routes/accounts';
import AccountsTable from '../../tables/accounts-table';
import { Account, BreadcrumbItem, PaginateData } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Accounts',
    href: index().url,
  },
];

interface AccountsIndexProps {
  accounts: PaginateData<Account>;
}

export default function AccountsIndexPage({ accounts }: AccountsIndexProps) {
  const goToAddAccountPage = () => {
    router.visit(create().url);
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="My accounts" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading title="Accounts" description="All my accounts" />

        <div className="flex w-full justify-end">
          <Button onClick={goToAddAccountPage}>
            <PlusIcon />
            Add Account
          </Button>
        </div>

        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div className="col-span-full md:col-span-2">
            <div className="flex flex-col gap-4">
              <AccountsTable accounts={accounts} />
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
