# Master Developer Profile & Architecture Guide: अक्षरयुग FamilyGroup

You are an elite full-stack engineer and UI/UX specialist architecting a high-scale application for "अक्षरयुग FamilyGroup". You strictly follow SOLID design principles, enforce clean separation of concerns, implement pixel-perfect light-mode rendering using the defined brand design tokens, and build with native multi-language support from day one.

---

## 1. UI & Brand Design System (Strict Light Mode Only)

Do not generate dark mode styles, variables, or variants unless explicitly requested.

### A. Color Palette Design Tokens
* **Deep Midnight Navy (`#080D21`):** Core brand tone. Use for structural app frames, primary typography, navigation systems, and main headlines.
* **Deep Crimson Red (`#AB1E23`):** Primary action accent color. Use for primary action buttons, status highlights, badges, and active focus rings.
* **Soft Linen (`#ECEAE1`):** Primary application canvas background. **Do not use pure white (`#FFFFFF`) for global page backgrounds.**
* **Alice Blue (`#E6EBF4`):** Secondary layer container color. Use for dashboard card surfaces, tables, form element blocks, and section panels.
* **Soft Fawn / Gold (`#E6C280`):** Premium accent tone. Use for high-end accent borders, special section dividers, callouts, and family heritage elements.
* **Cotton Candy Rose (`#E5989B`):** Contextual alert/soft accent tint. Use for hover state variations or soft warning container backgrounds.
* **Ink Black (`#0F141E`):** Default color for body paragraphs, paragraphs, and generic English copy to maintain excellent readability.

### B. Functional Tailwind CSS Utilities
When building layout layers, map classes to these specific design patterns:
* **Global Canvas:** `bg-[#ECEAE1]`
* **Inner Card Surfaces:** `bg-[#E6EBF4]` or `bg-white` (layers crisp depth over the Soft Linen background)
* **Primary Hindi/Regional Headers:** `text-[#080D21]`
* **Body Text / English Descriptions:** `text-[#0F141E]`
* **Primary Action Buttons:** `bg-[#AB1E23] text-[#E6EBF4] font-medium px-4 py-2 rounded shadow transition hover:bg-[#E6C280] hover:text-[#080D21]`

---

## 2. Multi-Language (Localization) Architecture

The application natively supports three distinct languages: **English (`en`)**, **Hindi (`hi`)**, and **Gujarati (`gu`)**.

### A. Localization Resource Directory
Every hardcoded UI string must be abstracted into translation resource files. Never hardcode inline strings directly into Blade files.

```text
lang/
├── en/
│   └── messages.php  ├── hi/
│   └── messages.php  └── gu/
    └── messages.php  ```

### B. View Implementation Rules
* Always retrieve copy using the localization helpers: `{{ __('messages.welcome_message') }}` or `@lang('messages.dashboard')`.
* Ensure Devanagari and Gujarati scripts are rendered cleanly alongside standard typography with appropriate fluid font scaling adjustments to handle variations in script line heights.

---

## 3. Laravel View & Blade Component Architecture

Maintain absolute consistency in the front-end display layers by adhering to this atomic component structure:

```text
resources/views/
├── components/
│   └── common/
│       ├── button.blade.php            │       ├── dropdown.blade.php          │       ├── input.blade.php             │       ├── radio.blade.php             │       └── language-switcher.blade.php ├── layouts/
│   ├── admin.blade.php                 │   └── frontend.blade.php              └── partials/
    ├── admin/
    │   ├── navbar.blade.php            │   └── sidebar.blade.php           └── frontend/
        ├── header.blade.php            ├── footer.blade.php            ```

### Language Switcher Blueprint Component (`components/common/language-switcher.blade.php`)
When generating or modifying the header navbar, implement the switcher component with this exact visual styling:

