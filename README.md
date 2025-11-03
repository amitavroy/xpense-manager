# Xpense manager

![Xpense Manager](https://raw.githubusercontent.com/amitavroy/expense_manager/main/docs/Dashboard-2.jpg)

This application is an Expense manager that I want to use to track my expenses. 

It should allow me to track my expenses, incomes. It should also allow me to keep a track of my bills.

## Technology used

Right now, this project uses 
- Laravel 
- Inertiajs 
- React with Typescript

This is based on the React starter kit of Laravel.
Database used for development has been SQLite. However, it should run easily on MySQL and even PGSql

## Installation

```
# Step 1
git clone https://github.com/amitavroy/expense_manager.git

# Step 2
cp .env.example .env

# Step 3
composer install && npm i

# Step 4
php artisan key:generate

# Step 5 (assuming you have configured your database connection in .env)
php artisan migrate --seed
```

# Modules 

## Transactions 

![Transaction Screenshot](https://raw.githubusercontent.com/amitavroy/expense_manager/main/docs/Screenshot%202025-10-26%20at%201.07.54%E2%80%AFPM.jpg)

The **Transactions** module will allow you to add expenses. Every expense is linked to a **Bank account**. When you add an expense transaction, you need to select the account. And, the same amount is deducted from your account.

![Manager your transaction](https://raw.githubusercontent.com/amitavroy/expense_manager/refs/heads/main/docs/Screenshot%202025-10-26%20at%201.08.03%E2%80%AFPM.jpg)

When you edit a transaction, we are also handling the scenarios like user changing the category and even account. The account balances are also updated based on the new edited transaction.

## Billers

Users will be able to add **Billers** and their **Bills**. Based on that, Bills will be available on Dashboard for payment. When a Bill is paid, it will also get added to the Expenses with correct category.

![Setup Billers](https://raw.githubusercontent.com/amitavroy/expense_manager/refs/heads/main/docs/Biller-Dashboard-1.jpg)

And you will get a quick access to pay bill right from the Dashboard. Each bill payment is recorded as a transaction as well.

![Setup Billers](https://raw.githubusercontent.com/amitavroy/expense_manager/refs/heads/main/docs/Bill-Pay-Modal-1.jpg)

```
select t.*, u.name, c.name, c.type
from transactions as t
join users as u on u.id = t.user_id
join categories as c on c.id = t.category_id
where c.type = 'expense'
```

Need to evaluate:
https://github.com/moneyphp/money
https://github.com/brick/money
