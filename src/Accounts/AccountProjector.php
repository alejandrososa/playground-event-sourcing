<?php namespace KBC\Accounts;

use KBC\Accounts\Events\AccountWasClosed;
use KBC\Accounts\Events\AccountWasOpened;
use KBC\Accounts\Events\MoneyWasWithdrawn;
use KBC\Accounts\Events\MoneyWasDeposited;
use KBC\Storages\JsonDatabase;

final class AccountProjector
{
    protected $jsonDatabase;

    public function __construct(JsonDatabase $jsonDatabase)
    {
        $this->jsonDatabase = $jsonDatabase;
    }

    public function projectAccountWasOpened(AccountWasOpened $event)
    {
        $this->jsonDatabase->insert([
            'id' => $event->getAccountId()->getId(),
            'name' => $event->getName()->getFullName(),
            'balance' => $event->getBalance()->getAmount(),
            'closed' => $event->isClosed()
        ]);
    }

    public function projectMoneyWasDeposited(MoneyWasDeposited $event)
    {
        $this->jsonDatabase->update($event, function ($row) use ($event) {
            $row['balance'] += $event->getBalance()->getAmount();

            return $row;
        });
    }

    public function projectMoneyWasWithdrawn(MoneyWasWithdrawn $event)
    {
        $this->jsonDatabase->update($event, function ($row) use ($event) {
            $row['balance'] -= $event->getBalance()->getAmount();

            return $row;
        });
    }

    public function projectAccountWasClosed(AccountWasClosed $event)
    {
        $this->jsonDatabase->update($event, function ($row) {
            $row['closed'] = true;

            return $row;
        });
    }
}