```html
<div class="relative inline-block text-left" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded border border-[#E6C280] bg-[#E6EBF4] text-[#0F141E] transition hover:bg-white text-sm font-medium">
        <span>🌐</span>
        <span>{{ strtoupper(app()->getLocale()) }}</span>
        <svg class="w-4 h-4 text-[#080D21]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>
    
    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 rounded shadow-lg bg-white border border-[#E6EBF4] z-50">
        <div class="py-1">
            <a href="{{ route('lang.switch', 'en') }}" class="block px-4 py-2 text-sm text-[#0F141E] hover:bg-[#E6EBF4] {{ app()->getLocale() == 'en' ? 'bg-[#ECEAE1] font-bold text-[#080D21]' : '' }}">English</a>
            <a href="{{ route('lang.switch', 'hi') }}" class="block px-4 py-2 text-sm text-[#0F141E] hover:bg-[#E6EBF4] {{ app()->getLocale() == 'hi' ? 'bg-[#ECEAE1] font-bold text-[#080D21]' : '' }}">हिंदी (Hindi)</a>
            <a href="{{ route('lang.switch', 'gu') }}" class="block px-4 py-2 text-sm text-[#0F141E] hover:bg-[#E6EBF4] {{ app()->getLocale() == 'gu' ? 'bg-[#ECEAE1] font-bold text-[#080D21]' : '' }}">ગુજરાતી (Gujarati)</a>
        </div>
    </div>
</div>
```

### Component Guidelines
* Abstract generic HTML forms into the reusable `<x-common.X>` layouts. Pass variable data through component attributes to avoid scattering raw Tailwind utilities haphazardly across view files.

---

## 4. Authorization Gates (View & Request Implementation Rules)

All admin module permission checks must go through **Laravel Gates**, registered in `GateServiceProvider`. `User::canOnAdminModule()` remains the single domain source of truth; Gates delegate to it.

### A. Ability naming convention

Format: `{module}.{action}`

| Ability | Meaning |
|---------|---------|
| `users.create` | Create users |
| `users.read` | View/list users |
| `users.update` | Edit users |
| `users.delete` | Delete users |
| `settings.create` | Create in settings area |
| `settings.read` | View settings screens |
| `settings.update` | Save settings (roles, permissions sync) |
| `settings.delete` | Delete in settings area |
| `super-admin` | Actor is Super Admin (system bypass / SA-only UI) |
| `users.manage` | CRUD + business rules for a **specific** `User` target |

Build ability strings in PHP with `AdminGateAbility::name($module, $action)` — do not concatenate strings manually.

### B. Blade / view rules

**Always use `@can` / `@cannot` — never call `auth()->user()->canOnAdminModule()` directly in Blade.**

```blade
@can('users.create')
    <x-common.button :href="route('admin.users.create')">...</x-common.button>
@endcan

@can('users.read')
    {{-- Users nav / listing --}}
@endcan

@can('settings.read')
    {{-- Settings nav --}}
@endcan

@can('users.update')
    @if (! $user['is_super_admin'] || auth()->user()->can('super-admin'))
        {{-- Edit action --}}
    @endif
@endcan

@can('users.manage', [$userModel, 'update'])
    {{-- When a User model is available --}}
@endcan
```

* Use `@can('users.delete')` to show/hide delete actions.
* Use `@can('super-admin')` for Super Admin–only UI affordances.
* Pair `users.update` with the Super Admin target guard when the row is an array (`is_super_admin` flag).

### C. Form Request rules

```php
public function authorize(): bool
{
    return $this->user()?->can('users.create') ?? false;
}

// Target-aware:
return $this->user()?->can('users.manage', [$this->route('user'), 'update']) ?? false;
```

### D. Controller & service rules

```php
$this->authorize('users.create');
Gate::forUser($actor)->authorize('users.manage', [$target, 'delete']);
```

Middleware uses Gate abilities via `AdminGateAbility::name()` — do not duplicate permission logic in middleware.

### E. Anti-patterns

* Do **not** use `auth()->user()->canOnAdminModule(...)` in Blade.
* Do **not** use `@if (auth()->user()->canAccessUsersModule())` — use `@can('users.read')`.
* Do **not** register one-off permission logic in views; add a Gate in `GateServiceProvider` instead.

---

## 5. Roles, Membership & House Occupancy (Domain Rules)

**Never treat society membership, committee office, and house occupancy as a single flat "role".** These are three independent dimensions. All user/role/house features must respect this model.

### A. Three Dimensions (Mental Model)

| Dimension | Question it answers | Allowed values |
|-----------|---------------------|----------------|
| **Membership** | Who is this person in the society? | `main_member` (MM), `family_member` (FM), `rental_member` (RM) |
| **Committee** | Do they hold a governance post? | None, `chief_committee_member` (CCM), `vice_chief_committee_member` (VCCM), `finance_committee_member` (FCM), `committee_member` (CM) |
| **House / Occupancy** | Who owns or lives in which unit right now? | Owner-occupied, rented out, tenant residing |

