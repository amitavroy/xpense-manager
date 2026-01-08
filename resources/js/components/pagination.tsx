import { Link } from '@inertiajs/react';
import { ChevronLeftIcon, ChevronRightIcon } from 'lucide-react';
import { PaginateData } from '../types';
import { Button } from './ui/button';

interface PaginationProps<T> {
  paginatedData: PaginateData<T>;
}

export default function Pagination<T>({ paginatedData }: PaginationProps<T>) {
  const { links, last_page } = paginatedData;

  if (last_page <= 1) {
    return null;
  }

  const getLinkLabel = (label: string): string => {
    // Remove HTML entities and return clean text
    return label
      .replace(/&laquo;/g, '')
      .replace(/&raquo;/g, '')
      .replace(/&lsaquo;/g, '')
      .replace(/&rsaquo;/g, '')
      .trim();
  };

  return (
    <div className="flex items-center justify-center gap-2 py-4">
      {links.map((link, index) => {
        if (link.url === null) {
          return (
            <Button key={index} variant="outline" size="sm" disabled>
              {getLinkLabel(link.label)}
            </Button>
          );
        }

        const isActive = link.active;
        const label = getLinkLabel(link.label);
        const isPrevious = label === 'Previous';
        const isNext = label === 'Next';

        return (
          <Link key={index} href={link.url}>
            <Button
              variant={isActive ? 'default' : 'outline'}
              size="sm"
              className={isActive ? 'pointer-events-none' : ''}
            >
              {isPrevious ? (
                <>
                  <ChevronLeftIcon />
                  <span>Previous</span>
                </>
              ) : isNext ? (
                <>
                  <span>Next</span>
                  <ChevronRightIcon />
                </>
              ) : (
                <span>{label}</span>
              )}
            </Button>
          </Link>
        );
      })}
    </div>
  );
}
