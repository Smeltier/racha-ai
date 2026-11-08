# Architecture Conventions

## Overview

Racha Aí uses a layered PHP architecture organized around HTTP controllers, application services, data access objects, and server-rendered views.

## Directory structure

```text
app/
├── Controllers/   HTTP request handlers
├── Models/        Domain models and DAOs
├── Services/      Application services and view DTOs
├── Http/          Routing, sessions, CSRF, and HTTP helpers
├── Exceptions/    Application and domain exceptions
└── views/         PHP templates
```

## Models

Models represent domain entities and enforce business invariants.

* live under `app/Models/{Domain}/`;
* must not depend on HTTP, sessions, controllers or templates;
* contain domain behavior, not just getters and setters;
* validate state transitions;
* expose named operations;
* use `create()` (static factory method) for new entities;
* use `reconstitute()` (static factory method) when rebuilding entities from persisted data;
* throw domain exceptions when an operation is invalid.

Models should protect their own invariants. A service should not manually
change model state when the model can provide a domain method for that
operation.

## Data Access Objects (DAOs)

Data Access Objects (DAOs) are responsible for persistence.

* live alongside their model under `app/Models/{Domain}/`;
* contain SQL queries and database mapping;
* convert database rows into domain models;
* convert domain models into database records;
* use prepared statements;
* manage transactions for multi-step persistence operations;
* do not contain authorization or business rules;
* do not depend on controllers, sessions, or views.

A DAO should return null when a single entity cannot be found. The service
layer decides whether that situation should become a domain exception.

## Services

Services coordinate application use cases.

* live under `app/Services/`;
* receive DAOs and other dependencies through constructor injection;
* load models from DAOs;
* perform application-level authorization checks;
* invoke domain methods on models;
* persist the resulting changes through DAOs;
* expose methods named after use cases;
* must not read `$_POST`, manipulate headers, render HTML, or access templates.

Authorization that depends on the current user belongs in the service layer.

Services should let domain and application exceptions propagate to the
controller. The controller is responsible for choosing the HTTP response.

## Controllers

Controllers adapt HTTP requests to application use cases.

* live under `app/Controllers/`;
* receive services through constructor injection;
* require authentication when necessary;
* read route parameters and form input;
* call the appropriate service method;
* translate exceptions into HTTP responses or form errors;
* render views with explicit data;
* redirect after successful POST requests;
* must not contain SQL or domain rules.

`BaseController` provides shared HTTP behavior such as:

* requiring an authenticated user;
* reading form input;
* rendering a view;
* redirecting to another route.

Controllers should remain thin. A controller should answer “which use case
should run?” rather than “how does this business rule work?”

## Views

Views are PHP templates located under `app/views/`.

* receive data from controllers;
* generate HTML;
* contain only simple presentation logic;
* must not query the database;
* must not call services or DAOs;
* must escape dynamic output with `e()`;
* must include `csrf_field()` in POST forms;
* should reuse shared layout files from `views/partials/`.

## HTTP and routing

Routes are declared in `config/routes.php`.

The router:

1. matches the HTTP method and URL pattern;
2. validates CSRF tokens for POST requests;
3. creates the appropriate controller;
4. passes route parameters to the controller action.

Controllers should not implement route matching or CSRF validation directly.

## Error handling

Use exceptions to represent invalid operations and missing resources.

Controllers are responsible for converting these exceptions into:

* validation messages rendered in the current form;
* redirects;
* 404, 403, or other appropriate HTTP responses.

Services and models should not generate HTML or HTTP responses.

## View DTOs

Some screens need data composed from multiple models. In those cases, the
service may return a read-only view DTO.

* live under `app/Services/`;
* should be `readonly`;
* contain data required by a specific view;
* may combine data from multiple models;
* should not contain database access or presentation logic.