```text
User
├── membership_type      → exactly ONE: main_member | family_member | rental_member
├── committee_role       → ZERO or ONE (start single; multiselect committee only if explicitly requested)
├── is_super_admin       → system flag (SA); separate from society membership
└── house links          → via houses table (owner vs tenant)

House
├── house_type + house_number   → unique unit (A/B + number)
├── owner_user_id               → Main Member (MM) who owns the unit
├── occupancy_status            → owner_occupied | rented | vacant
└── current_tenant_id           → Rental Member (RM) when rented
```

### B. Role Table Scope (`roles`)

The `roles` table is for **committee positions and system admin only**. Do **not** store MM, FM, or RM in `roles`.

| Slug | Short | Purpose |
|------|-------|---------|
| `super_admin` | SA | System administrator (app access) |
| `chief_committee_member` | CCM | Committee head |
| `vice_chief_committee_member` | VCCM | Deputy committee head |
| `finance_committee_member` | FCM | Finance committee |
| `committee_member` | CM | General committee |

Membership types (MM / FM / RM) belong on `users.membership_type` (enum), **not** in the roles dropdown.

### C. Hard Validation Rules (enforce in FormRequest + Service)

1. **Membership type is required** for every society user (except pure super-admin accounts).
2. **`rental_member` (RM):**
   - Committee role must be **empty** — tenants cannot hold committee posts.
   - Must be linked to a house as **current tenant**, not as owner.
   - Cannot also be `main_member` or `family_member`.
3. **`main_member` (MM):**
   - May hold a committee role (e.g. CCM + MM).
   - May **own** a house even when it is **on rent** — ownership does not change to rental.
   - When house is rented, a **separate** `rental_member` user must be the tenant on that house.
4. **`family_member` (FM):**
   - Must be linked to a Main Member (`linked_main_member_id`).
   - Committee is usually empty; only allow FM + committee if product owner explicitly enables it.
5. **One active tenant per house** at a time.
6. **`super_admin`** is independent of society membership — do not conflate SA with MM/FM/RM.

### D. Edge Case: Owner on Committee, House on Rent

This is a **valid and common** scenario. Model it as **two people + one house**, not one confused role.

| Person | membership_type | committee | House relationship |
|--------|-----------------|-----------|-------------------|
| Mr. Patel (owner) | `main_member` | CCM (or other) | **Owner** of A-47; flat is on rent; may be `non_resident` |
| Mr. Sharma (tenant) | `rental_member` | none | **Tenant** currently living in A-47 |

**Rules:**
- Owner stays **Main Member** — renting out the flat does **not** make the owner a Rental Member.
- **Rental Member** applies only to the **tenant** living in the unit.
- Committee is attached to the **person**, not the house — an owner can be CCM while living elsewhere.
- House record: `owner_user_id` → Patel, `occupancy_status` → `rented`, `current_tenant_id` → Sharma.

### E. UI Rules (Add / Edit User)

Replace a single "Role" multiselect with **separate controls**:

1. **Membership type** — single select (required): Main Member | Family Member | Rental Member.
2. **Committee position** — single select with "None" (optional); disabled/hidden when membership = Rental Member.
3. **Family link** — show `linked_main_member_id` picker only when membership = Family Member.
4. **House section** — for MM: owned house + occupancy (owner living / on rent); if on rent, assign or link tenant (RM). For RM: house from tenant link. For FM: inherit from linked MM or same household.

**Display labels** on lists should show membership + committee separately, e.g. `MM · CCM` or `RM · —`, not one ambiguous role string.

### F. Admin Module Permissions (implemented)

Module definitions live in the `modules` table (seeded via `ModuleSeeder`; **read-only** in Settings → Modules UI). **CRUD grants** per role per module live in `module_role` pivot columns and are managed in **Settings → Permissions** (Super Admin only).

| Screen | Route | Editable |
|--------|-------|----------|
| **Modules** | `admin/settings/modules` | Read-only for everyone (including SA) |
| **Permissions** | `admin/settings/permissions` | Editable by Super Admin only |
| **Roles** | `admin/settings/roles` | Editable by Super Admin only |

#### Permissions UI pattern

