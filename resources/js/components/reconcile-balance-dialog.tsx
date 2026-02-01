import ReconcileBalanceForm from '../forms/reconcile-balance-form';
import { Account } from '../types';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from './ui/dialog';

interface ReconcileBalanceDialogProps {
  account: Account;
  isOpen: boolean;
  onClose: () => void;
}

export default function ReconcileBalanceDialog({
  account,
  isOpen,
  onClose,
}: ReconcileBalanceDialogProps) {
  return (
    <Dialog open={isOpen} onOpenChange={(open) => !open && onClose()}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Reconcile Balance</DialogTitle>
          <DialogDescription>
            Enter the actual balance from your bank to sync with the tracked
            balance. An adjustment transaction will be created if there is a
            difference.
          </DialogDescription>
        </DialogHeader>
        <ReconcileBalanceForm account={account} onSuccess={onClose} />
      </DialogContent>
    </Dialog>
  );
}
