import { formatDate } from '@/lib/utils';
import { index } from '@/routes/transactions';
import { ExpenseFilters, ExpensePreset, User } from '@/types';
import { router } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';

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
  const [filters, setFilters] = useState<ExpenseFilters>({
    user_id: initialFilters.user_id ?? null,
    user_ids: initialFilters.user_ids ?? [],
    from_date: initialFilters.from_date ?? null,
    to_date: initialFilters.to_date ?? null,
    preset: initialFilters.preset ?? null,
  });

  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    setFilters({
      user_id: initialFilters.user_id ?? null,
      user_ids: initialFilters.user_ids ?? [],
      from_date: initialFilters.from_date ?? null,
      to_date: initialFilters.to_date ?? null,
      preset: initialFilters.preset ?? null,
    });
  }, [initialFilters]);

  const applyFilters = useCallback((newFilters: ExpenseFilters) => {
    setIsLoading(true);
    const params = new URLSearchParams();

    // Use user_ids if provided, otherwise fall back to user_id for backward compatibility
    if (newFilters.user_ids && newFilters.user_ids.length > 0) {
      newFilters.user_ids.forEach((id) => {
        params.append('user_ids[]', id.toString());
      });
    } else if (newFilters.user_id) {
      params.set('user_id', newFilters.user_id.toString());
    }
    if (newFilters.from_date) {
      params.set('from_date', newFilters.from_date);
    }
    if (newFilters.to_date) {
      params.set('to_date', newFilters.to_date);
    }
    if (newFilters.preset) {
      params.set('preset', newFilters.preset);
    }

    router.get(
      index().url + (params.toString() ? `?${params.toString()}` : ''),
      {},
      {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => setIsLoading(false),
      },
    );
  }, []);

  const handlePresetClick = useCallback(
    (preset: ExpensePreset) => {
      const newFilters: ExpenseFilters = {
        ...filters,
        preset,
        from_date: null,
        to_date: null,
      };
      setFilters(newFilters);
      applyFilters(newFilters);
    },
    [filters, applyFilters],
  );

  const handleDateChange = useCallback(
    (field: 'from_date' | 'to_date', value: string) => {
      const newFilters: ExpenseFilters = {
        ...filters,
        [field]: value || null,
        preset: null, // Clear preset when manually selecting dates
      };
      setFilters(newFilters);
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
      setFilters(newFilters);
      applyFilters(newFilters);
    },
    [filters, applyFilters],
  );

  const handleClearFilters = useCallback(() => {
    const clearedFilters: ExpenseFilters = {
      user_id: null,
      user_ids: [],
      from_date: null,
      to_date: null,
      preset: null,
    };
    setFilters(clearedFilters);
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
