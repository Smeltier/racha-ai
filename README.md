# Racha Aí
 
Split group expenses without the awkward math.
 
Racha Aí is a web application for groups of people who share costs, such as friends on a trip, roommates, or a team dinner. One person creates a group, any member records an expense, and the system works out how much each person owes.
 
**Status:** under development as the final project of the Web Development II course at CEFET-MG.
 
## Objective
 
Shared expenses usually end up in a messy spreadsheet or a chat full of "who paid for what?". Racha Aí replaces that with one place where:
 
* Every group keeps its own history of expenses.
* Each expense records who paid, who takes part in the split, and how much each participant owes.
* Balances are calculated from the recorded data, so nobody has to do the math by hand.
## Planned features (MVP)
 
* Account registration and login with sessions
* Groups with two roles: owner and member
* Member invitations with an expiration date and a status
* Expenses with an equal or a custom split
* Custom categories defined by each group
* Balance per member, calculated from the recorded expenses
* Paginated lists and date filters
Not in the first version: recording payments between members (settling up). It can be added later without changing the existing tables.
 
## Tech stack
 
* PHP 8.5
* MySQL
* HTML, CSS and JavaScript
* Composer with PSR 4 autoloading
* PHPUnit for automated tests
