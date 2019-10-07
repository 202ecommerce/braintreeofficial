---
name: Migration
category: Admin Controllers
---

## Definition

This controller servs for migration the data from paypal (if they exist).


The process of the migration compose of 3 steps.
- Offre to do migration. On this step you can skip migration.
- After migration of the data, you need set the missing credentials (such merchant account ID)
- Third step display the results of the migration

####  getStepOne()
This method returns html for the first step.

####  getStepTwo()
This method returns html for the second step.

####  getStepThree()
This method returns html for the third step.

####  doMigration()
This method manages the migration

#### doBackupTables()
This method is responsible for creating of the backup of the data before their importing

#### doMigrateMerchantAccountIdCurrency()
This method does the migration of the merchant account IDs
