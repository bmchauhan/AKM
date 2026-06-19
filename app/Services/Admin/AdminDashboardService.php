<?php

namespace App\Services\Admin;

use App\Enums\FinanceCollectionType;
use App\Enums\MaintenanceMonthEntryStatus;
use App\Enums\MembershipRole;
use App\Enums\UserRole;
use App\Models\FinanceCollection;
use App\Models\FinanceExpense;
use App\Models\MaintenanceMonthlyEntry;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function __construct(
        private readonly AdminFinanceOverviewService $financeOverview,
        private readonly AdminFinanceFundSettingService $fundSettings,
        private readonly AdminFinanceMyPaymentService $myPayments,
    ) {}

    /**
     * @return array{
     *     sections: list<array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string}>,
     *     quick_actions: list<array{label: string, route: string, tone: string, description: string}>,
     *     side_panel: ?array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string},
     *     alerts: list<array{tone: string, message: string, action_route: ?string, action_label: ?string}>
     * }
     */
    public function screenData(User $actor): array
    {
        return [
            'sections' => $this->buildSections($actor),
            'quick_actions' => $this->quickActions($actor),
            'side_panel' => $this->sideSnapPanel($actor),
            'alerts' => $this->alerts($actor),
        ];
    }

    /**
     * @return list<array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string}>
     */
    private function buildSections(User $actor): array
    {
        $sections = [];

        if ($this->shouldShowSocietyStats($actor)) {
            $sections[] = $this->societySection($actor);
        }

        if ($financeSection = $this->financeSection($actor)) {
            $sections[] = $financeSection;
        }

        if ($actor->isMainMember()) {
            $sections[] = $this->householdSection($actor);
        }

        if ($paymentSection = $this->myPaymentsSection($actor)) {
            $sections[] = $paymentSection;
        }

        if ($residentSection = $this->residentSection($actor)) {
            $sections[] = $residentSection;
        }

        return $sections;
    }

    /**
     * @return array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string}|null
     */
    private function sideSnapPanel(User $actor): ?array
    {
        if ($committeeSection = $this->committeeDutySection($actor)) {
            return $committeeSection;
        }

        if ($actor->isSuperAdmin()) {
            return null;
        }

        if ($actor->isMainMember()) {
            return $this->householdPulseSection($actor);
        }

        if ($actor->isFamilyMember() || $actor->isRentalMember()) {
            return $this->residentPulseSection($actor);
        }

        return null;
    }

    /**
     * @return array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string}
     */
    private function householdPulseSection(User $actor): array
    {
        $outstanding = $this->memberOutstandingAmount($actor);
        $household = $this->householdCounts($actor);
        $profileComplete = $this->profileCompleteness($actor);
        $pendingMonths = MaintenanceMonthlyEntry::query()
            ->where('main_member_id', $actor->id)
            ->whereIn('status', $this->unpaidStatuses())
            ->count();
        $paidThisYear = (float) MaintenanceMonthlyEntry::query()
            ->where('main_member_id', $actor->id)
            ->whereYear('billing_month', Carbon::now()->year)
            ->sum('amount_paid');

        return [
            'key' => 'household_pulse',
            'title' => __('messages.dashboard_section_household_pulse'),
            'subtitle' => __('messages.dashboard_section_household_pulse_subtitle'),
            'stats' => [
                [
                    'key' => 'outstanding',
                    'label' => __('messages.dashboard_stat_outstanding'),
                    'value' => $this->fundSettings->formatCompactMoney($outstanding),
                    'hint' => $this->fundSettings->formatMoney($outstanding),
                    'tone' => $outstanding > 0 ? 'expense' : 'income',
                    'link' => route('admin.finance.my-payments.index'),
                ],
                [
                    'key' => 'pending_months',
                    'label' => __('messages.dashboard_stat_pending_months'),
                    'value' => (string) $pendingMonths,
                    'hint' => __('messages.dashboard_stat_pending_months_hint'),
                    'tone' => $pendingMonths > 0 ? 'danger' : 'primary',
                    'link' => route('admin.finance.my-payments.index'),
                ],
                [
                    'key' => 'paid_year',
                    'label' => __('messages.dashboard_stat_paid_year'),
                    'value' => $this->fundSettings->formatCompactMoney($paidThisYear),
                    'hint' => $this->fundSettings->formatMoney($paidThisYear),
                    'tone' => 'income',
                    'link' => route('admin.finance.my-payments.index'),
                ],
                [
                    'key' => 'household_members',
                    'label' => __('messages.dashboard_stat_household_members'),
                    'value' => (string) $household['total'],
                    'hint' => __('messages.dashboard_stat_household_members_hint', [
                        'family' => (string) $household['family'],
                        'rental' => (string) $household['rental'],
                    ]),
                    'tone' => 'accent',
                    'link' => route('admin.members.index'),
                ],
                [
                    'key' => 'profile_complete',
                    'label' => __('messages.dashboard_stat_profile_complete'),
                    'value' => $profileComplete.'%',
                    'hint' => __('messages.dashboard_stat_profile_complete_hint'),
                    'tone' => $profileComplete >= 80 ? 'income' : 'neutral',
                    'link' => route('admin.profile'),
                ],
            ],
            'link' => route('admin.members.index'),
            'link_label' => __('messages.dashboard_manage_household'),
        ];
    }

    /**
     * @return array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string}
     */
    private function residentPulseSection(User $actor): array
    {
        $mainMember = $actor->mainMember;

        return [
            'key' => 'resident_pulse',
            'title' => __('messages.dashboard_section_resident_pulse'),
            'subtitle' => __('messages.dashboard_resident_linked', [
                'name' => $mainMember?->fullName() ?? '—',
            ]),
            'stats' => [
                [
                    'key' => 'membership',
                    'label' => __('messages.members_type'),
                    'value' => $actor->membership_type
                        ? MembershipRole::from($actor->membership_type)->shortForm()
                        : '—',
                    'hint' => $actor->membership_type
                        ? MembershipRole::from($actor->membership_type)->label()
                        : null,
                    'tone' => 'primary',
                    'link' => route('admin.profile'),
                ],
                [
                    'key' => 'main_member',
                    'label' => __('messages.members_main_member'),
                    'value' => $mainMember?->fullName() ?? '—',
                    'hint' => null,
                    'tone' => 'accent',
                    'link' => route('admin.profile'),
                ],
                [
                    'key' => 'house',
                    'label' => __('messages.users_house'),
                    'value' => $actor->houseLabel() ?? $mainMember?->houseLabel() ?? '—',
                    'hint' => null,
                    'tone' => 'income',
                    'link' => route('admin.profile'),
                ],
            ],
            'link' => route('admin.profile'),
            'link_label' => __('messages.dashboard_view_profile'),
        ];
    }

    /**
     * @return array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string}|null
     */
    private function committeeDutySection(User $actor): ?array
    {
        if (! $actor->hasCommitteeRole() || $actor->isSuperAdmin()) {
            return null;
        }

        $role = $actor->committeeRoleRecord;

        return [
            'key' => 'committee_duty',
            'title' => __('messages.dashboard_section_committee'),
            'subtitle' => __('messages.dashboard_section_committee_subtitle', [
                'role' => $role?->name ?? $actor->roleLabel(),
            ]),
            'stats' => $this->committeeDutyStats($actor),
            'link' => $this->primaryCommitteeRoute($actor),
            'link_label' => __('messages.dashboard_view_my_modules'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function committeeDutyStats(User $actor): array
    {
        $stats = [];

        if ($actor->can('users_all.read')) {
            $stats[] = [
                'key' => 'users_access',
                'label' => __('messages.dashboard_stat_users_access'),
                'value' => __('messages.dashboard_stat_granted'),
                'hint' => __('messages.dashboard_stat_users_access_hint'),
                'tone' => 'primary',
                'link' => route('admin.users.index'),
            ];
        }

        if ($actor->can('members_all.read')) {
            $stats[] = [
                'key' => 'members_access',
                'label' => __('messages.dashboard_stat_members_access'),
                'value' => __('messages.dashboard_stat_granted'),
                'hint' => __('messages.dashboard_stat_members_access_hint'),
                'tone' => 'income',
                'link' => route('admin.members.index'),
            ];
        }

        if ($actor->can('finance_collections.read')) {
            $monthTotal = (float) FinanceCollection::query()
                ->where('collection_type', '!=', FinanceCollectionType::Maintenance->value)
                ->whereYear('received_on', Carbon::now()->year)
                ->whereMonth('received_on', Carbon::now()->month)
                ->sum('amount');

            $stats[] = [
                'key' => 'my_collections_month',
                'label' => __('messages.dashboard_stat_collections_month'),
                'value' => $this->fundSettings->formatMoney($monthTotal),
                'hint' => __('messages.dashboard_stat_collections_month_hint'),
                'tone' => 'income',
                'link' => route('admin.finance.collections.index'),
            ];
        }

        if ($actor->can('finance_maintenance_ledger.read')) {
            $pending = $this->pendingMaintenanceCount();

            $stats[] = [
                'key' => 'pending_maintenance',
                'label' => __('messages.dashboard_stat_pending_maintenance'),
                'value' => (string) $pending,
                'hint' => __('messages.dashboard_stat_pending_maintenance_hint'),
                'tone' => $pending > 0 ? 'expense' : 'income',
                'link' => route('admin.finance.maintenance-ledger.index'),
            ];
        }

        if ($actor->can('workers.read')) {
            $stats[] = [
                'key' => 'workers_access',
                'label' => __('messages.dashboard_stat_workers_access'),
                'value' => __('messages.dashboard_stat_granted'),
                'hint' => __('messages.dashboard_stat_workers_access_hint'),
                'tone' => 'accent',
                'link' => route('admin.workers.index'),
            ];
        }

        if ($stats === []) {
            $stats[] = [
                'key' => 'committee_role',
                'label' => __('messages.dashboard_stat_committee_role'),
                'value' => $actor->roleLabel(),
                'hint' => __('messages.dashboard_stat_committee_role_hint'),
                'tone' => 'accent',
                'link' => route('admin.profile'),
            ];
        }

        return $stats;
    }

    /**
     * @return array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string}
     */
    private function societySection(User $actor): array
    {
        return [
            'key' => 'society',
            'title' => __('messages.dashboard_section_society'),
            'subtitle' => $actor->isSuperAdmin()
                ? __('messages.dashboard_section_society_subtitle_sa')
                : __('messages.dashboard_section_society_subtitle_committee'),
            'stats' => $this->societyStats($actor),
            'link' => $actor->can('users_all.read') ? route('admin.users.index') : null,
            'link_label' => $actor->can('users_all.read') ? __('messages.dashboard_view_users') : null,
        ];
    }

    /**
     * @return array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string}|null
     */
    private function financeSection(User $actor): ?array
    {
        if ($actor->can('finance.read') || $actor->can('finance_overview.read')) {
            return [
                'key' => 'finance',
                'title' => __('messages.dashboard_section_finance'),
                'subtitle' => __('messages.dashboard_section_finance_subtitle'),
                'stats' => $this->financeOverview->screenData(['period' => 'month'])['fund_stats'],
                'link' => route('admin.finance.index'),
                'link_label' => __('messages.finance_view_overview'),
            ];
        }

        $stats = [];

        if ($actor->can('finance_collections.read')) {
            $stats = array_merge($stats, $this->collectionOpsStats());
        }

        if ($actor->can('finance_expenses.read')) {
            $stats = array_merge($stats, $this->expenseOpsStats());
        }

        if ($actor->can('finance_maintenance_ledger.read')) {
            $stats[] = [
                'key' => 'ledger_pending',
                'label' => __('messages.dashboard_stat_pending_maintenance'),
                'value' => $this->pendingMaintenanceCount(),
                'hint' => __('messages.dashboard_stat_pending_maintenance_hint'),
                'tone' => $this->pendingMaintenanceCount() > 0 ? 'expense' : 'income',
            ];
        }

        if ($stats === []) {
            return null;
        }

        return [
            'key' => 'finance_scoped',
            'title' => __('messages.dashboard_section_finance'),
            'subtitle' => __('messages.dashboard_section_finance_scoped_subtitle'),
            'stats' => $stats,
            'link' => $this->firstFinanceRoute($actor),
            'link_label' => __('messages.dashboard_open_finance'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function collectionOpsStats(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $monthTotal = (float) FinanceCollection::query()
            ->whereBetween('received_on', [$start, $end])
            ->sum('amount');

        $maintenanceMonth = (float) MaintenanceMonthlyEntry::query()
            ->whereHas('mainMember')
            ->whereBetween('billing_month', [$start, $end])
            ->sum('amount_paid');

        return [
            [
                'key' => 'collections_month',
                'label' => __('messages.dashboard_stat_collections_month'),
                'value' => $this->fundSettings->formatMoney($monthTotal + $maintenanceMonth),
                'hint' => __('messages.dashboard_stat_collections_month_hint'),
                'tone' => 'income',
            ],
            [
                'key' => 'collections_today',
                'label' => __('messages.dashboard_stat_collections_today'),
                'value' => (int) FinanceCollection::query()->whereDate('received_on', Carbon::today())->count()
                    + (int) MaintenanceMonthlyEntry::query()
                        ->whereHas('mainMember')
                        ->whereDate('paid_on', Carbon::today())
                        ->where('amount_paid', '>', 0)
                        ->count(),
                'hint' => __('messages.dashboard_stat_collections_today_hint'),
                'tone' => 'accent',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function expenseOpsStats(): array
    {
        $start = Carbon::now()->startOfMonth();

        return [
            [
                'key' => 'expenses_month',
                'label' => __('messages.dashboard_stat_expenses_month'),
                'value' => $this->fundSettings->formatMoney(
                    (float) FinanceExpense::query()->whereDate('paid_on', '>=', $start)->sum('amount'),
                ),
                'hint' => __('messages.dashboard_stat_expenses_month_hint'),
                'tone' => 'expense',
            ],
        ];
    }

    /**
     * @return array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string}
     */
    private function householdSection(User $actor): array
    {
        return [
            'key' => 'household',
            'title' => __('messages.dashboard_section_household'),
            'subtitle' => __('messages.dashboard_section_household_subtitle'),
            'stats' => $this->householdStats($actor),
            'link' => route('admin.members.index'),
            'link_label' => __('messages.dashboard_manage_household'),
        ];
    }

    /**
     * @return array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string}|null
     */
    private function myPaymentsSection(User $actor): ?array
    {
        if (! $this->myPayments->canView($actor)) {
            return null;
        }

        $outstanding = $this->memberOutstandingAmount($actor);
        $paidThisYear = (float) MaintenanceMonthlyEntry::query()
            ->where('main_member_id', $actor->id)
            ->whereYear('billing_month', Carbon::now()->year)
            ->sum('amount_paid');
        $pendingMonths = MaintenanceMonthlyEntry::query()
            ->where('main_member_id', $actor->id)
            ->whereIn('status', $this->unpaidStatuses())
            ->count();

        return [
            'key' => 'my_payments',
            'title' => __('messages.dashboard_section_payments'),
            'subtitle' => __('messages.dashboard_section_payments_subtitle'),
            'stats' => [
                [
                    'key' => 'outstanding',
                    'label' => __('messages.dashboard_stat_outstanding'),
                    'value' => $this->fundSettings->formatMoney($outstanding),
                    'hint' => __('messages.dashboard_stat_outstanding_hint'),
                    'tone' => $outstanding > 0 ? 'expense' : 'income',
                ],
                [
                    'key' => 'paid_year',
                    'label' => __('messages.dashboard_stat_paid_year'),
                    'value' => $this->fundSettings->formatMoney($paidThisYear),
                    'hint' => __('messages.dashboard_stat_paid_year_hint', ['year' => (string) Carbon::now()->year]),
                    'tone' => 'income',
                ],
                [
                    'key' => 'pending_months',
                    'label' => __('messages.dashboard_stat_pending_months'),
                    'value' => $pendingMonths,
                    'hint' => __('messages.dashboard_stat_pending_months_hint'),
                    'tone' => $pendingMonths > 0 ? 'danger' : 'primary',
                ],
            ],
            'link' => route('admin.finance.my-payments.index'),
            'link_label' => __('messages.finance_my_payments_view'),
        ];
    }

    /**
     * @return array{key: string, title: string, subtitle: ?string, stats: list<array<string, mixed>>, link: ?string, link_label: ?string}|null
     */
    private function residentSection(User $actor): ?array
    {
        if (! $actor->isFamilyMember() && ! $actor->isRentalMember()) {
            return null;
        }

        if ($actor->isMainMember() || $actor->isSuperAdmin()) {
            return null;
        }

        $mainMember = $actor->mainMember;

        return [
            'key' => 'resident',
            'title' => __('messages.dashboard_section_resident'),
            'subtitle' => __('messages.dashboard_resident_linked', [
                'name' => $mainMember?->fullName() ?? '—',
            ]),
            'stats' => [
                [
                    'key' => 'membership',
                    'label' => __('messages.members_type'),
                    'value' => $actor->membership_type
                        ? MembershipRole::from($actor->membership_type)->shortForm()
                        : '—',
                    'hint' => $actor->membership_type
                        ? MembershipRole::from($actor->membership_type)->label()
                        : null,
                    'tone' => 'primary',
                ],
                [
                    'key' => 'main_member',
                    'label' => __('messages.members_main_member'),
                    'value' => $mainMember?->fullName() ?? '—',
                    'hint' => null,
                    'tone' => 'accent',
                ],
                [
                    'key' => 'house',
                    'label' => __('messages.users_house'),
                    'value' => $actor->houseLabel() ?? $mainMember?->houseLabel() ?? '—',
                    'hint' => null,
                    'tone' => 'income',
                ],
            ],
            'link' => route('admin.profile'),
            'link_label' => __('messages.dashboard_view_profile'),
        ];
    }

    /**
     * @return list<array{label: string, route: string, tone: string, description: string}>
     */
    private function quickActions(User $actor): array
    {
        $actions = [];

        if ($actor->can('users_all.read')) {
            $actions[] = [
                'label' => __('messages.users_all'),
                'route' => route('admin.users.index'),
                'tone' => 'primary',
                'description' => __('messages.dashboard_stat_users_access_hint'),
            ];
        }

        if ($actor->can('users_add.create')) {
            $actions[] = [
                'label' => __('messages.users_add'),
                'route' => route('admin.users.create'),
                'tone' => 'accent',
                'description' => __('messages.dashboard_quick_desc_users_add'),
            ];
        }

        if ($actor->isMainMember() || $actor->can('members_all.read')) {
            $actions[] = [
                'label' => __('messages.members_all'),
                'route' => route('admin.members.index'),
                'tone' => 'income',
                'description' => __('messages.dashboard_stat_members_access_hint'),
            ];
        }

        if ($actor->can('finance_overview.read')) {
            $actions[] = [
                'label' => __('messages.finance_overview'),
                'route' => route('admin.finance.index'),
                'tone' => 'expense',
                'description' => __('messages.dashboard_quick_desc_finance_overview'),
            ];
        } elseif ($actor->can('finance_collections.read')) {
            $actions[] = [
                'label' => __('messages.finance_collections'),
                'route' => route('admin.finance.collections.index'),
                'tone' => 'expense',
                'description' => __('messages.dashboard_stat_collections_month_hint'),
            ];
        } elseif ($actor->can('finance_maintenance_ledger.read')) {
            $actions[] = [
                'label' => __('messages.finance_maintenance_ledger'),
                'route' => route('admin.finance.maintenance-ledger.index'),
                'tone' => 'expense',
                'description' => __('messages.dashboard_stat_pending_maintenance_hint'),
            ];
        }

        if ($this->myPayments->canView($actor)) {
            $actions[] = [
                'label' => __('messages.finance_my_payments'),
                'route' => route('admin.finance.my-payments.index'),
                'tone' => 'income',
                'description' => __('messages.finance_my_payments_subtitle'),
            ];
        }

        if ($actor->can('settings_permissions.read')) {
            $actions[] = [
                'label' => __('messages.settings_permissions'),
                'route' => route('admin.settings.permissions.index'),
                'tone' => 'primary',
                'description' => __('messages.permissions_subtitle'),
            ];
        }

        if ($actor->can('settings_roles.read')) {
            $actions[] = [
                'label' => __('messages.settings_roles'),
                'route' => route('admin.settings.roles.index'),
                'tone' => 'accent',
                'description' => __('messages.dashboard_quick_desc_settings_roles'),
            ];
        }

        $actions[] = [
            'label' => __('messages.dashboard_view_profile'),
            'route' => route('admin.profile'),
            'tone' => 'neutral',
            'description' => __('messages.dashboard_stat_profile_complete_hint'),
        ];

        return $actions;
    }

    /**
     * @return list<array{tone: string, message: string, action_route: ?string, action_label: ?string}>
     */
    private function alerts(User $actor): array
    {
        $alerts = [];

        if ($actor->isSuperAdmin() && ! $this->fundSettings->current()) {
            $alerts[] = [
                'tone' => 'warning',
                'message' => __('messages.dashboard_alert_fund_not_configured'),
                'action_route' => route('admin.finance.fund-setting.show'),
                'action_label' => __('messages.finance_fund_setting_configure'),
            ];
        }

        if ($actor->isMainMember()) {
            $outstanding = $this->memberOutstandingAmount($actor);

            if ($outstanding > 0) {
                $alerts[] = [
                    'tone' => 'warning',
                    'message' => __('messages.dashboard_alert_outstanding', [
                        'amount' => $this->fundSettings->formatMoney($outstanding),
                    ]),
                    'action_route' => route('admin.finance.my-payments.index'),
                    'action_label' => __('messages.finance_my_payments_view'),
                ];
            }

            if ($this->profileCompleteness($actor) < 80) {
                $alerts[] = [
                    'tone' => 'info',
                    'message' => __('messages.dashboard_alert_profile_incomplete', [
                        'percent' => (string) $this->profileCompleteness($actor),
                    ]),
                    'action_route' => route('admin.profile'),
                    'action_label' => __('messages.dashboard_complete_profile'),
                ];
            }
        }

        if ($actor->can('finance_maintenance_ledger.read') || $actor->can('finance.read')) {
            $pending = $this->pendingMaintenanceCount();

            if ($pending > 0) {
                $alerts[] = [
                    'tone' => 'warning',
                    'message' => __('messages.dashboard_alert_pending_maintenance', [
                        'count' => (string) $pending,
                    ]),
                    'action_route' => route('admin.finance.maintenance-ledger.index'),
                    'action_label' => __('messages.dashboard_open_ledger'),
                ];
            }
        }

        if ($actor->hasCommitteeRole() && ! $actor->isSuperAdmin()) {
            $alerts[] = [
                'tone' => 'info',
                'message' => __('messages.dashboard_alert_committee_role', [
                    'role' => $actor->roleLabel(),
                ]),
                'action_route' => $this->primaryCommitteeRoute($actor),
                'action_label' => __('messages.dashboard_view_my_modules'),
            ];
        }

        return $alerts;
    }

    private function shouldShowSocietyStats(User $actor): bool
    {
        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($actor->hasCommitteeRole() && ($actor->can('users_all.read') || $actor->can('members_all.read'))) {
            return true;
        }

        return $actor->hasCommitteeRole();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function societyStats(User $actor): array
    {
        $counts = User::query()
            ->where('role', '!=', UserRole::SuperAdmin->value)
            ->selectRaw('COUNT(*) as total_users')
            ->selectRaw('SUM(CASE WHEN membership_type = ? THEN 1 ELSE 0 END) as main_members', [MembershipRole::MainMember->value])
            ->selectRaw('SUM(CASE WHEN membership_type = ? THEN 1 ELSE 0 END) as family_members', [MembershipRole::FamilyMember->value])
            ->selectRaw('SUM(CASE WHEN membership_type = ? THEN 1 ELSE 0 END) as rental_members', [MembershipRole::RentalMember->value])
            ->selectRaw('SUM(CASE WHEN committee_role IS NOT NULL THEN 1 ELSE 0 END) as committee_members')
            ->first();

        $stats = [
            [
                'key' => 'total_users',
                'label' => __('messages.dashboard_stat_total_users'),
                'value' => (int) ($counts->total_users ?? 0),
                'hint' => __('messages.dashboard_stat_total_users_hint'),
                'tone' => 'primary',
                'icon' => 'users',
            ],
            [
                'key' => 'main_members',
                'label' => __('messages.dashboard_stat_main_members'),
                'value' => (int) ($counts->main_members ?? 0),
                'hint' => __('messages.dashboard_stat_main_members_hint'),
                'tone' => 'income',
                'icon' => 'main-member',
            ],
            [
                'key' => 'family_members',
                'label' => __('messages.dashboard_stat_family_members'),
                'value' => (int) ($counts->family_members ?? 0),
                'hint' => __('messages.dashboard_stat_family_members_hint'),
                'tone' => 'accent',
                'icon' => 'family',
            ],
            [
                'key' => 'rental_members',
                'label' => __('messages.dashboard_stat_rental_members'),
                'value' => (int) ($counts->rental_members ?? 0),
                'hint' => __('messages.dashboard_stat_rental_members_hint'),
                'tone' => 'neutral',
                'icon' => 'rental',
            ],
        ];

        if ($actor->isSuperAdmin() || $actor->hasCommitteeRole()) {
            $stats[] = [
                'key' => 'committee_members',
                'label' => __('messages.dashboard_stat_committee_members'),
                'value' => (int) ($counts->committee_members ?? 0),
                'hint' => __('messages.dashboard_stat_committee_members_hint'),
                'tone' => 'expense',
                'icon' => 'committee',
            ];
        }

        return $stats;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function householdStats(User $actor): array
    {
        $household = $this->householdCounts($actor);
        $profileComplete = $this->profileCompleteness($actor);

        return [
            [
                'key' => 'my_house',
                'label' => __('messages.dashboard_stat_my_house'),
                'value' => $actor->houseLabel() ?? '—',
                'hint' => null,
                'tone' => 'primary',
            ],
            [
                'key' => 'my_family_members',
                'label' => __('messages.dashboard_stat_my_family'),
                'value' => $household['family'],
                'hint' => __('messages.dashboard_stat_my_family_hint'),
                'tone' => 'income',
            ],
            [
                'key' => 'my_rental_members',
                'label' => __('messages.dashboard_stat_my_rental'),
                'value' => $household['rental'],
                'hint' => __('messages.dashboard_stat_my_rental_hint'),
                'tone' => 'accent',
            ],
            [
                'key' => 'profile_complete',
                'label' => __('messages.dashboard_stat_profile_complete'),
                'value' => $profileComplete.'%',
                'hint' => __('messages.dashboard_stat_profile_complete_hint'),
                'tone' => $profileComplete >= 80 ? 'income' : 'neutral',
            ],
        ];
    }

    /**
     * @return array{family: int, rental: int, total: int}
     */
    private function householdCounts(User $actor): array
    {
        $rows = User::query()
            ->where('linked_main_member_id', $actor->id)
            ->whereIn('membership_type', [
                MembershipRole::FamilyMember->value,
                MembershipRole::RentalMember->value,
            ])
            ->select('membership_type', DB::raw('COUNT(*) as total'))
            ->groupBy('membership_type')
            ->pluck('total', 'membership_type');

        $family = (int) ($rows[MembershipRole::FamilyMember->value] ?? 0);
        $rental = (int) ($rows[MembershipRole::RentalMember->value] ?? 0);

        return [
            'family' => $family,
            'rental' => $rental,
            'total' => $family + $rental,
        ];
    }

    private function profileCompleteness(User $user): int
    {
        $checks = [
            filled($user->first_name),
            filled($user->last_name),
            filled($user->email),
            filled($user->username),
            filled($user->mobile_number),
            $user->gender !== null,
            $user->house_type !== null,
            filled($user->house_number),
            filled($user->profile_image_path),
            filled($user->id_proof_path),
        ];

        return (int) round((count(array_filter($checks)) / count($checks)) * 100);
    }

    private function pendingMaintenanceCount(): int
    {
        return MaintenanceMonthlyEntry::query()
            ->whereHas('mainMember')
            ->whereIn('status', $this->unpaidStatuses())
            ->count();
    }

    /**
     * @return list<string>
     */
    private function unpaidStatuses(): array
    {
        return [
            MaintenanceMonthEntryStatus::Pending->value,
            MaintenanceMonthEntryStatus::Due->value,
            MaintenanceMonthEntryStatus::Partial->value,
        ];
    }

    private function memberOutstandingAmount(User $actor): float
    {
        return (float) MaintenanceMonthlyEntry::query()
            ->where('main_member_id', $actor->id)
            ->whereIn('status', $this->unpaidStatuses())
            ->get()
            ->sum(fn (MaintenanceMonthlyEntry $entry) => max(0, (float) $entry->charge_amount - (float) $entry->amount_paid));
    }

    private function primaryCommitteeRoute(User $actor): ?string
    {
        if ($actor->can('finance_collections.read')) {
            return route('admin.finance.collections.index');
        }

        if ($actor->can('finance_maintenance_ledger.read')) {
            return route('admin.finance.maintenance-ledger.index');
        }

        if ($actor->can('users_all.read')) {
            return route('admin.users.index');
        }

        if ($actor->can('members_all.read')) {
            return route('admin.members.index');
        }

        return route('admin.profile');
    }

    private function firstFinanceRoute(User $actor): ?string
    {
        if ($actor->can('finance_collections.read')) {
            return route('admin.finance.collections.index');
        }

        if ($actor->can('finance_expenses.read')) {
            return route('admin.finance.expenses.index');
        }

        if ($actor->can('finance_maintenance_ledger.read')) {
            return route('admin.finance.maintenance-ledger.index');
        }

        return null;
    }
}
