Hospital Management System
Lead Management & Payment System
A Laravel-based hospital appointment booking system with CCAvenue payment gateway integration for managing patient consultations and advance payments.

🚀 Features

Appointment Management: Book health consultation appointments with patient details
Payment Integration: CCAvenue payment gateway integration for advance payments
Payment Callbacks: Secure payment callback handling and status updates
Status Tracking: Track appointment and payment statuses in real-time
API-based: RESTful API endpoints for seamless integration
Error Handling: Comprehensive error handling and logging
Security: Environment-based configuration for sensitive credentials


📋 Prerequisites

PHP >= 8.1
Composer
MySQL/PostgreSQL
Laravel 10.x or higher


🛠️ Installation & Setup
1. Clone the Repository
bashgit clone https://github.com/Ashutoshxo/Hospital-management.git
cd Hospital-management
2. Install Dependencies
bashcomposer install
3. Environment Configuration
bashcp .env.example .env
Update the .env file with your database credentials:
envDB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hospital_system
DB_USERNAME=your_username
DB_PASSWORD=your_password

# CCAvenue Configuration
CCAVENUE_MERCHANT_ID=your_merchant_id
CCAVENUE_ACCESS_CODE=your_access_code
CCAVENUE_WORKING_KEY=your_working_key
CCAVENUE_REDIRECT_URL=http://localhost:8000/api/payments/callback
CCAVENUE_CANCEL_URL=http://localhost:8000/payment/cancelled
4. Generate Application Key
bashphp artisan key:generate
5. Run Migrations
bashphp artisan migrate
6. Start the Development Server
bashphp artisan serve
The application will be available at http://localhost:8000

📡 API Endpoints
1. Create Appointment
POST /api/appointments
Request Body:
json{
    "patient_name": "John Doe",
    "patient_email": "john@example.com",
    "patient_phone": "9876543210",
    "consultant_type": "Cardiologist",
    "appointment_date": "2025-12-01 10:00:00",
    "consultation_fee": 500
}
Response: 201 Created
json{
    "id": 1,
    "patient_name": "John Doe",
    "patient_email": "john@example.com",
    "status": "pending",
    "created_at": "2025-11-25T10:00:00.000000Z"
}
2. Initiate Payment
POST /api/appointments/{id}/initiate-payment
Response: 200 OK
json{
    "payment_url": "https://ccavenue.com/payment",
    "payment_id": 1,
    "amount": 500,
    "order_id": "APT-1"
}
3. Payment Callback (CCAvenue)
POST /api/payments/callback
Request Body:
json{
    "transaction_id": "TXN123456789",
    "payment_status": "success",
    "amount": 500,
    "order_id": "APT-1"
}
Response: 200 OK
json{
    "message": "Payment processed successfully",
    "appointment_status": "confirmed"
}

🗄️ Database Schema
Appointments Table
ColumnTypeDescriptionidPrimary keyUnique identifierpatient_nameStringPatient's full namepatient_emailStringPatient's emailpatient_phoneStringContact numberconsultant_typeStringType of consultantappointment_dateDateTimeScheduled appointment dateconsultation_feeDecimalFee amountstatusEnumpending/confirmed/cancelledcreated_at, updated_atTimestampsRecord timestamps
Payments Table
ColumnTypeDescriptionidPrimary keyUnique identifierappointment_idForeign keyReference to appointmentsamountDecimalPayment amountccavenue_transaction_idStringCCAvenue transaction IDpayment_statusEnuminitiated/success/failedpayment_dateDateTimePayment completion datecreated_at, updated_atTimestampsRecord timestamps

🔒 Security Considerations

Environment Variables: All sensitive credentials stored in .env file
Payment Security: CCAvenue checksum validation implemented
Input Validation: Laravel validation rules for all API inputs
Error Logging: Comprehensive logging for debugging and auditing


☁️ AWS Deployment Strategy
EC2 Deployment

Deploy on EC2 instances with Application Load Balancer
Use Auto Scaling Groups for high availability
Store .env in AWS Systems Manager Parameter Store or Secrets Manager

Database

Use Amazon RDS (MySQL/PostgreSQL) for managed database
Enable automated backups with point-in-time recovery
Set up read replicas for scalability

File Storage

Use Amazon S3 for document storage
Configure IAM roles for secure access
Enable versioning and lifecycle policies