import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatDate(dateString: string): string {
  const date = new Date(dateString);
  const day = date.getDate().toString().padStart(2, '0');
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

const currencyFormatter = new Intl.NumberFormat('en-IN', {
  style: 'currency',
  currency: 'INR',
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});

/** e.g. `credit_card` → "Credit card", `normal` → "Normal". */
export function humanizeSnakeCase(value: string): string {
  return value
    .split('_')
    .map(
      (word) =>
        word.charAt(0).toUpperCase() + word.slice(1).toLowerCase(),
    )
    .join(' ');
}

export function formatCurrency(amount: number | string): string {
  const numbericAcmount =
    typeof amount === 'string' ? parseFloat(amount) : amount;

  if (!Number.isFinite(numbericAcmount)) {
    return currencyFormatter.format(0);
  }

  return currencyFormatter.format(numbericAcmount);
}

/** Short form for chart axes to avoid overflow (e.g. "₹10k", "₹1L"). */
export function formatCurrencyShort(amount: number): string {
  if (!Number.isFinite(amount)) return '₹0';
  const abs = Math.abs(amount);
  if (abs >= 1_00_00_000) return `₹${(amount / 1_00_00_000).toFixed(1)}Cr`;
  if (abs >= 1_00_000) return `₹${(amount / 1_00_000).toFixed(1)}L`;
  if (abs >= 1_000) return `₹${(amount / 1_000).toFixed(1)}k`;
  return currencyFormatter.format(amount);
}

export function getVehicleKilometers(
  vehicles: { id: number; kilometers?: number }[],
  vehicleIdValue: string,
): string {
  if (!vehicleIdValue) {
    return '';
  }

  const id = Number.parseInt(vehicleIdValue, 10);

  if (Number.isNaN(id)) {
    return '';
  }

  const vehicle = vehicles.find((item) => item.id === id);

  if (!vehicle || typeof vehicle.kilometers !== 'number') {
    return '';
  }

  return vehicle.kilometers.toString();
}
