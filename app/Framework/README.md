# Ravion Framework V1

The Ravion Framework is the internal application framework built on top of Laravel.

Its purpose is to standardize common ERP behavior across all modules.

## Core Layers

### Controllers

Base controllers for common module patterns.

- BaseMasterController
- Future: BaseReportController
- Future: BaseDashboardController

### Services

Reusable business/application services.

- MasterCrudService
- MasterQueryService
- MasterValidationService
- ResourceService

### Traits

Reusable controller behaviors.

- MasterAuditTrait
- PermissionTrait

### Resources

Configuration objects that describe how a module behaves.

- MasterResource

### Theme

Centralized theme and appearance control.

- ThemeManager

### Support

Helpers for common UI/backend patterns.

- BreadcrumbBuilder
- ActionMenuBuilder
- ResourceBuilder

## Current Rule

Do not over-abstract too early.

A framework feature should be created only when at least two modules need the same pattern.

## Current Pilot Modules

- Activity Division
- Activity
- Work Stage

## Current Status

Framework V1 started.

Completed:
- MasterAuditTrait
- MasterCrudService

Next:
- MasterQueryService
- BaseMasterController