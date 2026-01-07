import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Bill {
  id: number;
  user_id: number;
  biller_id: number;
  default_amount: number;
  frequency: BillFrequency;
  interval_days: number | null;
  next_payment_date: string;
  is_active: boolean;
  auto_generate_bill: boolean;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  biller?: Biller;
  user?: User;
}
export type BillFrequency =
  | 'weekly'
  | 'monthly'
  | 'quarterly'
  | 'half_yearly'
  | 'yearly'
  | 'custom';

export interface Biller {
  id: number;
  user_id: number;
  category_id: number;
  name: string;
  description: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  category?: Category;
  user?: User;
  bills?: Bill[];
}

export interface BillInstance {
  id: number;
  bill_id: number;
  transaction_id: number;
  due_date: string;
  amount: number;
  status: BillStatus;
  paid_date: string | null;
  notes: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  bill?: Bill;
  transaction?: Transaction;
}

export type BillStatus = 'pending' | 'paid' | 'skipped' | 'cancelled';

export interface Category {
  id: number;
  name: string;
  type: CategoryType;
  is_active: boolean;
  created_at: string;
}

export type AccountType = 'bank' | 'cash' | 'credit_card';
export type CategoryType = 'income' | 'expense';
export type TransactionType = 'income' | 'expense';

export interface Account {
  id: number;
  name: string;
  type: AccountType;
  balance: number;
  currency: string;
  is_active: boolean;
  created_at: string;
}

export interface Transaction {
  id: number;
  user_id: number;
  account_id: number;
  category_id: number;
  amount: number;
  date: string;
  description: string;
  account?: Account;
  category?: Category;
  user?: User;
}

export interface PaginateData<T> {
  data: T[];
  current_page: number;
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  links: {
    url: string | null;
    label: string;
    active: boolean;
  }[];
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number;
  total: number;
}

export interface Auth {
  user: User;
}

export interface BreadcrumbItem {
  title: string;
  href: string;
}

export interface NavGroup {
  title: string;
  items: NavItem[];
}

export interface NavItem {
  title: string;
  href: NonNullable<InertiaLinkProps['href']>;
  icon?: LucideIcon | null;
  isActive?: boolean;
  prefetch?: boolean;
}

export interface SharedData {
  name: string;
  quote: { message: string; author: string };
  auth: Auth;
  sidebarOpen: boolean;
  [key: string]: unknown;
}

export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  email_verified_at: string | null;
  two_factor_enabled?: boolean;
  created_at: string;
  updated_at: string;
  [key: string]: unknown; // This allows for additional properties...
}

export interface AccountDropdown {
  id: number;
  name: string;
}

export interface CategoryDropdown {
  id: number;
  name: string;
}
