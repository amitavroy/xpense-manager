import { Head } from '@inertiajs/react';
import Heading from '../../components/heading';
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '../../components/ui/accordion';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '../../components/ui/card';
import BillAddForm from '../../forms/bill-add-form';
import BillerForm from '../../forms/biller-form';
import AppLayout from '../../layouts/app-layout';
import { formatDate } from '../../lib/utils';
import billers from '../../routes/billers';
import { Bill, Biller, BreadcrumbItem, Category } from '../../types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Billers',
    href: billers.index().url,
  },
  {
    title: 'Biller Details',
    href: '#',
  },
];

interface BillerShowProps {
  biller: Biller;
  bill: Bill;
  categories: Category[];
}

export default function BillersShowPage({
  biller,
  bill,
  categories,
}: BillerShowProps) {
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Biller Details" />

      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading
          title="Biller Details"
          description="View the details of a biller"
        />

        <div className="grid grid-cols-4 gap-4">
          <div className="col-span-2">
            <div className="flex flex-col">
              <BillerForm biller={biller} categories={categories} />
            </div>
          </div>
          <div className="col-span-2 flex flex-col space-y-4">
            <Accordion
              type="single"
              collapsible
              className="w-full"
              defaultValue="bills-accordion"
            >
              <AccordionItem value="add-bill-accordion">
                <AccordionTrigger>Add Bill</AccordionTrigger>
                <AccordionContent>
                  <BillAddForm bill={bill} biller={biller} />
                </AccordionContent>
              </AccordionItem>

              {biller.bills && biller.bills.length > 0 && (
                <AccordionItem value="bills-accordion">
                  <AccordionTrigger>Bills</AccordionTrigger>
                  <AccordionContent>
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                      {biller.bills?.map((bill) => (
                        <Card key={bill.id}>
                          <CardHeader>
                            <CardTitle>
                              <h3 className="uppercase">
                                {bill.frequency} - {bill.default_amount}
                              </h3>
                            </CardTitle>
                          </CardHeader>
                          <CardContent>
                            <p className="text-sm text-muted-foreground">
                              Next Payment: {formatDate(bill.next_payment_date)}
                            </p>
                          </CardContent>
                        </Card>
                      ))}
                    </div>
                  </AccordionContent>
                </AccordionItem>
              )}
            </Accordion>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
