
# Physio App API

This repository contains the backend API for the Physio App project


## Reference
You may find the main project on the following repository
 - [@geoxhon/physio_app](https://github.com/geoxhon/physio_app)


## Installation

Install by putting the php files on your php server

Import the database by importing the physioapp.sql file

Run the following script on your php server to install dependencies

```php
  php composer.phar update
```
    
## API Reference

#### Authenticate via username and password
```http
  POST /api/v1/auth/login
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `username` | `string` | **Required**. Your account username |
| `password` | `string` | **Required**. Your account password |

#### Authenticate via refresh token

```http
  POST /api/v1/auth/token/{token}/login
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `token`      | `string` | **Required**. Your refresh token. |

#### Register a new account

```http
  POST /api/v1/auth/register
```
Patient users cannot create accounts, therefore should not call this endpoint.
| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `username` | `string` | **Required**. A username for the new account. |
| `password` | `string` | **Required**. A password for the new account. **Must validate against /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'**|
| `email` | `string` | **Required**. An email for the new account. |
| `displayName` | `string` | **Required**. The name of the new account owner. |
| `SSN` | `string` | **Required**. The Social Security Number of the new account. |
| `address` | `string` | **Required**. The address of the new account owner. |

#### Logout

```http
  POST /api/v1/auth/logout
```

#### Get the user who created your account
Manager users do not have an owner, therefore should not call this endpoint.
```http
  GET /api/v1/me/creator
```
#### Get all the users you have registered
Patient users cannot create accounts, therefore should not call this endpoint.
```http
  GET /api/v1/me/children
```
#### Get your appointments
Manager users do not handle appointments, therefore should not call this endpoint.
```http
  GET /api/v1/appointments
```
#### Get all appointments that have been recorded by a doctor.
Manager users do not handle appointments, therefore should not call this endpoint.
```http
  GET /api/v1/appointments/history
```
#### Get reserved timestamps, useful for appointment registration.
Manager users do not handle appointments, therefore should not call this endpoint.
```http
  GET /api/v1/appointments/availability
```
#### Book a new appointment
Only patients may call this endpoint.
```http
  POST /api/v1/appointments/book
```
  | Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `timestamp`      | `int` | **Required**. The appointment date in timestamp format. |

#### Accept a pending appointment
Only doctors may call this endpoint.

Can only be called if appoinement status is pending.
```http
  POST /api/v1/appointments/{appointmentId}/accept
```
  | Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `appointmentId`      | `int` | **Required**. The appointment id. |

#### Cancel an appointment
Manager users do not handle appointments, therefore should not call this endpoint.

Cancelled appointments cannot be cancelled again.
```http
  POST /api/v1/appointments/{appointmentId}/cancel
```
  | Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `appointmentId`      | `int` | **Required**. The appointment id. |

#### Record a visit.
Only doctor users may call this endpoint.

Can only be called on appointments with accepted status.
```http
  POST /api/v1/appointments/{appointmentId}/record
```
  | Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `appointmentId`      | `int` | **Required**. The appointment id. |
| `serviceId`      | `string` | **Required**. The id of the service that was provided to the patient. |
| `details`      | `string` | **Required**. Any details about the appointment go here. |

#### Create service
Only managers users may call this endpoint.

```http
  POST /api/v1/services/create
```
  | Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `id`      | `string` | **Required**. An id for the new service |
| `name`      | `string` | **Required**. The name of the service. |
| `description`      | `string` | **Required**. The description of the service. |
| `cost`      | `int` | **Required**. The cost of the service. |

#### Get all services
```http
  GET /api/v1/services
```
