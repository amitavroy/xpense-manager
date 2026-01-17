import { ExpenseFilters, ExpensePreset, User } from '@/types';
import { ChevronDownIcon, X } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent } from './ui/card';
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from './ui/dropdown-menu';
import { Input } from './ui/input';

interface ExpenseFiltersProps {
  filters: ExpenseFilters;
  isLoading: boolean;
  selectedUserIds: number[];
  selectedUsersText: string;
  hasActiveFilters: boolean;
  getFilterSummary: () => string;
  handlePresetClick: (preset: ExpensePreset) => void;
  handleDateChange: (field: 'from_date' | 'to_date', value: string) => void;
  handleUserToggle: (userId: number) => void;
  handleClearFilters: () => void;
  PRESETS: { value: ExpensePreset; label: string }[];
  users: User[];
}

export default function ExpenseFiltersComponent({
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
  users,
}: ExpenseFiltersProps) {
  return (
    <Card>
      <CardContent className="p-4">
        <div className="flex flex-col gap-4">
          {/* Date Presets */}
          <div className="flex flex-wrap gap-2">
            {PRESETS.map((preset) => (
              <Button
                key={preset.value}
                variant={
                  filters.preset === preset.value ? 'default' : 'outline'
                }
                size="sm"
                onClick={() => handlePresetClick(preset.value)}
                disabled={isLoading}
              >
                {preset.label}
              </Button>
            ))}
          </div>

          {/* Date Range Inputs and User Filter */}
          <div className="flex flex-wrap gap-4">
            <div className="flex flex-col gap-2">
              <label htmlFor="from_date" className="text-sm font-medium">
                From Date
              </label>
              <Input
                id="from_date"
                type="date"
                value={filters.from_date || ''}
                onChange={(e) => handleDateChange('from_date', e.target.value)}
                disabled={isLoading || filters.preset !== null}
              />
            </div>
            <div className="flex flex-col gap-2">
              <label htmlFor="to_date" className="text-sm font-medium">
                To Date
              </label>
              <Input
                id="to_date"
                type="date"
                value={filters.to_date || ''}
                onChange={(e) => handleDateChange('to_date', e.target.value)}
                disabled={isLoading || filters.preset !== null}
              />
            </div>
            {users.length > 0 && (
              <div className="flex flex-col gap-2">
                <label className="text-sm font-medium">Users</label>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button
                      variant="outline"
                      className="w-full justify-between"
                      disabled={isLoading}
                    >
                      <span>{selectedUsersText}</span>
                      <ChevronDownIcon className="h-4 w-4 opacity-50" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent className="w-56">
                    {users.map((user) => (
                      <DropdownMenuCheckboxItem
                        key={user.id}
                        checked={selectedUserIds.includes(user.id)}
                        onCheckedChange={() => handleUserToggle(user.id)}
                      >
                        {user.name}
                      </DropdownMenuCheckboxItem>
                    ))}
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            )}
            {hasActiveFilters && (
              <div className="flex items-end">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={handleClearFilters}
                  disabled={isLoading}
                >
                  <X className="mr-1 h-4 w-4" />
                  Clear Filters
                </Button>
              </div>
            )}
          </div>

          {/* Filter Summary */}
          <div className="text-sm text-muted-foreground">
            {isLoading ? 'Loading...' : getFilterSummary()}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
