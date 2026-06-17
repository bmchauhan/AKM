<?php

namespace App\Enums;

enum FinanceExpenseTag: string
{
    case SecurityPayment = 'security_payment';
    case SocietySweeperPayment = 'society_sweeper_payment';
    case GarbageCollectorPayment = 'garbage_collector_payment';
    case ElectricityBill = 'electricity_bill';
    case CameraMaintenanceCharge = 'camera_maintenance_charge';
    case SewageCharge = 'sewage_charge';
    case WaterTankerCharges = 'water_tanker_charges';
    case ElectricItemRepairing = 'electric_item_repairing';
    case GardenerPayment = 'gardener_payment';
    case Others = 'others';

    public function requiresWorker(): bool
    {
        return $this->workerType() !== null;
    }

    public function workerType(): ?WorkerType
    {
        return WorkerType::fromExpenseTag($this);
    }

    public function label(): string
    {
        return match ($this) {
            self::SecurityPayment => __('messages.finance_expense_security'),
            self::SocietySweeperPayment => __('messages.finance_expense_sweeper'),
            self::GarbageCollectorPayment => __('messages.finance_expense_garbage'),
            self::ElectricityBill => __('messages.finance_expense_electricity'),
            self::CameraMaintenanceCharge => __('messages.finance_expense_camera'),
            self::SewageCharge => __('messages.finance_expense_sewage'),
            self::WaterTankerCharges => __('messages.finance_expense_water_tanker'),
            self::ElectricItemRepairing => __('messages.finance_expense_electric_repair'),
            self::GardenerPayment => __('messages.finance_expense_gardener'),
            self::Others => __('messages.finance_expense_others'),
        };
    }
}
