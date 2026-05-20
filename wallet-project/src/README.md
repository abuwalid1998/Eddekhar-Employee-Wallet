# Employee Wallet API

REST API for employees, wallets, transfers, withdrawals, payroll events, and auth.

## Base URL

```text
/api
```

## Authentication

Protected routes require JWT:

```http
Authorization: Bearer <token>
```

---

## Endpoints (with request/response examples)

## Auth

### POST `/auth/sign-up`
**Request**
```json
{
  "email": "john@example.com",
  "password": "secret1234"
}
```

**Response (201)**
```json
{
  "message": "User registered successfully.",
  "data": {
    "user_id": 1,
    "email": "john@example.com",
    "token": "<jwt>",
    "token_type": "Bearer",
    "expires_in_minutes": 120
  }
}
```

### POST `/auth/sign-in` (same as `/auth/login`)
**Request**
```json
{
  "email": "john@example.com",
  "password": "secret1234"
}
```

**Response (200)**
```json
{
  "message": "Signed in successfully.",
  "data": {
    "user_id": 1,
    "email": "john@example.com",
    "token": "<jwt>",
    "token_type": "Bearer",
    "expires_in_minutes": 120
  }
}
```

## Health

### GET `/health`
**Response (200)**
```json
{
  "status": "healthy",
  "checks": {
    "database": true,
    "redis": true
  }
}
```

## Employees (JWT required)

### GET `/employees`
**Request query example**
```text
/employees?page=1
```

**Response (200)**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@company.com",
      "wallets": []
    }
  ],
  "links": {},
  "meta": {}
}
```

### POST `/employees`
**Request**
```json
{
  "name": "John Doe",
  "email": "john@company.com"
}
```

**Response (200)**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@company.com",
    "wallets": []
  }
}
```

### GET `/employees/{employee}`
**Response (200)**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@company.com",
    "wallets": [
      {
        "id": 10,
        "type": "salary",
        "currency": "USD"
      }
    ]
  }
}
```

## Wallets (JWT required)

### GET `/wallets`
**Request query example**
```text
/wallets?employee_id=1&currency=USD&page=1
```

**Response (200)**
```json
{
  "data": [
    {
      "id": 10,
      "employee_id": 1,
      "type": "salary",
      "currency": "USD",
      "available_balance": 0,
      "reserved_balance": 0,
      "status": "active",
      "created_at": "2026-05-20T08:00:00.000000Z"
    }
  ],
  "links": {},
  "meta": {}
}
```

### POST `/wallets`
**Request**
```json
{
  "employee_id": 1,
  "type": "salary",
  "currency": "usd"
}
```

**Response (201)**
```json
{
  "success": true,
  "message": "Wallet created successfully.",
  "wallet": {
    "id": 10,
    "employee_id": 1,
    "type": "salary",
    "currency": "USD",
    "available_balance": 0,
    "reserved_balance": 0,
    "status": "active",
    "created_at": "2026-05-20T08:00:00.000000Z"
  }
}
```

### GET `/wallets/{wallet}`
**Response (200)**
```json
{
  "data": {
    "id": 10,
    "employee_id": 1,
    "type": "salary",
    "currency": "USD",
    "available_balance": 0,
    "reserved_balance": 0,
    "status": "active",
    "created_at": "2026-05-20T08:00:00.000000Z"
  }
}
```

### GET `/wallets/{wallet}/transactions`
**Response (200)**
```json
{
  "data": [
    {
      "id": 100,
      "wallet_id": 10,
      "type": "credit",
      "amount": 10000,
      "status": "completed"
    }
  ],
  "links": {},
  "meta": {}
}
```

## Transfers (JWT + Idempotency-Key required)

### POST `/transfers`
Headers:
```text
Idempotency-Key: transfer-001
```

**Request**
```json
{
  "from_wallet_id": 10,
  "to_wallet_id": 11,
  "amount": 500,
  "description": "Internal transfer"
}
```

**Response (200)**
```json
{
  "message": "Transfer completed successfully.",
  "data": {
    "debit": {"id": 201, "wallet_id": 10, "type": "debit", "amount": 500},
    "credit": {"id": 202, "wallet_id": 11, "type": "credit", "amount": 500}
  }
}
```

## Withdrawals (JWT + Idempotency-Key required)

### POST `/withdrawals`
Headers:
```text
Idempotency-Key: withdrawal-001
```

**Request**
```json
{
  "wallet_id": 10,
  "amount": 1000,
  "description": "ATM withdrawal"
}
```

**Response (201)**
```json
{
  "data": {
    "id": 55,
    "wallet_id": 10,
    "amount": 1000,
    "status": "pending"
  }
}
```

## Payroll (JWT required)

### POST `/payroll/events`
**Request**
```json
{
  "event_id": "payroll-2026-05-20-001",
  "employee_id": 1,
  "amount": 350000,
  "currency": "USD",
  "description": "May salary"
}
```

**Response (201 or 200 for duplicate)**
```json
{
  "message": "Payroll event processed successfully.",
  "data": {
    "event_id": 77,
    "status": "processed"
  }
}
```
