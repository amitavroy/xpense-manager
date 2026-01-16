import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as indexAccounts } from '@/routes/accounts';
import biller from '@/routes/billers';
import categories from '@/routes/categories';
import incomes from '@/routes/incomes';
import transactions from '@/routes/transactions';
import { index as indexTrips } from '@/routes/trips';
import { index as indexVehicles } from '@/routes/vehicles';
import { SharedData, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
  Banknote,
  Bike,
  BookOpen,
  Building,
  CreditCard,
  Folder,
  LayoutGrid,
  Plane,
} from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: dashboard(),
    icon: LayoutGrid,
  },
  {
    title: 'Incomes',
    href: incomes.index().url,
    icon: Banknote,
    prefetch: true,
  },
  {
    title: 'Expenses',
    href: transactions.index().url,
    icon: CreditCard,
    prefetch: false,
  },
  {
    title: 'Accounts',
    href: indexAccounts().url,
    icon: Banknote,
    prefetch: true,
  },
  {
    title: 'Categories',
    href: categories.index().url,
    icon: Folder,
    prefetch: true,
  },
  {
    title: 'Billers',
    href: biller.index().url,
    icon: Building,
    prefetch: true,
  },
  {
    title: 'Trips',
    href: indexTrips().url,
    icon: Plane,
    prefetch: true,
  },
  {
    title: 'Vehicles',
    href: indexVehicles().url,
    icon: Bike,
    prefetch: true,
  },
];

const footerNavItems: NavItem[] = [
  {
    title: 'Repository',
    href: 'https://github.com/laravel/react-starter-kit',
    icon: Folder,
  },
  {
    title: 'Documentation',
    href: 'https://laravel.com/docs/starter-kits#react',
    icon: BookOpen,
  },
];

export function AppSidebar() {
  const { name } = usePage<SharedData>().props;
  return (
    <Sidebar collapsible="icon" variant="inset">
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <Link href={dashboard()} prefetch>
                <AppLogo title={name} />
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <SidebarContent>
        <NavMain items={mainNavItems} />
      </SidebarContent>

      <SidebarFooter>
        <NavFooter items={footerNavItems} className="mt-auto" />
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
