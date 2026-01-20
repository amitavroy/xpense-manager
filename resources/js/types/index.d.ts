import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface ExpenseStats {
  currentMonthTotalExpense: number;
  previousMonthTotalExpense: number;
}

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
  credit_limit?: number;
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

export interface Trip {
  id: number;
  user_id: number;
  name: string;
  start_date: string;
  end_date: string;
  created_at: string;
  updated_at: string;
  user?: User;
  members?: User[];
  expenses?: TripExpense[];
}

export interface TripExpense {
  id: number;
  trip_id: number;
  paid_by: number;
  date: string;
  amount: number;
  description: string;
  is_shared: boolean;
  created_at: string;
  updated_at: string;
  trip?: Trip;
  paid_by_user?: User;
  shared_with?: User[];
}

export interface TripStats {
  totalExpensesByUser: number;
  totalSharedExpenses: number;
  totalNonSharedExpenses: number;
}

export interface UserDropdown {
  id: number;
  name: string;
}

export interface Vehicle {
  id?: number;
  user_id: number;
  name: string;
  company_name: string;
  registration_number: string;
  kilometers: number;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;
  user?: User;
}

export interface VehicleDropdown {
  id: number;
  name: string;
}

export interface FuelEntry {
  id?: number;
  user_id?: number;
  vehicle_id?: number;
  account_id?: number;
  date?: string;
  odometer_reading?: number;
  fuel_quantity?: number;
  amount?: number;
  petrol_station_name?: string;
  created_at?: string;
  updated_at?: string;
  vehicle?: Vehicle;
  account?: Account;
  user?: User;
}

export interface ExpenseFilters {
  user_id?: number | null;
  user_ids?: number[];
  from_date?: string | null;
  to_date?: string | null;
  preset?: ExpensePreset | null;
}

export type ExpensePreset =
  | 'last_30_days'
  | 'this_month'
  | 'last_month'
  | 'last_week';
