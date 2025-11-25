# Hospital Management System
## Lead Management & Payment System

A Laravel-based hospital appointment booking system with CCAvenue payment gateway integration for managing patient consultations and advance payments.

---

## 🚀 Features

- **Appointment Management**: Book health consultation appointments with patient details
- **Payment Integration**: CCAvenue payment gateway integration for advance payments
- **Payment Callbacks**: Secure payment callback handling and status updates
- **Status Tracking**: Track appointment and payment statuses in real-time
- **API-based**: RESTful API endpoints for seamless integration
- **Error Handling**: Comprehensive error handling and logging
- **Security**: Environment-based configuration for sensitive credentials

---

## 📋 Prerequisites

- PHP >= 8.1
- Composer
- MySQL/PostgreSQL
- Laravel 10.x or higher

---

## 🛠️ Installation & Setup

### 1. Clone the Repository
```bash
git clone https://github.com/Ashutoshxo/Hospital-management.git
cd Hospital-management
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
```

Update the `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hospital_system
DB_USERNAME=root
DB_PASSWORD=

# CCAvenue Configuration
CCAVENUE_MERCHANT_ID=your_merchant_id
CCAVENUE_ACCESS_CODE=your_access_code
CCAVENUE_WORKING_KEY=your_working_key
CCAVENUE_REDIRECT_URL=http://localhost:8000/api/payments/callback
CCAVENUE_CANCEL_URL=http://localhost:8000/payment/cancelled
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Start the Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

---

## 📡 API Endpoints

### 1. Create Appointment

**POST** `/api/appointments`

**Request Body:**
```json
{
    "patient_name": "John Doe",
    "patient_email": "john@example.com",
    "patient_phone": "9876543210",
    "consultant_type": "Cardiologist",
    "appointment_date": "2025-12-01 10:00:00",
    "consultation_fee": 500
}
```

**Response:** `201 Created`
```json
{
    "id": 1,
    "patient_name": "John Doe",
    "patient_email": "john@example.com",
    "status": "pending",
    "created_at": "2025-11-25T10:00:00.000000Z"
}
```

### 2. Initiate Payment

**POST** `/api/appointments/{id}/initiate-payment`

**Response:** `200 OK`
```json
{
    "payment_url": "https://ccavenue.com/payment",
    "payment_id": 1,
    "amount": 500,
    "order_id": "APT-1"
}
```

### 3. Payment Callback (CCAvenue)

**POST** `/api/payments/callback`

**Request Body:**
```json
{
    "transaction_id": "TXN123456789",
    "payment_status": "success",
    "amount": 500,
    "order_id": "APT-1"
}
```

**Response:** `200 OK`
```json
{
    "message": "Payment processed successfully",
    "appointment_status": "confirmed"
}
```

---

## 🗄️ Database Schema

### Appointments Table

| Column | Type | Description |
|--------|------|-------------|
| id | Primary key | Unique identifier |
| patient_name | String | Patient's full name |
| patient_email | String | Patient's email |
| patient_phone | String | Contact number |
| consultant_type | String | Type of consultant |
| appointment_date | DateTime | Scheduled appointment date |
| consultation_fee | Decimal | Fee amount |
| status | Enum | pending/confirmed/cancelled |
| created_at, updated_at | Timestamps | Record timestamps |

### Payments Table

| Column | Type | Description |
|--------|------|-------------|
| id | Primary key | Unique identifier |
| appointment_id | Foreign key | Reference to appointments |
| amount | Decimal | Payment amount |
| ccavenue_transaction_id | String | CCAvenue transaction ID |
| payment_status | Enum | initiated/success/failed |
| payment_date | DateTime | Payment completion date |
| created_at, updated_at | Timestamps | Record timestamps |

---

## 🔒 Security Considerations

- **Environment Variables**: All sensitive credentials stored in `.env` file
- **Payment Security**: CCAvenue checksum validation implemented
- **Input Validation**: Laravel validation rules for all API inputs
- **Error Logging**: Comprehensive logging for debugging and auditing

---

## ☁️ AWS Deployment Strategy

### EC2 Deployment

- Deploy on EC2 instances with Application Load Balancer
- Use Auto Scaling Groups for high availability
- Store `.env` in AWS Systems Manager Parameter Store or Secrets Manager

### Database

- Use Amazon RDS (MySQL/PostgreSQL) for managed database
- Enable automated backups with point-in-time recovery
- Set up read replicas for scalability

### File Storage

- Use Amazon S3 for document storage
- Configure IAM roles for secure access
- Enable versioning and lifecycle policies

