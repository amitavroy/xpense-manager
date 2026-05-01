import { formatDate } from '@/lib/utils';
import { index } from '@/routes/transactions';
import { ExpenseFilters, ExpensePreset, User } from '@/types';
import { router } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';

/** HTML date inputs expect `yyyy-MM-dd` (timezone-safe); normalize ISO payloads from the server. */
function normalizeDateForInput(value: string | null | undefined): string | null {
  if (value == null || value === '') {
    return null;
  }
  if (typeof value !== 'string') {
    return null;
  }
  return value.includes('T') ? value.slice(0, 10) : value;
}

function buildTransactionsIndexUrl(target: ExpenseFilters): string {
  const query: Record<string, string | number | (string | number)[] | undefined> =
    {};

  if (target.user_ids && target.user_ids.length > 0) {
    query.user_ids = target.user_ids;
  } else if (target.user_id) {
    query.user_id = target.user_id;
  }
  if (target.from_date) {
    query.from_date = target.from_date;
  }
  if (target.to_date) {
    query.to_date = target.to_date;
  }
  if (target.preset) {
    query.preset = target.preset;
  }

  return index.url({ query });
}

const PRESETS: { value: ExpensePreset; label: string }[] = [
  { value: 'last_30_days', label: 'Last 30 Days' },
  { value: 'this_month', label: 'This Month' },
  { value: 'last_month', label: 'Last Month' },
  { value: 'last_week', label: 'Last Week' },
];

interface UseExpenseFiltersProps {
  initialFilters?: ExpenseFilters;
  users?: User[];
}

export function useExpenseFilters({
  initialFilters = {},
  users = [],
}: UseExpenseFiltersProps = {}) {
  const userIdsKey = [...(initialFilters.user_ids ?? [])]
    .slice()
    .sort((a, b) => a - b)
    .join(',');

  /* eslint-disable react-hooks/exhaustive-deps -- `userIdsKey` fingerprints contents when Laravel sends a fresh `user_ids` array */
  const serverFilters = useMemo(
    (): ExpenseFilters => ({
      user_id: initialFilters.user_id ?? null,
      user_ids: initialFilters.user_ids
        ? [...initialFilters.user_ids]
        : [],
      from_date: normalizeDateForInput(initialFilters.from_date ?? null),
      to_date: normalizeDateForInput(initialFilters.to_date ?? null),
      preset: initialFilters.preset ?? null,
    }),
    [
      initialFilters.user_id,
      userIdsKey,
      initialFilters.from_date,
      initialFilters.to_date,
      initialFilters.preset,
    ],
  );
  /* eslint-enable react-hooks/exhaustive-deps */

  const [pendingDateRange, setPendingDateRange] = useState<{
    from_date: string | null;
    to_date: string | null;
  } | null>(null);

  useEffect(() => {
    setPendingDateRange(null);
  }, [
    serverFilters.from_date,
    serverFilters.to_date,
    serverFilters.preset,
  ]);

  const filters = useMemo((): ExpenseFilters => {
    if (pendingDateRange === null) {
      return serverFilters;
    }

    return {
      ...serverFilters,
      preset: null,
      from_date: pendingDateRange.from_date,
      to_date: pendingDateRange.to_date,
    };
  }, [serverFilters, pendingDateRange]);

  const [isLoading, setIsLoading] = useState(false);

  const applyFilters = useCallback((newFilters: ExpenseFilters) => {
    setIsLoading(true);

    router.get(buildTransactionsIndexUrl(newFilters), {}, {
      preserveScroll: true,
      preserveState: true,
      only: ['transactions', 'filters'],
      onFinish: () => setIsLoading(false),
    });
  }, []);

  const handlePresetClick = useCallback(
    (preset: ExpensePreset) => {
      setPendingDateRange(null);
      const newFilters: ExpenseFilters = {
        ...filters,
        preset,
        from_date: null,
        to_date: null,
      };
      applyFilters(newFilters);
    },
    [filters, applyFilters],
  );

  const handleDateChange = useCallback(
    (field: 'from_date' | 'to_date', value: string) => {
      const nextValue = value || null;
      const newFilters: ExpenseFilters = {
        ...filters,
        [field]: nextValue,
        preset: null,
      };
      setPendingDateRange({
        from_date: newFilters.from_date ?? null,
        to_date: newFilters.to_date ?? null,
      });
      applyFilters(newFilters);
    },
    [filters, applyFilters],
  );

  const handleUserToggle = useCallback(
    (userId: number) => {
      const currentUserIds = filters.user_ids || [];
      const newUserIds = currentUserIds.includes(userId)
        ? currentUserIds.filter((id) => id !== userId)
        : [...currentUserIds, userId];

      const newFilters: ExpenseFilters = {
        ...filters,
        user_ids: newUserIds,
        user_id: null, // Clear single user_id when using multi-select
      };
      applyFilters(newFilters);
    },
    [filters, applyFilters],
  );

  const handleClearFilters = useCallback(() => {
    setPendingDateRange(null);
    const clearedFilters: ExpenseFilters = {
      user_id: null,
      user_ids: [],
      from_date: null,
      to_date: null,
      preset: null,
    };
    applyFilters(clearedFilters);
  }, [applyFilters]);

  const selectedUserIds = useMemo(
    () => filters.user_ids || [],
    [filters.user_ids],
  );
  const selectedUsersText =
    selectedUserIds.length === 0
      ? 'All Users'
      : selectedUserIds.length === 1
        ? users.find((u) => u.id === selectedUserIds[0])?.name || '1 user'
        : `${selectedUserIds.length} users`;

  const hasActiveFilters =
    (filters.user_ids && filters.user_ids.length > 0) ||
    filters.user_id !== null ||
    filters.from_date !== null ||
    filters.to_date !== null ||
    filters.preset !== null;

  const getFilterSummary = useCallback(() => {
    const parts: string[] = [];

    // Add user filter info
    if (selectedUserIds.length > 0) {
      if (selectedUserIds.length === 1) {
        const userName =
          users.find((u) => u.id === selectedUserIds[0])?.name ||
          'selected user';
        parts.push(`for ${userName}`);
      } else {
        parts.push(`for ${selectedUserIds.length} users`);
      }
    }

    // Add date filter info
    if (filters.preset) {
      const presetLabel =
        PRESETS.find((p) => p.value === filters.preset)?.label || '';
      parts.push(`for ${presetLabel}`);
    } else if (filters.from_date || filters.to_date) {
      const from = filters.from_date
        ? formatDate(filters.from_date)
        : 'beginning';
      const to = filters.to_date ? formatDate(filters.to_date) : 'today';
      parts.push(`from ${from} to ${to}`);
    } else {
      // Default to current month
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
      const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
      parts.push(
        `from ${formatDate(firstDay.toISOString().split('T')[0])} to ${formatDate(lastDay.toISOString().split('T')[0])}`,
      );
    }

    return `Showing expenses ${parts.join(' ')}`;
  }, [filters, selectedUserIds, users]);

  return {
    filters,
    isLoading,
    selectedUserIds,
    selectedUsersText,
    hasActiveFilters,
    getFilterSummary,
    handlePresetClick,
    handleDateChange,
    handleUserToggle,
    handleClearFilters,
    PRESETS,
  } as const;
}