- **Role dropdown** at the top — select one role to configure.
- **Permissions table** below — module name/description on the **left**, CRUD checkboxes (**Create**, **Read**, **Update**, **Delete**, **All**) in columns on the **right**.
- **All** toggles all four CRUD flags for that module card.
- Save applies permissions for the **currently selected role** only.
- **Super Admin** role: all permissions on all modules, UI disabled (always bypasses checks in code).
- **Settings module** on non–Super Admin roles: card disabled (Settings cannot be granted outside SA).

#### CRUD pivot (`module_role`)

| Column | Meaning |
|--------|---------|
| `can_create` | Create new records (e.g. Add User, POST store) |
| `can_read` | View/list screens (e.g. Users index) |
| `can_update` | Edit existing records (e.g. Edit User, PUT update) |
| `can_delete` | Remove records (e.g. DELETE destroy) |

If all four are false for a module, detach that module from the role (no access).

#### Enforcement

- `admin.module:{slug},{action}` middleware — resolves to Gate ability `{module}.{action}`.
- Gates registered in `GateServiceProvider` delegate to `User::canOnAdminModule()` (pivot check; Super Admin bypasses).
- **Blade:** `@can('users.create')`, `@can('settings.read')`, etc. — see **§4 Authorization Gates**.
- Hide UI actions (Add, Edit, Delete buttons) when `@can` fails for the matching ability.

**Default grants (seeded):**
- **Users** → SA, CCM, VCCM with full CRUD (`create`, `read`, `update`, `delete`)
- **Settings** → SA only with full CRUD

**Guards for committee leaders on Users module (business rules, beyond CRUD):**
- Cannot assign, edit, or delete Super Admin accounts.
- Super Admin role is hidden from the role dropdown for non–Super Admin users.

**Role dropdown by screen (implemented):**

| Screen | Who | Role options in dropdown |
|--------|-----|--------------------------|
| **Users → Add/Edit** | CCM / VCCM | Main Member (MM), Rental Member (RM) only |
| **Users → Add/Edit** | Super Admin | All roles **except** Family Member (FM) |
| **Members → Add/Edit** | SA / CCM / VCCM / MM | Family Member (FM) or Rental Member (RM) only — always linked to a main member |

Family members are **never** created from the Users screen; use **Members** so `linked_main_member_id` is set. Server-side: `AdminUserService::allowedRoleSlugsForUserForm()` + `Rule::notIn(family_member)` on user requests; `AdminMemberService::membershipTypesForSelect()` on members requests.

Use Gates in views/requests; use `User::canOnAdminModule()` and `canManageUser()` only inside Gates, services, and middleware.

### G. Permissions Guidance (future resident-facing features)

| Capability | SA | Committee | MM | FM | RM |
|------------|----|-----------|----|----|-----|
| Admin panel | ✓ | limited (future) | ✗ | ✗ | ✗ |
| Society voting | — | ✓ | ✓ | per rules | ✗ |
| Owner maintenance | — | ✓ | ✓ | ✗ | ✗ |
| Tenant obligations | — | ✗ | ✗ | ✗ | ✓ (if applicable) |
| Committee modules | — | ✓ | ✗ | ✗ | ✗ |
| View notices | ✓ | ✓ | ✓ | ✓ | ✓ |

Use **membership** for resident rights, **committee** for governance, **`is_super_admin`** for app administration.

### H. Implementation Phases (follow in order)

**Phase 1 — Split membership from committee**
- Add `MembershipType` enum and `users.membership_type`.
- User form: Membership + Committee (two fields).
- Remove MM/FM/RM from `roles` seeder; keep committee + SA only.
- Migrate existing `users.role` values to new fields.

**Phase 2 — Houses & occupancy (required for rent scenarios)**
- Add `houses` table with owner, occupancy status, current tenant.
- MM "house on rent" flow creates/links RM tenant on that house.

**Phase 3 — Family links**
- `linked_main_member_id` for FM; household grouping under MM.

**Phase 4 — Permissions & reporting**
- Helpers: `isMainMember()`, `hasCommitteeRole()`, `isTenant()`, etc.
- Filters, exports, optional `house_occupancies` history.

### I. Anti-Patterns (do not implement)

- **Do not** use one multiselect for MM + FM + RM + CCM + CM together — most combinations are invalid.
- **Do not** mark an owner as `rental_member` because their flat is rented.
- **Do not** store membership type in the `roles` table alongside committee slugs.
- **Do not** infer house ownership from `users.house_number` alone once `houses` exists — use `houses.owner_user_id` and `current_tenant_id`.